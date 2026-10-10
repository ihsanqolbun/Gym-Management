<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class MembershipPricing
{
    private const PRIVATE_CATEGORIES = ['private_siang', 'private_malam'];

    /**
     * Tentukan kategori dan harga.
     * Private dipilih manual (harganya sama di semua umur),
     * selain itu kategori dihitung dari umur pada tanggal mulai.
     *
     * @return array{category: string, price: int}
     */
    public function resolve(
        Carbon $dateOfBirth,
        Carbon $startDate,
        string $package,
        ?string $privateCategory = null,
    ): array {
        if ($privateCategory !== null) {
            if (! in_array($privateCategory, self::PRIVATE_CATEGORIES, true)) {
                throw new InvalidArgumentException('Kategori private tidak valid.');
            }
            $category = $privateCategory;
        } else {
            $category = $this->categoryFromAge($dateOfBirth, $startDate);
        }

        $price = config("pricelist.prices.{$category}.{$package}");

        if ($price === null) {
            throw new InvalidArgumentException(
                "Paket {$package} tidak tersedia untuk kategori {$category}."
            );
        }

        return ['category' => $category, 'price' => $price];
    }

    public function categoryFromAge(Carbon $dateOfBirth, Carbon $onDate): string
    {
        $age = (int) $dateOfBirth->diffInYears($onDate, true);

        return match (true) {
            $age < 7 => throw new InvalidArgumentException('Umur minimal member adalah 7 tahun.'),
            $age <= 17 => 'pelajar',
            $age <= 24 => 'reguler',
            default => 'dewasa',
        };
    }
}