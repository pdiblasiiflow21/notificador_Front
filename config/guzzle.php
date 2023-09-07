<?php

declare(strict_types=1);

return [
    'iflow' => [
        'timeout'         => env('GUZZLE_IFLOW_TIMEOUT', 90),
        'allow_redirects' => env('GUZZLE_IFLOW_ALLOW_REDIRECTS', false),
        'verify'          => env('GUZZLE_IFLOW_VERIFY', false),
    ],
];
