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
    [
        'text' => 'Cursos',
        'url' => 'graduacao/cursos',
        'can' => 'disciplinas',
    ],
    [
        'text' => 'Relatório síntese',
        'url' => 'graduacao/relatorio/sintese',
        'can' => 'datagrad',
    ],
    [
        'text' => 'Relatório complementar',
        'url' => 'graduacao/relatorio/complementar',
        'can' => 'datagrad',
    ],
    [
        'text' => 'Relatório carga didática',
        'url' => 'graduacao/relatorio/cargadidatica',
        'can' => 'datagrad',
    ],
    [
        'text' => 'Relatório grade horária',
        'url' => 'graduacao/relatorio/gradehoraria',
        'can' => 'datagrad',
    ],
    [
        'text' => 'Relatório de evasão',
        'url' => 'graduacao/relatorio/evasao',
        'can' => 'evasao',
    ],
    [
        'text' => 'Disciplinas',
        'url' => 'disciplinas',
        'can' => 'disciplinas',
    ],
];



$right_menu = [
    [
        'text' => '<span class="text-danger"><i class="fas fa-user-tag"></i> Funções</span>',
        'url' => 'roles',
        'can' => 'disciplina-cg',
    ],
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
    'title' => config('app.name'),
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
