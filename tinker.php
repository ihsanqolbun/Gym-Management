<?php

use App\Models\User;
use App\Models\Membership;

// Cari membership yang ada dulu, baru ambil owner-nya (biar dijamin nyambung)
$anyMembership = Membership::first();
$owner = User::find($anyMembership->user_id);

// Skenario 1: owner asli liat membership miliknya sendiri -> harus true
$result1 = $owner->can('view', $anyMembership);
echo "Owner asli (id: {$owner->id}, role: {$owner->role}) liat membership miliknya (user_id: {$anyMembership->user_id}): " . ($result1 ? 'true' : 'false') . "\n";