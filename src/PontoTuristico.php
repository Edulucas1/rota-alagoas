<?php
declare(strict_types=1);

class PontoTuristico
{
    private const SELECT_BASE = '
        SELECT p.*, u.nome AS guia_nome,
               ROUND(AVG(a.nota), 1) AS nota_media, COUNT(a.id) AS total_avaliacoes
        FROM pontos_turisticos p
        JOIN usuarios u ON u.id = p.criado_por
        LEFT JOIN avaliacoes a ON a.ponto_id = p.id';

    /** Lista com filtros opcionais: cidade, categoria, busca, criado_por. */
    public static function listar(array $filtros = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filtros['cidade']))     { $where[] = 'p.cidade = ?';     $params[] = $filtros['cidade']; }
        if (!empty($filtros['categoria']))  { $where[] = 'p.categoria = ?';  $params[] = $filtros['categoria']; }
        if (!empty($filtros['busca'])) {
            // procura no nome, no bairro e nas tags (ex.: "família", "pôr do sol", "Francês")
            $where[] = '(p.nome LIKE ? OR p.bairro LIKE ? OR p.tags LIKE ?)';
            $termo = '%' . $filtros['busca'] . '%';
            array_push($params, $termo, $termo, $termo);
        }
        if (!empty($filtros['criado_por'])) { $where[] = 'p.criado_por = ?'; $params[] = $filtros['criado_por']; }

        $sql = self::SELECT_BASE
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' GROUP BY p.id ORDER BY COUNT(a.id) = 0, AVG(a.nota) DESC, COUNT(a.id) DESC, p.nome';
        return Database::executar($sql, $params)->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $p = Database::executar(self::SELECT_BASE . ' WHERE p.id = ? GROUP BY p.id', [$id])->fetch();
        return $p ?: null;
    }

    public static function cidades(): array
    {
        return Database::executar('SELECT DISTINCT cidade FROM pontos_turisticos ORDER BY cidade')->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function validar(array $d): array
    {
        $erros = [];
        if (mb_strlen(trim($d['nome'] ?? '')) < 3) $erros[] = 'Informe o nome do ponto turístico.';
        if (trim($d['cidade'] ?? '') === '') $erros[] = 'Informe a cidade.';
        if (!array_key_exists($d['categoria'] ?? '', CATEGORIAS_PONTO)) $erros[] = 'Escolha uma categoria.';
        if (mb_strlen(trim($d['descricao'] ?? '')) < 10) $erros[] = 'Escreva uma descrição (mínimo 10 caracteres).';
        $dur = (int) ($d['duracao_min'] ?? 0);
        if ($dur < 15 || $dur > 720) $erros[] = 'Duração deve ficar entre 15 e 720 minutos.';
        if ((float) ($d['custo_estimado'] ?? 0) < 0) $erros[] = 'Custo não pode ser negativo.';
        if (($d['faixa_preco'] ?? '') !== '' && !array_key_exists($d['faixa_preco'], FAIXAS_PRECO)) $erros[] = 'Faixa de preço inválida.';
        if (!array_key_exists($d['melhor_periodo'] ?? 'qualquer', PERIODOS)) $erros[] = 'Período inválido.';
        foreach (['latitude' => 90, 'longitude' => 180] as $campo => $max) {
            $v = trim((string) ($d[$campo] ?? ''));
            if ($v !== '' && (!is_numeric($v) || abs((float) $v) > $max)) $erros[] = ucfirst($campo) . ' inválida.';
        }
        return $erros;
    }

    /** Insere (id nulo) ou atualiza. Só o dono atualiza (RN01). */
    public static function salvar(array $d, int $guiaId, ?int $id = null): int
    {
        $numero = fn($v) => trim((string) $v) === '' ? null : (float) $v;
        $campos = [
            trim($d['nome']), trim($d['cidade']), trim($d['bairro'] ?? '') ?: null, $d['categoria'], trim($d['descricao']),
            trim($d['endereco'] ?? '') ?: null, (int) $d['duracao_min'], (float) ($d['custo_estimado'] ?? 0),
            ($d['faixa_preco'] ?? '') ?: null, empty($d['depende_mare']) ? 0 : 1, $d['melhor_periodo'] ?? 'qualquer',
            self::normalizarTags((string) ($d['tags'] ?? '')) ?: null, $numero($d['latitude'] ?? ''), $numero($d['longitude'] ?? ''),
        ];
        if ($id === null) {
            Database::executar(
                'INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado,
                     faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [...$campos, $guiaId]
            );
            return (int) Database::conexao()->lastInsertId();
        }
        Database::executar(
            'UPDATE pontos_turisticos SET nome=?, cidade=?, bairro=?, categoria=?, descricao=?, endereco=?, duracao_min=?, custo_estimado=?,
                 faixa_preco=?, depende_mare=?, melhor_periodo=?, tags=?, latitude=?, longitude=?
             WHERE id = ? AND criado_por = ?',
            [...$campos, $id, $guiaId]
        );
        return $id;
    }

    /** "Praia; Família , pôr do sol" -> "praia;familia;pôr do sol" (sem repetidos). */
    public static function normalizarTags(string $texto): string
    {
        $tags = [];
        foreach (preg_split('/[;,]/', mb_strtolower($texto)) as $t) {
            $t = trim($t);
            $t = strtr($t, ['família' => 'familia', 'religião' => 'religiao', 'história' => 'historia', 'vida noturna' => 'vida_noturna']);
            if ($t !== '') $tags[$t] = true;
        }
        return implode(';', array_keys($tags));
    }

    public static function excluir(int $id, int $guiaId): bool
    {
        return Database::executar('DELETE FROM pontos_turisticos WHERE id = ? AND criado_por = ?', [$id, $guiaId])->rowCount() > 0;
    }
}
