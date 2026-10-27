<?php
declare(strict_types=1);

class Evento
{
    /** Eventos que ainda não terminaram, com filtros opcionais. */
    public static function listarFuturos(array $filtros = []): array
    {
        $where = ['e.fim >= NOW()'];
        $params = [];
        if (!empty($filtros['cidade']))         { $where[] = 'e.cidade = ?';          $params[] = $filtros['cidade']; }
        if (!empty($filtros['de']))             { $where[] = 'DATE(e.fim) >= ?';      $params[] = $filtros['de']; }
        if (!empty($filtros['ate']))            { $where[] = 'DATE(e.inicio) <= ?';   $params[] = $filtros['ate']; }
        if (!empty($filtros['organizador_id'])) { $where[] = 'e.organizador_id = ?';  $params[] = $filtros['organizador_id']; }

        return Database::executar(
            'SELECT e.*, u.nome AS organizador_nome FROM eventos e JOIN usuarios u ON u.id = e.organizador_id
             WHERE ' . implode(' AND ', $where) . ' ORDER BY e.inicio',
            $params
        )->fetchAll();
    }

    /** Todos os eventos de um organizador (inclusive passados) para o painel. */
    public static function listarDoOrganizador(int $orgId): array
    {
        return Database::executar('SELECT * FROM eventos WHERE organizador_id = ? ORDER BY inicio DESC', [$orgId])->fetchAll();
    }

    /** Eventos que acontecem entre duas datas (para o roteiro — RN04). */
    public static function noPeriodo(string $inicio, string $fim): array
    {
        return Database::executar(
            'SELECT * FROM eventos WHERE DATE(inicio) <= ? AND DATE(fim) >= ? ORDER BY inicio',
            [$fim, $inicio]
        )->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $e = Database::executar('SELECT * FROM eventos WHERE id = ?', [$id])->fetch();
        return $e ?: null;
    }

    public static function cidades(): array
    {
        return Database::executar('SELECT DISTINCT cidade FROM eventos WHERE fim >= NOW() ORDER BY cidade')->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function validar(array $d): array
    {
        $erros = [];
        if (mb_strlen(trim($d['titulo'] ?? '')) < 3) $erros[] = 'Informe o título do evento.';
        if (mb_strlen(trim($d['descricao'] ?? '')) < 10) $erros[] = 'Escreva uma descrição (mínimo 10 caracteres).';
        if (trim($d['cidade'] ?? '') === '') $erros[] = 'Informe a cidade.';
        if (trim($d['local'] ?? '') === '') $erros[] = 'Informe o local.';
        if (!array_key_exists($d['categoria'] ?? '', CATEGORIAS_EVENTO)) $erros[] = 'Escolha uma categoria.';
        $ini = strtotime($d['inicio'] ?? '');
        $fim = strtotime($d['fim'] ?? '');
        if (!$ini || !$fim) {
            $erros[] = 'Informe data e hora de início e fim.';
        } elseif ($fim < $ini) {
            $erros[] = 'A data de fim deve ser depois do início.';
        }
        if ((float) ($d['preco'] ?? 0) < 0) $erros[] = 'Preço não pode ser negativo.';
        return $erros;
    }

    public static function salvar(array $d, int $orgId, ?int $id = null): int
    {
        $campos = [
            trim($d['titulo']), trim($d['descricao']), trim($d['cidade']), trim($d['local']),
            date('Y-m-d H:i:s', strtotime($d['inicio'])), date('Y-m-d H:i:s', strtotime($d['fim'])),
            $d['categoria'], (float) ($d['preco'] ?? 0),
        ];
        if ($id === null) {
            Database::executar(
                'INSERT INTO eventos (titulo, descricao, cidade, local, inicio, fim, categoria, preco, organizador_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [...$campos, $orgId]
            );
            return (int) Database::conexao()->lastInsertId();
        }
        Database::executar(
            'UPDATE eventos SET titulo=?, descricao=?, cidade=?, local=?, inicio=?, fim=?, categoria=?, preco=?
             WHERE id = ? AND organizador_id = ?',
            [...$campos, $id, $orgId]
        );
        return $id;
    }

    public static function excluir(int $id, int $orgId): bool
    {
        return Database::executar('DELETE FROM eventos WHERE id = ? AND organizador_id = ?', [$id, $orgId])->rowCount() > 0;
    }
}
