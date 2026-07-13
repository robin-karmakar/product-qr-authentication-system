<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Super Admin
    |--------------------------------------------------------------------------
    |
    | Used exclusively by SuperAdminSeeder to create the first account in
    | the system. Set these in .env for local/staging seeding only —
    | never commit real credentials. Rotate the password immediately
    | after first login in any environment that matters.
    |
    */

    'super_admin' => [
        'email' => env('SPATS_SUPER_ADMIN_EMAIL'),
        'password' => env('SPATS_SUPER_ADMIN_PASSWORD'),
    ],

];
