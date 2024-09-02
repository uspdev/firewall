<?php

$menu = [
    [
        'text' => '<i class="fas fa-history"></i> Logs',
        'url' => 'activities',
        'can' => 'admin'
    ],
    [
        'text' => '<i class="fas fa-dragon"></i> Todas as regras',
        'url' => 'allRules',
        'can' => 'admin'
    ],
];

$right_menu = [
    [
        // menu utilizado para views da biblioteca senhaunica-socialite.
        'key' => 'senhaunica-socialite',
    ],
    [
        'key' => 'laravel-tools',
    ],
];

# dashboard_url renomeado para app_url
# USPTHEME_SKIN deve ser colocado no .env da aplicação 

return [
    'title' => 'Firewall',
    'skin' => env('USP_THEME_SKIN', 'uspdev'),
    'app_url' => config('app.url'),
    'session_key' => 'laravel-usp-theme',
    'logout_method' => 'POST',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'menu' => $menu,
    'right_menu' => $right_menu,
    'mensagensFlash' => false,
];
