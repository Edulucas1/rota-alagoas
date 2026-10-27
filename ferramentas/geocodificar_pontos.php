<?php
/**
 * Busca latitude/longitude dos pontos turísticos que ainda não têm coordenadas,
 * usando o OpenStreetMap (Nominatim), e gera database/coordenadas_pontos.sql.
 *
 * Rodar no terminal, na pasta do projeto (precisa de internet e do MySQL ligado):
 *   /opt/lampp/bin/php ferramentas/geocodificar_pontos.php
 * Depois, confira o relatório e importe o SQL gerado:
 *   /opt/lampp/bin/mysql -u root rota_alagoas < database/coordenadas_pontos.sql
 *
 * Regras de uso do Nominatim: no máximo 1 pedido por segundo e um User-Agent que
 * identifique o projeto (https://operations.osmfoundation.org/policies/nominatim/).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('Rode este script pelo terminal.');
}

$raiz = dirname(__DIR__);
$config = require $raiz . '/config/config.php';
$db = $config['db'];
$pdo = new PDO(
    "mysql:host={$db['host']};port={$db['porta']};dbname={$db['nome']};charset=utf8mb4",
    $db['usuario'], $db['senha'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

const BASE = 'https://nominatim.openstreetmap.org/search';
const USER_AGENT = 'RotaAlagoas-Hackathon/1.0 (https://github.com/Edulucas1/rota-alagoas)';
const RAIO_MAX_KM = 35;   // resultado mais longe que isso da cidade é descartado

$base = getenv('NOMINATIM_URL') ?: BASE;   // permite testar com um servidor falso

function buscar(string $base, string $texto): ?array
{
    static $ultimo = 0.0;
    $espera = 1.1 - (microtime(true) - $ultimo);
    if ($espera > 0) usleep((int) ($espera * 1e6));
    $ultimo = microtime(true);

    $url = $base . '?' . http_build_query([
        'q' => $texto, 'format' => 'jsonv2', 'limit' => 1, 'countrycodes' => 'br',
        'viewbox' => '-38.3,-8.8,-35.1,-10.55', 'bounded' => 1, 'accept-language' => 'pt-BR',
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['User-Agent: ' . USER_AGENT],
    ]);
    $resp = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($resp === false || $status !== 200) {
        fwrite(STDERR, "  ! erro HTTP $status ao buscar \"$texto\"\n");
        return null;
    }
    $dados = json_decode($resp, true);
    return $dados[0] ?? null;
}

function distanciaKm(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $r = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    return 2 * $r * asin(sqrt($a));
}

$pontos = $pdo->query('SELECT id, nome, cidade, bairro FROM pontos_turisticos WHERE latitude IS NULL OR longitude IS NULL ORDER BY cidade, nome')->fetchAll();
if (!$pontos) {
    exit("Todos os pontos já têm coordenadas.\n");
}
echo count($pontos) . " ponto(s) sem coordenadas. Isso leva cerca de " . ceil(count($pontos) * 2.5 / 60) . " minuto(s).\n\n";

$centros = [];
$sql = [];
$resumo = ['exato' => 0, 'bairro' => 0, 'faltou' => 0];
$faltaram = [];

foreach ($pontos as $p) {
    $cidade = $p['cidade'];
    if (!isset($centros[$cidade])) {
        $c = buscar($base, "$cidade, Alagoas, Brasil");
        $centros[$cidade] = $c ? [(float) $c['lat'], (float) $c['lon']] : null;
    }
    // Nome sem o que está entre parênteses: "La Rue (Rua La Rue)" -> "La Rue"
    $nomeLimpo = trim(preg_replace('/\s*\(.*?\)\s*/', ' ', $p['nome']));
    $tentativas = [["{$p['nome']}, $cidade, Alagoas", 'exato']];
    if ($nomeLimpo !== $p['nome']) $tentativas[] = ["$nomeLimpo, $cidade, Alagoas", 'exato'];
    if ($p['bairro']) $tentativas[] = ["{$p['bairro']}, $cidade, Alagoas", 'bairro'];

    $achou = null;
    foreach ($tentativas as [$texto, $tipo]) {
        $r = buscar($base, $texto);
        if (!$r) continue;
        $lat = (float) $r['lat'];
        $lon = (float) $r['lon'];
        if ($centros[$cidade] && distanciaKm($lat, $lon, ...$centros[$cidade]) > RAIO_MAX_KM) continue;
        $achou = [$lat, $lon, $tipo, $r['display_name'] ?? ''];
        break;
    }

    if (!$achou) {
        $resumo['faltou']++;
        $faltaram[] = "{$p['nome']} ({$cidade})";
        printf("  [ -- ] %-45s não encontrado\n", mb_strimwidth($p['nome'], 0, 45));
        continue;
    }
    [$lat, $lon, $tipo, $onde] = $achou;
    $resumo[$tipo]++;
    printf("  [%s] %-45s %.6f, %.6f  %s\n", $tipo === 'exato' ? ' OK ' : 'BAIR', mb_strimwidth($p['nome'], 0, 45), $lat, $lon, mb_strimwidth($onde, 0, 60, '…'));
    $sql[] = sprintf(
        "UPDATE pontos_turisticos SET latitude = %.6f, longitude = %.6f WHERE nome = %s AND cidade = %s AND latitude IS NULL; -- %s",
        $lat, $lon, $pdo->quote($p['nome']), $pdo->quote($cidade), $tipo === 'exato' ? 'OpenStreetMap' : 'aproximado: centro do bairro'
    );
}

$arquivo = $raiz . '/database/coordenadas_pontos.sql';
file_put_contents($arquivo,
    "-- Coordenadas geradas por ferramentas/geocodificar_pontos.php em " . date('d/m/Y H:i') . "\n"
    . "-- Fonte: OpenStreetMap (Nominatim). 'aproximado' = centro do bairro, confira no mapa.\n"
    . "SET NAMES utf8mb4;\n\n" . implode("\n", $sql) . "\n");

echo "\nResultado: {$resumo['exato']} encontrados pelo nome, {$resumo['bairro']} aproximados pelo bairro, {$resumo['faltou']} sem coordenadas.\n";
if ($faltaram) {
    echo "Sem coordenadas (preencha à mão pelo Google Maps, botão direito > copiar coordenadas):\n  - " . implode("\n  - ", $faltaram) . "\n";
}
echo "\nSQL gerado em database/coordenadas_pontos.sql. Para gravar no banco:\n";
echo "  /opt/lampp/bin/mysql -u root {$db['nome']} < database/coordenadas_pontos.sql\n";
