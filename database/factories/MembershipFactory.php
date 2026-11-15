<?php

namespace Database\Factories;

use App\Models\Membership;
use App\Models\User;
use App\Services\MembershipPricing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MembershipFactory extends Factory
{
    // Urutan key penting: tiap closure bisa baca key di atasnya lewat $a
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'package' => fake()->randomElement(['visit_1x', 'visit_10x', 'all_day_month']),

            'start_date' => fn () => Carbon::instance(fake()->dateTimeBetween('-2 months', 'now'))
                ->toDateString(),

            // Sama kayak controller: visit_1x = +1 hari, sisanya +1 bulan
            'end_date' => fn (array $a) => $a['package'] === 'visit_1x'
                ? Carbon::parse($a['start_date'])->addDay()->toDateString()
                : Carbon::parse($a['start_date'])->addMonth()->toDateString(),

            'type' => fn (array $a) => $a['package'] === 'visit_1x' ? 'harian' : 'bulanan',

            // Kategori dan harga diturunkan oleh service yang sama dengan endpoint
            'category' => fn (array $a) => $this->pricingFor($a)['category'],
            'price' => fn (array $a) => $this->pricingFor($a)['price'],

            // Register fee cuma buat membership pertama user
            'register_fee' => fn (array $a) => Membership::where('user_id', $a['user_id'])->exists()
                ? 0
                : config('pricelist.register_fee'),

            'payment_status' => fake()->boolean(85) ? 'paid' : 'pending',

            'status' => fn (array $a) => Carbon::parse($a['end_date'])->isFuture() ? 'active' : 'expired',
        ];
    }

    private function pricingFor(array $a): array
    {
        $user = User::find($a['user_id']);

        return app(MembershipPricing::class)->resolve(
            $user->date_of_birth,
            Carbon::parse($a['start_date']),
            $a['package'],
        );
    }
}