-- =====================================================================
-- Rota Alagoas — atualização dos pontos turísticos a partir da planilha
-- Use em um banco que JÁ ESTÁ em uso (não apaga usuários, roteiros nem avaliações).
-- phpMyAdmin: selecione o banco rota_alagoas > aba "Importar" > este arquivo > Executar.
-- Pode ser executado mais de uma vez: colunas e pontos que já existem são mantidos.
-- (Instalação do zero: use rota_alagoas.sql, que já inclui tudo isto.)
-- =====================================================================

SET NAMES utf8mb4;

-- 1. Novas colunas (dados da planilha)
ALTER TABLE pontos_turisticos
    ADD COLUMN IF NOT EXISTS bairro         VARCHAR(80) NULL AFTER cidade,
    ADD COLUMN IF NOT EXISTS faixa_preco    ENUM('gratuito','barato','medio','caro','pago') NULL AFTER custo_estimado,
    ADD COLUMN IF NOT EXISTS depende_mare   TINYINT(1) NOT NULL DEFAULT 0 AFTER faixa_preco,
    ADD COLUMN IF NOT EXISTS melhor_periodo ENUM('manha','tarde','fim_de_tarde','noite','qualquer') NOT NULL DEFAULT 'qualquer' AFTER depende_mare,
    ADD COLUMN IF NOT EXISTS tags           VARCHAR(255) NULL AFTER melhor_periodo,
    ADD COLUMN IF NOT EXISTS latitude       DECIMAL(9,6) NULL AFTER tags,
    ADD COLUMN IF NOT EXISTS longitude      DECIMAL(9,6) NULL AFTER latitude;

-- 2. Completa os 25 pontos originais (inclui os 6 que também estavam na planilha)
UPDATE pontos_turisticos SET bairro = 'Pajuçara', faixa_preco = 'pago', depende_mare = 1, melhor_periodo = 'manha', tags = 'praia;natureza;familia' WHERE id = 1 AND nome = 'Piscinas naturais da Pajuçara';
UPDATE pontos_turisticos SET bairro = 'Ponta Verde', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'fim_de_tarde', tags = 'praia;gastronomia;familia' WHERE id = 2 AND nome = 'Orla de Ponta Verde e Jatiúca';
UPDATE pontos_turisticos SET bairro = 'Pajuçara', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'noite', tags = 'compras;cultura' WHERE id = 3 AND nome = 'Feirinha de Artesanato da Pajuçara';
UPDATE pontos_turisticos SET bairro = 'Centro', faixa_preco = 'barato', depende_mare = 0, melhor_periodo = 'manha', tags = 'cultura;historia' WHERE id = 4 AND nome = 'Museu Théo Brandão';
UPDATE pontos_turisticos SET bairro = 'Centro', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'manha', tags = 'historia;cultura' WHERE id = 5 AND nome = 'Museu Palácio Floriano Peixoto (MUPA)';
UPDATE pontos_turisticos SET bairro = 'Pontal da Barra', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'tarde', tags = 'compras;cultura' WHERE id = 6 AND nome = 'Pontal da Barra e renda filé';
UPDATE pontos_turisticos SET bairro = 'Pontal da Barra', faixa_preco = 'medio', depende_mare = 0, melhor_periodo = 'manha', tags = 'natureza;familia' WHERE id = 7 AND nome = 'Passeio das 9 Ilhas';
UPDATE pontos_turisticos SET bairro = 'Ipioca', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'manha', tags = 'praia;natureza' WHERE id = 8 AND nome = 'Praia de Ipioca';
UPDATE pontos_turisticos SET bairro = 'Garça Torta', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'manha', tags = 'praia;natureza;tranquilidade' WHERE id = 9 AND nome = 'Praia de Garça Torta';
UPDATE pontos_turisticos SET bairro = 'Jatiúca', faixa_preco = 'medio', depende_mare = 0, melhor_periodo = 'noite', tags = 'gastronomia;vida_noturna' WHERE id = 10 AND nome = 'Roteiro gastronômico da Jatiúca';
UPDATE pontos_turisticos SET bairro = 'Centro', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'manha', tags = 'historia;cultura;religiao' WHERE id = 11 AND nome = 'Catedral Metropolitana de Maceió';
UPDATE pontos_turisticos SET bairro = 'Centro', faixa_preco = 'barato', depende_mare = 0, melhor_periodo = 'tarde', tags = 'cultura;historia' WHERE id = 12 AND nome = 'Teatro Deodoro';
UPDATE pontos_turisticos SET bairro = 'Francês', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'manha', tags = 'praia;esporte;familia' WHERE id = 13 AND nome = 'Praia do Francês';
UPDATE pontos_turisticos SET bairro = 'Centro Histórico', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'manha', tags = 'historia;cultura;religiao' WHERE id = 14 AND nome = 'Centro Histórico de Marechal Deodoro';
UPDATE pontos_turisticos SET bairro = NULL, faixa_preco = 'barato', depende_mare = 0, melhor_periodo = 'tarde', tags = 'praia;natureza;fotografia' WHERE id = 15 AND nome = 'Praia do Gunga e Mirante';
UPDATE pontos_turisticos SET bairro = NULL, faixa_preco = 'medio', depende_mare = 0, melhor_periodo = 'manha', tags = 'natureza;aventura;praia;fotografia' WHERE id = 16 AND nome = 'Praia de Carro Quebrado';
UPDATE pontos_turisticos SET bairro = NULL, faixa_preco = 'barato', depende_mare = 1, melhor_periodo = 'manha', tags = 'praia;natureza' WHERE id = 17 AND nome = 'Ilha da Croa';
UPDATE pontos_turisticos SET bairro = NULL, faixa_preco = 'medio', depende_mare = 1, melhor_periodo = 'manha', tags = 'praia;natureza;tranquilidade' WHERE id = 18 AND nome = 'Piscinas naturais de São Miguel dos Milagres';
UPDATE pontos_turisticos SET bairro = NULL, faixa_preco = 'barato', depende_mare = 0, melhor_periodo = 'manha', tags = 'natureza;familia' WHERE id = 19 AND nome = 'Santuário do Peixe-Boi';
UPDATE pontos_turisticos SET bairro = NULL, faixa_preco = 'pago', depende_mare = 1, melhor_periodo = 'manha', tags = 'praia;natureza;aventura;familia' WHERE id = 20 AND nome = 'Galés de Maragogi';
UPDATE pontos_turisticos SET bairro = 'Centro Histórico', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'manha', tags = 'historia;cultura;religiao' WHERE id = 21 AND nome = 'Centro Histórico de Penedo';
UPDATE pontos_turisticos SET bairro = NULL, faixa_preco = 'medio', depende_mare = 0, melhor_periodo = 'manha', tags = 'natureza;aventura' WHERE id = 22 AND nome = 'Foz do Rio São Francisco';
UPDATE pontos_turisticos SET bairro = 'Centro', faixa_preco = 'gratuito', depende_mare = 0, melhor_periodo = 'tarde', tags = 'historia;cultura' WHERE id = 23 AND nome = 'Centro Histórico de Piranhas';
UPDATE pontos_turisticos SET bairro = NULL, faixa_preco = 'caro', depende_mare = 0, melhor_periodo = 'manha', tags = 'natureza;aventura' WHERE id = 24 AND nome = 'Cânions do Rio São Francisco';
UPDATE pontos_turisticos SET bairro = 'Serra da Barriga', faixa_preco = 'barato', depende_mare = 0, melhor_periodo = 'manha', tags = 'historia;cultura' WHERE id = 25 AND nome = 'Parque Memorial Quilombo dos Palmares';

-- 3. Pontos novos da planilha (só insere se ainda não existir um com o mesmo nome e cidade)
INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Ponta Verde', 'Maceió', 'Ponta Verde', 'praia', 'Uma das praias mais famosas da capital.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;natureza;gastronomia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Ponta Verde' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Pajuçara', 'Maceió', 'Pajuçara', 'praia', 'Orla urbana com calçadão e feirinha de artesanato; ponto de saída das jangadas.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;compras;familia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Pajuçara' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Jatiúca', 'Maceió', 'Jatiúca', 'praia', 'Bares, restaurantes e orla movimentada.', NULL, 120, 0.0, 'gratuito', 0, 'tarde', 'praia;gastronomia;vida_noturna', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Jatiúca' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Cruz das Almas', 'Maceió', 'Cruz das Almas', 'praia', 'Ondas fortes em vários trechos; procurada para surfe.', NULL, 90, 0.0, 'gratuito', 0, 'manha', 'praia;esporte;aventura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Cruz das Almas' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Jacarecica', 'Maceió', 'Jacarecica', 'praia', 'Faixa de areia com trechos mais tranquilos.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;natureza;tranquilidade', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Jacarecica' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Guaxuma', 'Maceió', 'Guaxuma', 'praia', 'Clima de praia de bairro.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;tranquilidade', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Guaxuma' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Riacho Doce', 'Maceió', 'Riacho Doce', 'praia', 'Conhecida pelo ambiente simples e tradicional.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;cultura;tranquilidade', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Riacho Doce' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Mirante São Gonçalo', 'Maceió', 'Farol', 'natureza', 'Vista panorâmica da cidade.', NULL, 30, 0.0, 'gratuito', 0, 'fim_de_tarde', 'fotografia;cultura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Mirante São Gonçalo' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Pavilhão do Artesanato', 'Maceió', NULL, 'compras', 'Artesanato e lembranças regionais.', NULL, 60, 0.0, 'gratuito', 0, 'tarde', 'compras;cultura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Pavilhão do Artesanato' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Mercado do Artesanato', 'Maceió', 'Levada', 'compras', 'Produtos e trabalhos artesanais.', NULL, 60, 0.0, 'gratuito', 0, 'manha', 'compras;cultura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Mercado do Artesanato' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Conjunto Franciscano de Santa Maria Madalena', 'Marechal Deodoro', 'Centro Histórico', 'historia', 'Inclui a Igreja de Santa Maria Madalena, a Igreja da Ordem Terceira de São Benedito e o Museu de Arte Sacra Dom Ranulpho.', NULL, 90, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Conjunto Franciscano de Santa Maria Madalena' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Casa de Marechal Deodoro', 'Marechal Deodoro', 'Centro Histórico', 'cultura', 'Museu instalado na casa onde nasceu o Proclamador da República.', NULL, 45, 0.0, NULL, 0, 'manha', 'cultura;historia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Casa de Marechal Deodoro' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Palácio Provincial', 'Marechal Deodoro', 'Centro Histórico', 'historia', 'Antigo prédio do governo da província, hoje aberto para visitação.', NULL, 45, 0.0, NULL, 0, 'manha', 'cultura;historia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Palácio Provincial' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Igreja do Rosário dos Pretos', 'Marechal Deodoro', 'Centro Histórico', 'historia', 'Templo histórico do século XVIII construído por e para pessoas negras.', NULL, 30, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Igreja do Rosário dos Pretos' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Polo Gastronômico da Massagueira', 'Marechal Deodoro', 'Massagueira', 'gastronomia', 'Bares e restaurantes à beira da lagoa, conhecidos por pratos como sururu e siri-mole.', NULL, 120, 60.0, 'medio', 0, 'tarde', 'gastronomia;cultura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Polo Gastronômico da Massagueira' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Passeio de Lancha na Lagoa', 'Marechal Deodoro', NULL, 'natureza', 'Rota que passa pela Ilha de Santa Rita e pela Ilha do Paraíso.', NULL, 180, 80.0, 'pago', 0, 'manha', 'natureza;familia;aventura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Passeio de Lancha na Lagoa' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Piscinas Naturais do Francês', 'Marechal Deodoro', 'Francês', 'praia', 'Trecho protegido por recifes, com águas calmas.', NULL, 120, 0.0, 'gratuito', 1, 'manha', 'praia;natureza;familia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Piscinas Naturais do Francês' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Ponto dos Surfistas', 'Marechal Deodoro', 'Francês', 'praia', 'Trecho da Praia do Francês com ondas fortes, procurado por surfistas.', NULL, 90, 0.0, 'gratuito', 0, 'manha', 'praia;esporte;aventura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Ponto dos Surfistas' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'La Rue (Rua La Rue)', 'Marechal Deodoro', 'Francês', 'gastronomia', 'Rua de pedestres no centrinho da praia, com cafés, lojas de artesanato, bares e restaurantes; ideal para fim de tarde ou jantar.', NULL, 90, 60.0, 'medio', 0, 'noite', 'gastronomia;compras;vida_noturna', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'La Rue (Rua La Rue)' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia do Saco', 'Marechal Deodoro', NULL, 'praia', 'Mais tranquila e rústica, para quem quer fugir do movimento.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;natureza;tranquilidade', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia do Saco' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Prainha da Barra Nova', 'Marechal Deodoro', 'Barra Nova', 'praia', 'Encontro da lagoa com o mar, ótima para banhos calmos.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;natureza;familia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Prainha da Barra Nova' AND cidade = 'Marechal Deodoro');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia da Barra de São Miguel', 'Barra de São Miguel', NULL, 'praia', 'Praia principal, protegida por recifes.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;familia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia da Barra de São Miguel' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia do Niquim', 'Barra de São Miguel', NULL, 'praia', 'Praia da Barra de São Miguel, perto da foz do Rio Niquim.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;tranquilidade', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia do Niquim' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia das Conchas', 'Barra de São Miguel', NULL, 'praia', 'Praia tranquila na Barra de São Miguel.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia das Conchas' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Mirante da Barra', 'Barra de São Miguel', NULL, 'natureza', 'Na rodovia AL-101; vista panorâmica da orla, da Lagoa do Roteiro e da Praia do Gunga.', NULL, 30, 15.0, 'pago', 0, 'tarde', 'fotografia;natureza', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Mirante da Barra' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Letreiro Amo São Miguel', 'Barra de São Miguel', NULL, 'natureza', 'Na orla da praia principal; parada para fotos com o mar ao fundo.', NULL, 15, 0.0, 'gratuito', 0, 'qualquer', 'fotografia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Letreiro Amo São Miguel' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Pracinha do Vivá', 'Barra de São Miguel', 'Barra Mar', 'natureza', 'Espaço charmoso e bastante fotografado.', NULL, 20, 0.0, 'gratuito', 0, 'fim_de_tarde', 'fotografia;cultura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Pracinha do Vivá' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Vila Niquim', 'Barra de São Miguel', 'Centro', 'gastronomia', 'Complexo gastronômico com restaurantes e bares de culinária regional e variada.', NULL, 90, 60.0, 'medio', 0, 'noite', 'gastronomia;vida_noturna', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Vila Niquim' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Povoado de Palateia', 'Barra de São Miguel', 'Palateia', 'natureza', 'Comunidade tradicional de maricultura (ostras e mariscos) e extração de própolis vermelha; passeios ecológicos e de barco.', NULL, 120, 50.0, 'pago', 0, 'manha', 'natureza;cultura;gastronomia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Povoado de Palateia' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Passeio de Lancha ou Barco (Rio Niquim)', 'Barra de São Miguel', NULL, 'natureza', 'Percorre a foz do Rio Niquim, bancos de areia, manguezais e recifes, até a Praia do Gunga ou a Lagoa do Roteiro.', NULL, 180, 80.0, 'pago', 1, 'manha', 'natureza;aventura;familia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Passeio de Lancha ou Barco (Rio Niquim)' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Passeio de Buggy ou Quadriciclo (Barra)', 'Barra de São Miguel', NULL, 'aventura', 'Explora as falésias coloridas e as trilhas da região.', NULL, 120, 120.0, 'pago', 0, 'manha', 'aventura;natureza', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Passeio de Buggy ou Quadriciclo (Barra)' AND cidade = 'Barra de São Miguel');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Igreja de Nossa Senhora da Corrente', 'Penedo', 'Centro Histórico', 'historia', 'Construída em 1729; estilo barroco/rococó, talha dourada, azulejos portugueses e um porão que servia de esconderijo para pessoas escravizadas.', NULL, 40, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Igreja de Nossa Senhora da Corrente' AND cidade = 'Penedo');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Convento e Igreja de Santa Maria dos Anjos', 'Penedo', 'Centro Histórico', 'historia', 'Complexo franciscano iniciado em 1660, com rico acervo de arte sacra no museu interno.', NULL, 60, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Convento e Igreja de Santa Maria dos Anjos' AND cidade = 'Penedo');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Theatro Sete de Setembro', 'Penedo', 'Centro Histórico', 'historia', 'Inaugurado em 1884; considerado o teatro mais antigo de Alagoas e ainda em funcionamento.', NULL, 30, 0.0, NULL, 0, 'tarde', 'cultura;historia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Theatro Sete de Setembro' AND cidade = 'Penedo');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Museu do Paço Imperial', 'Penedo', 'Centro Histórico', 'cultura', 'Antigo palácio de governo onde Dom Pedro II se hospedou em 1859; guarda peças históricas.', NULL, 60, 0.0, NULL, 0, 'manha', 'cultura;historia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Museu do Paço Imperial' AND cidade = 'Penedo');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Catedral de Nossa Senhora do Rosário', 'Penedo', 'Centro Histórico', 'historia', 'Templo no coração da cidade, com herança arquitetônica dos séculos XVII a XIX.', NULL, 30, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Catedral de Nossa Senhora do Rosário' AND cidade = 'Penedo');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Igreja de São Gonçalo Garcia', 'Penedo', 'Centro Histórico', 'historia', 'Templo histórico tombado pelo IPHAN, de grande valor artístico e cultural.', NULL, 30, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Igreja de São Gonçalo Garcia' AND cidade = 'Penedo');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Mirante da Rocheira', 'Penedo', NULL, 'natureza', 'Na parte alta da cidade; vista do Rio São Francisco e do pôr do sol.', NULL, 40, 0.0, 'gratuito', 0, 'fim_de_tarde', 'fotografia;natureza', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Mirante da Rocheira' AND cidade = 'Penedo');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Vila Dão Pêdu', 'Penedo', 'Centro Histórico', 'gastronomia', 'Espaço no Centro Histórico para curtir a noite, a gastronomia e a cultura local.', NULL, 90, 60.0, 'medio', 0, 'noite', 'gastronomia;vida_noturna;cultura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Vila Dão Pêdu' AND cidade = 'Penedo');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Passeio de Barco pelo Rio São Francisco', 'Penedo', NULL, 'natureza', 'Saídas para ilhas, bancos de areia e mirantes ao longo do Velho Chico.', NULL, 180, 80.0, 'pago', 0, 'manha', 'natureza;familia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Passeio de Barco pelo Rio São Francisco' AND cidade = 'Penedo');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia do Pontal do Peba', 'Piaçabuçu', 'Pontal do Peba', 'praia', 'Praia marítima nos arredores de Penedo (aprox. 40 km).', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;natureza', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia do Pontal do Peba' AND cidade = 'Piaçabuçu');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Burgalhau', 'Maragogi', NULL, 'praia', 'Praia mais reservada, com boa infraestrutura; famosa pelo ponto de apoio de bugueiros.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;tranquilidade', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Burgalhau' AND cidade = 'Maragogi');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Ponta de Mangue', 'Maragogi', 'Ponta de Mangue', 'praia', 'Águas calmas que parecem uma piscina natural gigante; ideal para famílias.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;familia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Ponta de Mangue' AND cidade = 'Maragogi');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia de Antunes', 'Maragogi', 'Antunes', 'praia', 'Famosa pelo mar azul-turquesa, areia branquinha, coqueiros tortos e redes dentro da água.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;fotografia;natureza', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia de Antunes' AND cidade = 'Maragogi');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Caminho de Moisés (Barra Grande)', 'Maragogi', 'Barra Grande', 'natureza', 'Banco de areia que se abre no meio do mar na maré baixa, permitindo caminhar mar adentro.', NULL, 90, 0.0, 'gratuito', 1, 'manha', 'natureza;fotografia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Caminho de Moisés (Barra Grande)' AND cidade = 'Maragogi');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia do Xaréu', 'Maragogi', NULL, 'praia', 'Conhecida como ''Praia da Bruna''; águas calmas e mornas, cenários para fotos com balanços e redes.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;fotografia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia do Xaréu' AND cidade = 'Maragogi');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Piscinas Naturais de Taocas e Barra Grande', 'Maragogi', NULL, 'natureza', 'Outras opções de recifes de corais para mergulho na maré baixa.', NULL, 180, 80.0, 'pago', 1, 'manha', 'natureza;aventura', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Piscinas Naturais de Taocas e Barra Grande' AND cidade = 'Maragogi');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Passeio de Buggy (Maragogi)', 'Maragogi', NULL, 'aventura', 'Roteiro pelo litoral norte ou sul, passando por praias e mirantes.', NULL, 240, 120.0, 'pago', 0, 'manha', 'aventura;praia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Passeio de Buggy (Maragogi)' AND cidade = 'Maragogi');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Mirante de Maragogi (Alto do Cruzeiro)', 'Maragogi', NULL, 'natureza', 'Vista panorâmica da cidade e do mar, com letreiros para fotos.', NULL, 30, 0.0, 'gratuito', 0, 'fim_de_tarde', 'fotografia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Mirante de Maragogi (Alto do Cruzeiro)' AND cidade = 'Maragogi');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Ruínas da Igreja de São Bento', 'Maragogi', 'São Bento', 'historia', 'Vestígios históricos com vista privilegiada da região.', NULL, 30, 0.0, 'gratuito', 0, 'tarde', 'cultura;historia', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Ruínas da Igreja de São Bento' AND cidade = 'Maragogi');
