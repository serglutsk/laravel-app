<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Name Parser Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the name parser service used to parse full names
    | into individual components (title, first name, initial, last name).
    |
    */

    'titles' => [
        'Mr',
        'Mrs',
        'Miss',
        'Ms',
        'Dr',
        'Prof',
        'Mister',
    ],

    'conjunctions' => [
        'and',
        '&',
    ],
];
