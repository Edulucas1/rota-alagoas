# 4. Modelo Entidade-Relacionamento

O script completo está em [`database/rota_alagoas.sql`](../database/rota_alagoas.sql).

```mermaid
erDiagram
    USUARIOS ||--o{ PONTOS_TURISTICOS : "cadastra (guia)"
    USUARIOS ||--o{ EVENTOS : "organiza"
    USUARIOS ||--o{ AVALIACOES : "escreve (turista)"
    PONTOS_TURISTICOS ||--o{ AVALIACOES : "recebe"
    USUARIOS ||--o{ ROTEIROS : "gera (turista)"
    ROTEIROS ||--|{ ROTEIRO_ITENS : "contém"
    PONTOS_TURISTICOS |o--o{ ROTEIRO_ITENS : "aparece em"
    EVENTOS |o--o{ ROTEIRO_ITENS : "aparece em"

    USUARIOS {
        int id PK
        varchar nome
        varchar email UK
        varchar senha_hash
        enum perfil "turista | guia | organizador"
        datetime criado_em
    }
    PONTOS_TURISTICOS {
        int id PK
        varchar nome
        varchar cidade
        varchar bairro
        enum categoria "praia | natureza | historia | cultura | gastronomia | aventura | compras"
        text descricao
        varchar endereco
        smallint duracao_min
        decimal custo_estimado
        enum faixa_preco "gratuito | barato | medio | caro | pago"
        tinyint depende_mare
        enum melhor_periodo "manha | tarde | fim_de_tarde | noite | qualquer"
        varchar tags "interesses separados por ;"
        decimal latitude
        decimal longitude
        int criado_por FK
        datetime criado_em
    }
    EVENTOS {
        int id PK
        varchar titulo
        text descricao
        varchar cidade
        varchar local
        datetime inicio
        datetime fim
        enum categoria
        decimal preco
        int organizador_id FK
        datetime criado_em
    }
    AVALIACOES {
        int id PK
        int usuario_id FK
        int ponto_id FK
        tinyint nota "1 a 5"
        varchar comentario
        datetime criado_em
    }
    ROTEIROS {
        int id PK
        int usuario_id FK
        varchar titulo
        text resumo
        date data_inicio
        tinyint dias "1 a 7"
        varchar cidade_base
        varchar interesses
        enum companhia
        enum ritmo
        enum orcamento
        enum origem "ia | padrao"
        datetime criado_em
    }
    ROTEIRO_ITENS {
        int id PK
        int roteiro_id FK
        tinyint dia
        tinyint ordem
        time horario
        int ponto_id FK "nulo se for evento"
        int evento_id FK "nulo se for ponto"
        varchar titulo
        varchar dica
    }
```

## Decisões de modelagem

| Decisão | Por quê |
|---|---|
| Uma tabela `usuarios` com coluna `perfil` | Os três atores têm os mesmos dados de login; o perfil controla o que cada um pode fazer. |
| `avaliacoes` com `UNIQUE (usuario_id, ponto_id)` | Garante a RN02: uma avaliação por turista por ponto. A nota média é calculada com `AVG()`, não é guardada. |
| `CHECK (nota BETWEEN 1 AND 5)` e `CHECK (dias BETWEEN 1 AND 7)` | Regra de negócio também no banco, não só no PHP. |
| `roteiro_itens` com `ponto_id` **ou** `evento_id` | Um item do roteiro pode ser um lugar ou um evento. As duas FKs são opcionais. |
| `roteiro_itens.titulo` (cópia do nome) | Se o ponto for excluído (`ON DELETE SET NULL`), o roteiro antigo continua legível. |
| `roteiros.origem` | Registra se o roteiro veio da IA ou do plano B — útil para QA e para o pitch. |
| `ON DELETE CASCADE` em roteiros → itens e usuários → avaliações | Excluir um roteiro apaga seus itens; excluir usuário apaga o que é só dele. |
| Índices em `cidade`, `categoria` e `inicio` | São os campos usados nos filtros das listagens. |
| `tags`, `melhor_periodo` e `depende_mare` em `pontos_turisticos` | Vêm da planilha de pesquisa do grupo. A IA e o plano B usam esses campos para casar o lugar com o perfil, pôr cada atividade na hora certa e avisar da maré baixa. `tags` fica como texto separado por `;` (simples para o hackathon; o ideal seria uma tabela N:N). |

## Cardinalidades

- Um **guia** cadastra 0..N pontos; cada ponto tem exatamente 1 guia.
- Um **organizador** cadastra 0..N eventos; cada evento tem 1 organizador.
- Um **turista** escreve 0..N avaliações; cada ponto recebe 0..N avaliações (N:N resolvido pela tabela `avaliacoes`).
- Um **turista** gera 0..N roteiros; cada roteiro tem 1..N itens.
- Cada item referencia 0..1 ponto e 0..1 evento.
