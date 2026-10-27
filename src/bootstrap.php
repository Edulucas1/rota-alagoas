<?php
/**
 * Carregado no topo de todas as páginas.
 * Sessão, configuração, autoload das classes e funções auxiliares.
 */
declare(strict_types=1);

session_start();
date_default_timezone_set('America/Maceio');

define('RAIZ', dirname(__DIR__));

$arquivoConfig = RAIZ . '/config/config.php';
if (!file_exists($arquivoConfig)) {
    http_response_code(500);
    exit('<h2>Falta o arquivo config/config.php</h2><p>Copie <code>config/config.example.php</code> para <code>config/config.php</code> e ajuste o banco e a chave da IA.</p>');
}
$GLOBALS['CONFIG'] = require $arquivoConfig;

spl_autoload_register(function (string $classe): void {
    $arquivo = __DIR__ . '/' . $classe . '.php';
    if (file_exists($arquivo)) {
        require $arquivo;
    }
});

// ---------------------------------------------------------------- helpers

function config(string $chave, $padrao = null)
{
    $valor = $GLOBALS['CONFIG'];
    foreach (explode('.', $chave) as $parte) {
        if (!is_array($valor) || !array_key_exists($parte, $valor)) {
            return $padrao;
        }
        $valor = $valor[$parte];
    }
    return $valor;
}

/** Escapa texto para HTML (evita XSS). */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'msg' => $mensagem];
}

function pegar_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_validar(): void
{
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Requisição inválida (token CSRF). Volte e tente de novo.');
    }
}

function usuario_atual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

/** Bloqueia a página para quem não está logado ou não tem o perfil exigido. */
function exigir_login(?string $perfil = null): array
{
    $u = usuario_atual();
    if (!$u) {
        flash('aviso', 'Entre na sua conta para continuar.');
        redirecionar('login.php?voltar=' . urlencode($_SERVER['REQUEST_URI'] ?? 'index.php'));
    }
    if ($perfil !== null && $u['perfil'] !== $perfil) {
        http_response_code(403);
        flash('erro', 'Seu perfil não tem acesso a esta área.');
        redirecionar('index.php');
    }
    return $u;
}

function dinheiro(float $valor): string
{
    return $valor <= 0 ? 'Grátis' : 'R$ ' . number_format($valor, 2, ',', '.');
}

function duracao(int $min): string
{
    $h = intdiv($min, 60);
    $m = $min % 60;
    return ($h ? $h . 'h' : '') . ($m ? str_pad((string) $m, $h ? 2 : 1, '0', STR_PAD_LEFT) . ($h ? '' : ' min') : '');
}

function estrelas(?float $nota): string
{
    if ($nota === null) {
        return '<span class="muted">sem avaliações</span>';
    }
    $cheias = (int) round($nota);
    return '<span class="estrelas" title="' . number_format($nota, 1, ',', '') . '">'
        . str_repeat('★', $cheias) . str_repeat('☆', 5 - $cheias)
        . '</span> ' . number_format($nota, 1, ',', '');
}

/** Preço do ponto para exibir: valor estimado ou, sem valor, a faixa da planilha. */
function preco_ponto(array $p): string
{
    $custo = (float) ($p['custo_estimado'] ?? 0);
    if ($custo <= 0 && empty($p['faixa_preco'])) {
        return 'Preço a confirmar';
    }
    return dinheiro($custo);
}

/** Tags do ponto ("praia;familia") como lista. */
function tags_ponto(array $p): array
{
    return array_values(array_filter(array_map('trim', explode(';', (string) ($p['tags'] ?? '')))));
}

function data_br(string $data, bool $hora = false): string
{
    $ts = strtotime($data);
    return $hora ? date('d/m/Y H:i', $ts) : date('d/m/Y', $ts);
}

// Rótulos usados nas telas e no assistente
const CATEGORIAS_PONTO = [
    'praia' => 'Praias', 'natureza' => 'Natureza', 'historia' => 'História',
    'cultura' => 'Cultura', 'gastronomia' => 'Gastronomia', 'aventura' => 'Aventura', 'compras' => 'Artesanato e compras',
];
const FAIXAS_PRECO = ['gratuito' => 'Gratuito', 'barato' => 'Barato', 'medio' => 'Médio', 'caro' => 'Caro', 'pago' => 'Pago'];
const PERIODOS = ['manha' => 'Manhã', 'tarde' => 'Tarde', 'fim_de_tarde' => 'Fim de tarde', 'noite' => 'Noite', 'qualquer' => 'Qualquer horário'];
const CATEGORIAS_EVENTO = [
    'musica' => 'Música', 'cultura' => 'Cultura', 'gastronomia' => 'Gastronomia',
    'festa' => 'Festa', 'feira' => 'Feira', 'esporte' => 'Esporte',
];
const COMPANHIAS = ['sozinho' => 'Sozinho(a)', 'casal' => 'Casal', 'familia' => 'Família com crianças', 'amigos' => 'Amigos'];
const RITMOS     = ['tranquilo' => 'Tranquilo (≈4h/dia)', 'moderado' => 'Moderado (≈6h/dia)', 'intenso' => 'Intenso (≈8h/dia)'];
const ORCAMENTOS = ['economico' => 'Econômico', 'medio' => 'Médio', 'livre' => 'Sem limite'];
const PERFIS     = ['turista' => 'Turista', 'guia' => 'Guia local', 'organizador' => 'Organizador de eventos'];
