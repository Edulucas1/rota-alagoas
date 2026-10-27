<?php
declare(strict_types=1);

class Avaliacao
{
    /** Cria ou atualiza a avaliação do turista para o ponto (RN02). Devolve lista de erros. */
    public static function avaliar(int $usuarioId, int $pontoId, int $nota, string $comentario): array
    {
        if ($nota < 1 || $nota > 5) {
            return ['A nota deve ser de 1 a 5.'];
        }
        if (!PontoTuristico::buscar($pontoId)) {
            return ['Ponto turístico não encontrado.'];
        }
        $comentario = mb_substr(trim($comentario), 0, 500);
        Database::executar(
            'INSERT INTO avaliacoes (usuario_id, ponto_id, nota, comentario) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE nota = VALUES(nota), comentario = VALUES(comentario), criado_em = NOW()',
            [$usuarioId, $pontoId, $nota, $comentario ?: null]
        );
        return [];
    }

    public static function doPonto(int $pontoId): array
    {
        return Database::executar(
            'SELECT a.*, u.nome AS usuario_nome FROM avaliacoes a JOIN usuarios u ON u.id = a.usuario_id
             WHERE a.ponto_id = ? ORDER BY a.criado_em DESC',
            [$pontoId]
        )->fetchAll();
    }

    public static function doUsuarioNoPonto(int $usuarioId, int $pontoId): ?array
    {
        $a = Database::executar('SELECT * FROM avaliacoes WHERE usuario_id = ? AND ponto_id = ?', [$usuarioId, $pontoId])->fetch();
        return $a ?: null;
    }
}
