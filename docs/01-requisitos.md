# 1. Documento de Requisitos — Rota Alagoas

**Hackathon ADS · Anhanguera Maceió · Grupo 3 — Turismo e cultura**
Equipe: Eduardo Lucas (cabeça de chave), José Eduardo Souza da Silva, José Wellington da Silva Vieira, Kelvin Gadelha, Matheus.

## 1.1 Visão geral

O **Rota Alagoas** é um sistema web que reúne pontos turísticos, roteiros, eventos e avaliações de Alagoas em um só lugar. O visitante informa quantos dias tem e o que gosta, e um **assistente de IA** monta um roteiro dia a dia usando apenas os lugares e eventos cadastrados no banco de dados.

## 1.2 O problema

- As informações turísticas de Alagoas estão espalhadas (Instagram, sites de agências, blogs, grupos de WhatsApp).
- O turista que chega com poucos dias não sabe o que cabe no tempo dele, nem o que combina com o seu perfil (família com crianças, casal, aventura, cultura).
- Guias locais e organizadores de eventos não têm um canal simples para divulgar o que oferecem.
- Eventos culturais pequenos (feiras, festivais, apresentações) passam despercebidos pelos visitantes.

## 1.3 Usuários (atores)

| Ator | Quem é | O que precisa |
|---|---|---|
| **Turista** | Visitante de Alagoas, de fora ou do interior | Descobrir lugares, ver eventos, montar um roteiro que caiba nos dias que tem, avaliar o que visitou |
| **Guia local** | Pessoa que conhece a região e trabalha com turismo | Cadastrar e manter pontos turísticos com informações confiáveis |
| **Organizador de eventos** | Produtor cultural, associação, prefeitura | Divulgar eventos com data, local e preço |
| **Assistente de IA** (ator sistema) | Google Gemini | Receber o perfil do visitante e os dados do banco e devolver um roteiro estruturado |

## 1.4 Requisitos funcionais

| ID | Requisito | Ator | Prioridade |
|---|---|---|---|
| **RF01** | O sistema deve permitir cadastro e login de usuários com o perfil **turista**, **guia** ou **organizador**. | Todos | Alta |
| **RF02** | O guia deve poder cadastrar, editar e excluir **pontos turísticos** (nome, cidade, categoria, descrição, endereço, duração média da visita e custo estimado). | Guia | Alta |
| **RF03** | O organizador deve poder cadastrar, editar e excluir **eventos** (título, descrição, cidade, local, data/hora de início e fim, categoria e preço). | Organizador | Alta |
| **RF04** | O sistema deve listar os pontos turísticos com **filtro por cidade e categoria** e busca por nome, exibindo a nota média. | Todos | Alta |
| **RF05** | O sistema deve listar os **eventos futuros**, com filtro por cidade e período. | Todos | Média |
| **RF06** | O turista deve poder **avaliar** um ponto turístico com nota de 1 a 5 e comentário (uma avaliação por ponto, que pode ser atualizada). | Turista | Média |
| **RF07** | O sistema deve ter um **assistente de IA** que monta um roteiro dia a dia conforme o **perfil** (interesses, companhia, ritmo, orçamento) e o **tempo** do visitante (data de chegada e número de dias), usando somente pontos e eventos cadastrados. | Turista + IA | Alta |
| **RF08** | O turista deve poder **salvar, consultar e excluir** os roteiros gerados ("Meus roteiros"). | Turista | Média |

## 1.5 Requisitos não funcionais

| ID | Requisito | Como verificar |
|---|---|---|
| **RNF01 — Segurança** | Senhas guardadas com `password_hash` (bcrypt); todas as consultas com PDO e *prepared statements*; saída HTML escapada; formulários com token CSRF; **a chave da API fica em `config/config.php`, que está no `.gitignore` e nunca vai para o GitHub**. | Revisão de código, CT-01, CT-11, CT-12 |
| **RNF02 — Disponibilidade (Plano B)** | Se a IA falhar (sem chave, sem internet, *timeout* de 25 s, resposta inválida), o sistema gera o roteiro com um **algoritmo local** a partir das notas e categorias do banco, e avisa o usuário. O sistema nunca fica sem resposta. | CT-08 |
| **RNF03 — Usabilidade** | Interface em português, responsiva (funciona no celular a partir de 360 px) e roteiro entregue em até 25 segundos. | CT-13 |

## 1.6 Regras de negócio

- **RN01** — Só o perfil *guia* cadastra pontos turísticos; só o perfil *organizador* cadastra eventos. Cada um só edita o que cadastrou.
- **RN02** — Só o perfil *turista* avalia pontos turísticos. Um turista tem no máximo uma avaliação por ponto.
- **RN03** — O roteiro tem de 1 a 7 dias. A IA só pode sugerir pontos e eventos que existem no banco (o sistema descarta IDs inventados).
- **RN04** — Só entram no roteiro eventos que acontecem dentro do período da viagem.
- **RN05** — Ritmo define a carga diária: tranquilo ≈ 4 h, moderado ≈ 6 h, intenso ≈ 8 h de atividades.

## 1.7 O recurso de IA

| | |
|---|---|
| **O que entra** | Perfil do visitante (dias, data de chegada, cidade-base, interesses, companhia, ritmo, orçamento) + lista de pontos turísticos do banco (id, nome, cidade, categoria, duração, custo, nota média) + eventos do período. |
| **O que sai** | JSON com título, resumo e, para cada dia, a lista de atividades em ordem (horário, `ponto_id` ou `evento_id`, dica). O sistema valida o JSON, descarta IDs que não existem e grava o roteiro nas tabelas `roteiros` e `roteiro_itens`. |
| **Se falhar** | Plano B: algoritmo local escolhe os pontos com as categorias de interesse e maior nota, agrupa por cidade e distribui pelos dias conforme o ritmo. O roteiro é salvo com `origem = 'padrao'` e a tela mostra o aviso "Roteiro montado sem IA". |
| **Modelo** | Google Gemini (`gemini-2.5-flash`, configurável), chamado pela API REST com `responseMimeType: application/json`. |

## 1.8 Fora do escopo (nesta versão)

Pagamento e reserva, mapa interativo, aplicativo nativo, upload de fotos, moderação de comentários.
