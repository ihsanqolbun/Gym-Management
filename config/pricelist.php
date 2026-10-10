<?php

return [
    // Biaya pendaftaran sekali bayar
    'register_fee' => 75000,

    // Aturan jam sesi private
    'private' => [
        'earliest_start'   => '06:00',
        'last_end'         => '20:00',
        'duration_minutes' => 180,
        'siang_start'      => '11:00',
        'siang_end'        => '17:00',
    ],

    // Harga per [category][package]
    'prices' => [
        'pelajar' => [
            'visit_1x'      => 15000,
            'visit_10x'     => 135000,
            'all_day_month' => 250000,
        ],
        'reguler' => [
            'visit_1x'      => 20000,
            'visit_10x'     => 185000,
            'all_day_month' => 300000,
        ],
        'dewasa' => [
            'visit_1x'      => 35000,
            'visit_10x'     => 335000,
            'all_day_month' => 450000,
        ],
        'private_siang' => [
            'visit_1x'  => 100000,
            'visit_10x' => 950000,
        ],
        'private_malam' => [
            'visit_1x'  => 125000,
            'visit_10x' => 1200000,
        ],
    ],
];