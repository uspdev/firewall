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

# dashboard_url renomeado para app_url
# USPTHEME_SKIN deve ser colocado no .env da aplicação 

return [
    'title' => config('app.name'),
    'skin' => env('USP_THEME_SKIN', 'uspdev'),
    'app_url' => config('app.url'),
    'logout_method' => 'POST',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'menu' => $menu,
    //'right_menu' => $right_menu,
];
