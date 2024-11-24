<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Specify the path to the Firebase service account credentials file.
    | Make sure this path is correct and points to the JSON file provided
    | by Firebase when generating a private key.
    |
    */
    'credentials' => env('FIREBASE_CREDENTIALS'),


    /*
    |--------------------------------------------------------------------------
    | Firebase Realtime Database URL
    |--------------------------------------------------------------------------
    |
    | This is the URL for your Firebase Realtime Database. You can find this
    | in the Firebase Console under the "Database" section.
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL'),


    /*
    |--------------------------------------------------------------------------
    | Additional Firebase Configurations
    |--------------------------------------------------------------------------
    |
    | Add any additional configurations here if needed in the future.
    |
    */
];
