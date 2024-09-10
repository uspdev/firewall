<?php

return [
    'ssh' => env('pfsense_ssh'),
    'private_key' => storage_path(env('pfsense_private_key')),
];
