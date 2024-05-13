<?php

return [

    //buat providersnya
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    //mengidentifikasi middleware nya apa
    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

    //mencari letak modelnya yang menyimpan data username,password
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ]
    ]
];
