# 3. Planejamento — Quadro no Trello

## 3.1 Papéis da equipe (sugestão — ajustem se precisar)

| Integrante | Papel principal | Também cuida de |
|---|---|---|
| **Eduardo Lucas** (cabeça de chave) | Líder técnico, back-end e integração com a IA | Revisão dos *pull requests* |
| **José Eduardo Souza da Silva** | Documentação (quem anota) | Requisitos, histórias, casos de uso |
| **José Wellington da Silva Vieira** | Trello e QA | Plano de testes, casos de teste, bugs |
| **Kelvin Gadelha** | GitHub e front-end | Telas, CSS, protótipo |
| **Matheus** | Banco de dados | Modelo ER, dados de exemplo, pitch |

## 3.2 Estrutura do quadro

Nome do quadro: **Rota Alagoas — Grupo 3**

Listas (da esquerda para a direita):
`Backlog` → `Sprint 1 (20/10)` → `Sprint 2 (21–22/10)` → `Sprint 3 (23/10)` → `Em andamento` → `Em teste` → `Feito`

Etiquetas: 🟦 Documentação · 🟩 Back-end · 🟨 Front-end · 🟥 Banco · 🟪 IA · 🟧 QA · ⬜ Entrega

> Cada cartão de história leva no título o ID (HU01…) e, na descrição, os critérios de aceitação do documento 02. Coloque o responsável como membro do cartão.

## 3.3 Cartões

### Sprint 1 — Análise e planejamento (terça, 20/10)

| Cartão | Etiqueta | Responsável |
|---|---|---|
| Definir nome do grupo, papéis, criar Trello e repositório no GitHub | ⬜ Entrega | Eduardo / Kelvin / José Wellington |
| Escrever documento de requisitos (8 RF, 3 RNF) | 🟦 Doc | José Eduardo |
| Definir o recurso de IA: entrada, saída e plano B | 🟪 IA | Eduardo |
| Escrever histórias de usuário HU01–HU05 | 🟦 Doc | José Eduardo |
| Escrever casos de uso UC01–UC03 | 🟦 Doc | José Eduardo |
| Transformar histórias em cartões no backlog | ⬜ Entrega | José Wellington |

### Sprint 2 — Projeto e desenvolvimento (quarta e quinta, 21–22/10)

| Cartão | Etiqueta | Responsável |
|---|---|---|
| Modelo entidade-relacionamento | 🟥 Banco | Matheus |
| Script SQL + dados de exemplo (pontos, eventos, avaliações) | 🟥 Banco | Matheus |
| Diagrama de classes | 🟩 Back | Eduardo |
| Protótipo das telas | 🟨 Front | Kelvin |
| Base do projeto: conexão PDO, layout, CSS | 🟩 Back / 🟨 Front | Eduardo / Kelvin |
| **RF01** Cadastro e login com perfil | 🟩 Back | Eduardo |
| **HU04 / RF02** CRUD de pontos turísticos (guia) | 🟩 Back | Matheus |
| **HU05 / RF03** CRUD de eventos (organizador) | 🟩 Back | Kelvin |
| **HU03 / RF04** Listagem e filtros de pontos | 🟨 Front | Kelvin |
| **RF05** Agenda de eventos com filtro | 🟨 Front | Kelvin |
| **HU03 / RF06** Avaliação de pontos | 🟩 Back | Matheus |
| **HU01 / RF07** Assistente de IA (Gemini) | 🟪 IA | Eduardo |
| **RNF02** Plano B sem IA (PlanejadorLocal) | 🟪 IA | Eduardo |
| **HU02 / RF08** Salvar e listar "Meus roteiros" | 🟩 Back | Eduardo |
| README do projeto | 🟦 Doc | José Eduardo |

### Sprint 3 — QA e entrega (sexta, 23/10)

| Cartão | Etiqueta | Responsável |
|---|---|---|
| Plano de testes | 🟧 QA | José Wellington |
| Executar casos de teste CT-01 a CT-15 | 🟧 QA | José Wellington + todos |
| Rodar testes automatizados (`php tests/testes_unitarios.php`) | 🟧 QA | José Wellington |
| Relatório de bugs e correções | 🟧 QA | José Wellington |
| Matriz de rastreabilidade | 🟧 QA | José Eduardo |
| Gerar chave do Gemini e testar a IA de ponta a ponta | 🟪 IA | Eduardo |
| Ensaiar o pitch de 5 minutos | ⬜ Entrega | Todos |
| Demo ao vivo (com plano B pronto) | ⬜ Entrega | Eduardo + Matheus |

### Backlog (ideias para depois do hackathon)

- Mapa interativo com os pontos do roteiro
- Upload de fotos dos pontos turísticos
- Exportar roteiro em PDF / compartilhar no WhatsApp
- Ajustar roteiro conversando com a IA ("troque o dia 2 por algo mais calmo")
- Previsão do tempo e tábua de marés no roteiro
- Moderação de comentários

## 3.4 Definição de pronto (DoD)

Um cartão só vai para **Feito** quando:
1. o código está no GitHub (branch `main` ou PR aprovado);
2. os critérios de aceitação da história foram verificados;
3. o caso de teste correspondente passou (ver docs/07);
4. não tem chave de API nem senha no código.
