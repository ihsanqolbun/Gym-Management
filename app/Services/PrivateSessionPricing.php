<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class PrivateSessionPricing
{
    /**
     * @return array{time_slot: string, price: int}
     */
    public function resolve(string $startTime): array
    {
        $cfg = config('pricelist.private');

        $start = Carbon::createFromFormat('!H:i', $startTime);
        $end = $start->copy()->addMinutes($cfg['duration_minutes']);

        if (
            $start->lt(Carbon::createFromFormat('!H:i', $cfg['earliest_start']))
            || $end->gt(Carbon::createFromFormat('!H:i', $cfg['last_end']))
        ) {
            throw new InvalidArgumentException(
                "Jam private harus mulai {$cfg['earliest_start']} dan selesai paling lambat {$cfg['last_end']}."
            );
        }

        $isSiang = $start->gte(Carbon::createFromFormat('!H:i', $cfg['siang_start']))
            && $start->lt(Carbon::createFromFormat('!H:i', $cfg['siang_end']));

        $slot = $isSiang ? 'private_siang' : 'private_malam';

        return [
            'time_slot' => $slot,
            'price' => config("pricelist.prices.{$slot}.visit_1x"),
        ];
    }
}