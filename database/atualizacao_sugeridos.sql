-- =====================================================================
-- Rota Alagoas — 2ª atualização dos pontos (sugestões da planilha conferidas)
-- Rode DEPOIS de atualizacao_pontos_planilha.sql. Pode ser executado mais de uma vez.
-- Terminal: /opt/lampp/bin/mysql -u root rota_alagoas < database/atualizacao_sugeridos.sql
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Bairro de Jaraguá', 'Maceió', 'Jaraguá', 'historia', 'Bairro do antigo porto de Maceió, com sobrados do fim do século XIX construídos por comerciantes portugueses. Foi reduto boêmio por décadas e hoje é área tombada.', NULL, 90, 0, 'gratuito', 0, 'noite', 'cultura;historia;vida_noturna', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Bairro de Jaraguá' AND cidade = 'Maceió');

INSERT INTO pontos_turisticos (nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por)
SELECT 'Praia do Sobral', 'Maceió', 'Trapiche da Barra', 'praia', 'Praia urbana na região do Trapiche da Barra, com ondas para surfe dos dois lados; melhor perto da maré cheia.', NULL, 90, 0, 'gratuito', 0, 'manha', 'praia;esporte', NULL, NULL, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM pontos_turisticos WHERE nome = 'Praia do Sobral' AND cidade = 'Maceió');

