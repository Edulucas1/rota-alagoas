<?php
/**
 * Copie este arquivo para config.php e preencha.
 * O config.php está no .gitignore: a chave da API NUNCA vai para o GitHub.
 */
return [
    'db' => [
        'host'    => 'localhost',
        'porta'   => 3306,
        'nome'    => 'rota_alagoas',
        'usuario' => 'root',
        'senha'   => '',          // XAMPP vem com senha vazia
    ],

    'gemini' => [
        // Gere a chave em https://aistudio.google.com/apikey
        // Também pode ficar na variável de ambiente GEMINI_API_KEY.
        'chave'         => '',
        'modelo'        => 'gemini-3.6-flash',
        // Se o modelo principal estiver sobrecarregado (HTTP 503), tenta estes, em ordem.
        // Nomes conferidos na lista de modelos da chave (27/10/2026).
        'modelos_reserva' => ['gemini-3.5-flash', 'gemini-3.5-flash-lite', 'gemini-3.8-flash'],
        'timeout'       => 20,    // segundos; passou disso, entra o plano B
        // No XAMPP para Windows, se aparecer erro de certificado SSL,
        // configure curl.cainfo no php.ini (ver README) ou, só em demo, mude para false.
        'verificar_ssl' => true,
    ],

    // Mostra detalhes técnicos do erro da IA na tela (use false na apresentação)
    'debug' => true,
];
