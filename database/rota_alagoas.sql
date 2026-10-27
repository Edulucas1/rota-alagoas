-- =====================================================================
-- Rota Alagoas — Hackathon ADS Anhanguera Maceió — Grupo 3
-- Banco de dados MySQL / MariaDB (XAMPP)
-- Importe pelo phpMyAdmin: aba "Importar" > escolher este arquivo > Executar
-- =====================================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS rota_alagoas;
CREATE DATABASE rota_alagoas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rota_alagoas;

-- ---------------------------------------------------------------------
-- Tabelas
-- ---------------------------------------------------------------------

CREATE TABLE usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    senha_hash  VARCHAR(255) NOT NULL,
    perfil      ENUM('turista','guia','organizador') NOT NULL DEFAULT 'turista',
    criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pontos_turisticos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(150) NOT NULL,
    cidade          VARCHAR(80)  NOT NULL,
    bairro          VARCHAR(80)  NULL,
    categoria       ENUM('praia','natureza','historia','cultura','gastronomia','aventura','compras') NOT NULL,
    descricao       TEXT NOT NULL,
    endereco        VARCHAR(200) NULL,
    duracao_min     SMALLINT NOT NULL DEFAULT 120,
    custo_estimado  DECIMAL(8,2) NOT NULL DEFAULT 0,
    faixa_preco     ENUM('gratuito','barato','medio','caro','pago') NULL,
    depende_mare    TINYINT(1) NOT NULL DEFAULT 0,
    melhor_periodo  ENUM('manha','tarde','fim_de_tarde','noite','qualquer') NOT NULL DEFAULT 'qualquer',
    tags            VARCHAR(255) NULL,          -- interesses separados por ';' (usados pela IA)
    latitude        DECIMAL(9,6) NULL,
    longitude       DECIMAL(9,6) NULL,
    criado_por      INT NOT NULL,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ponto_guia FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    INDEX idx_ponto_cidade (cidade),
    INDEX idx_ponto_categoria (categoria)
) ENGINE=InnoDB;

CREATE TABLE eventos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(150) NOT NULL,
    descricao       TEXT NOT NULL,
    cidade          VARCHAR(80)  NOT NULL,
    local           VARCHAR(150) NOT NULL,
    inicio          DATETIME NOT NULL,
    fim             DATETIME NOT NULL,
    categoria       ENUM('musica','cultura','gastronomia','festa','feira','esporte') NOT NULL,
    preco           DECIMAL(8,2) NOT NULL DEFAULT 0,
    organizador_id  INT NOT NULL,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evento_org FOREIGN KEY (organizador_id) REFERENCES usuarios(id),
    INDEX idx_evento_inicio (inicio)
) ENGINE=InnoDB;

CREATE TABLE avaliacoes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    ponto_id    INT NOT NULL,
    nota        TINYINT NOT NULL,
    comentario  VARCHAR(500) NULL,
    criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aval_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_aval_ponto   FOREIGN KEY (ponto_id)   REFERENCES pontos_turisticos(id) ON DELETE CASCADE,
    CONSTRAINT uq_aval UNIQUE (usuario_id, ponto_id),
    CONSTRAINT ck_nota CHECK (nota BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE roteiros (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id   INT NOT NULL,
    titulo       VARCHAR(150) NOT NULL,
    resumo       TEXT NULL,
    data_inicio  DATE NOT NULL,
    dias         TINYINT NOT NULL,
    cidade_base  VARCHAR(80) NOT NULL,
    interesses   VARCHAR(200) NOT NULL,
    companhia    ENUM('sozinho','casal','familia','amigos') NOT NULL,
    ritmo        ENUM('tranquilo','moderado','intenso') NOT NULL,
    orcamento    ENUM('economico','medio','livre') NOT NULL,
    origem       ENUM('ia','padrao') NOT NULL,
    criado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_roteiro_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT ck_dias CHECK (dias BETWEEN 1 AND 7)
) ENGINE=InnoDB;

CREATE TABLE roteiro_itens (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    roteiro_id  INT NOT NULL,
    dia         TINYINT NOT NULL,
    ordem       TINYINT NOT NULL,
    horario     TIME NULL,
    ponto_id    INT NULL,
    evento_id   INT NULL,
    titulo      VARCHAR(150) NOT NULL,
    dica        VARCHAR(500) NULL,
    CONSTRAINT fk_item_roteiro FOREIGN KEY (roteiro_id) REFERENCES roteiros(id) ON DELETE CASCADE,
    CONSTRAINT fk_item_ponto   FOREIGN KEY (ponto_id)   REFERENCES pontos_turisticos(id) ON DELETE SET NULL,
    CONSTRAINT fk_item_evento  FOREIGN KEY (evento_id)  REFERENCES eventos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Dados de demonstração
-- Senha de todos os usuários de teste: 123456
-- Custos e durações são aproximados; eventos são fictícios.
-- ---------------------------------------------------------------------

INSERT INTO usuarios (id, nome, email, senha_hash, perfil) VALUES
(1, 'Carla Guia',            'guia@rota.al',  '$2y$10$jMczgHIdSudPKtcjmIgwquMVs09/pINHAnbEkHZyQ4m/OLD8aBgRO', 'guia'),
(2, 'Coletivo Cultural AL',  'org@rota.al',   '$2y$10$jMczgHIdSudPKtcjmIgwquMVs09/pINHAnbEkHZyQ4m/OLD8aBgRO', 'organizador'),
(3, 'Ana Turista',           'ana@rota.al',   '$2y$10$jMczgHIdSudPKtcjmIgwquMVs09/pINHAnbEkHZyQ4m/OLD8aBgRO', 'turista'),
(4, 'Bruno Viajante',        'bruno@rota.al', '$2y$10$jMczgHIdSudPKtcjmIgwquMVs09/pINHAnbEkHZyQ4m/OLD8aBgRO', 'turista'),
(5, 'Marcos Guia',           'guia2@rota.al', '$2y$10$jMczgHIdSudPKtcjmIgwquMVs09/pINHAnbEkHZyQ4m/OLD8aBgRO', 'guia');

-- Pontos 1–25: dados originais do projeto. Pontos 26 em diante: planilha de pesquisa do grupo
-- (rota-alagoas-pontos-turisticos.xlsx). Custos de itens 'pago' sem valor na planilha são estimativas.
INSERT INTO pontos_turisticos (id, nome, cidade, bairro, categoria, descricao, endereco, duracao_min, custo_estimado, faixa_preco, depende_mare, melhor_periodo, tags, latitude, longitude, criado_por) VALUES
(1, 'Piscinas naturais da Pajuçara', 'Maceió', 'Pajuçara', 'praia', 'Passeio de jangada até as piscinas naturais formadas pelos recifes, a cerca de 2 km da praia. Vá na maré baixa e leve máscara de mergulho para ver os peixes.', 'Orla da Pajuçara', 150, 50.0, 'pago', 1, 'manha', 'praia;natureza;familia', NULL, NULL, 1),
(2, 'Orla de Ponta Verde e Jatiúca', 'Maceió', 'Ponta Verde', 'praia', 'Calçadão com ciclovia, coqueiros e mar verde-azulado. Bom para caminhar no fim da tarde e tomar água de coco.', 'Av. Silvio Carlos Viana', 120, 0.0, 'gratuito', 0, 'fim_de_tarde', 'praia;gastronomia;familia', NULL, NULL, 1),
(3, 'Feirinha de Artesanato da Pajuçara', 'Maceió', 'Pajuçara', 'compras', 'Barracas com artesanato alagoano: renda filé, bordados, cerâmica, cachaças e doces regionais. Funciona melhor à noite.', 'Orla da Pajuçara', 60, 0.0, 'gratuito', 0, 'noite', 'compras;cultura', NULL, NULL, 1),
(4, 'Museu Théo Brandão', 'Maceió', 'Centro', 'cultura', 'Museu de antropologia e folclore da UFAL, em casarão na orla. Acervo de folguedos, como o Guerreiro e o Reisado, e arte popular alagoana.', 'Av. da Paz, Centro', 90, 10.0, 'barato', 0, 'manha', 'cultura;historia', NULL, NULL, 1),
(5, 'Museu Palácio Floriano Peixoto (MUPA)', 'Maceió', 'Centro', 'historia', 'Antiga sede do governo de Alagoas, com mobiliário de época e exposições sobre a história do estado.', 'Praça dos Martírios, Centro', 60, 0.0, 'gratuito', 0, 'manha', 'historia;cultura', NULL, NULL, 1),
(6, 'Pontal da Barra e renda filé', 'Maceió', 'Pontal da Barra', 'compras', 'Bairro de rendeiras às margens da Lagoa Mundaú. Ateliês de filé, a renda símbolo de Alagoas, e ponto de saída de passeios de barco.', 'Pontal da Barra', 90, 0.0, 'gratuito', 0, 'tarde', 'compras;cultura', NULL, NULL, 1),
(7, 'Passeio das 9 Ilhas', 'Maceió', 'Pontal da Barra', 'natureza', 'Passeio de barco pelas lagoas Mundaú e Manguaba, entre manguezais e ilhas, com parada para banho e almoço.', 'Saída pelo Pontal da Barra', 240, 80.0, 'medio', 0, 'manha', 'natureza;familia', NULL, NULL, 1),
(8, 'Praia de Ipioca', 'Maceió', 'Ipioca', 'praia', 'Praia tranquila no litoral norte de Maceió, com piscinas naturais na maré baixa e beach clubs.', 'Litoral norte, Ipioca', 180, 0.0, 'gratuito', 0, 'manha', 'praia;natureza', NULL, NULL, 5),
(9, 'Praia de Garça Torta', 'Maceió', 'Garça Torta', 'praia', 'Vila de pescadores com mar calmo e restaurantes pé na areia. Boa para famílias.', 'Litoral norte, Garça Torta', 120, 0.0, 'gratuito', 0, 'manha', 'praia;natureza;tranquilidade', NULL, NULL, 5),
(10, 'Roteiro gastronômico da Jatiúca', 'Maceió', 'Jatiúca', 'gastronomia', 'Restaurantes de frutos do mar e comida regional: sururu, peixe ao molho de coco, tapioca e cocada.', 'Jatiúca / Ponta Verde', 90, 80.0, 'medio', 0, 'noite', 'gastronomia;vida_noturna', NULL, NULL, 5),
(11, 'Catedral Metropolitana de Maceió', 'Maceió', 'Centro', 'historia', 'Igreja do século XIX na praça D. Pedro II, no centro histórico da capital.', 'Praça D. Pedro II, Centro', 40, 0.0, 'gratuito', 0, 'manha', 'historia;cultura;religiao', NULL, NULL, 1),
(12, 'Teatro Deodoro', 'Maceió', 'Centro', 'cultura', 'Teatro de 1910, um dos mais bonitos do Nordeste. Confira a programação antes de ir.', 'Praça Deodoro, Centro', 90, 30.0, 'barato', 0, 'tarde', 'cultura;historia', NULL, NULL, 1),
(13, 'Praia do Francês', 'Marechal Deodoro', 'Francês', 'praia', 'Uma das praias mais famosas de Alagoas: de um lado piscinas de águas calmas, do outro ondas boas para surf.', 'Praia do Francês', 240, 30.0, 'gratuito', 0, 'manha', 'praia;esporte;familia', NULL, NULL, 1),
(14, 'Centro Histórico de Marechal Deodoro', 'Marechal Deodoro', 'Centro Histórico', 'historia', 'Primeira capital de Alagoas. Casario colonial, igrejas e o convento franciscano de Santa Maria Madalena.', 'Centro, Marechal Deodoro', 120, 0.0, 'gratuito', 0, 'manha', 'historia;cultura;religiao', NULL, NULL, 1),
(15, 'Praia do Gunga e Mirante', 'Roteiro', NULL, 'praia', 'Praia entre o mar e a lagoa, cercada de coqueirais. O mirante no alto da falésia é um dos cartões-postais do estado.', 'Barra de São Miguel / Roteiro', 300, 40.0, 'barato', 0, 'tarde', 'praia;natureza;fotografia', NULL, NULL, 1),
(16, 'Praia de Carro Quebrado', 'Barra de Santo Antônio', NULL, 'natureza', 'Falésias coloridas e praia quase deserta, acessível de buggy.', 'Barra de Santo Antônio', 240, 60.0, 'medio', 0, 'manha', 'natureza;aventura;praia;fotografia', NULL, NULL, 5),
(17, 'Ilha da Croa', 'Barra de Santo Antônio', NULL, 'praia', 'Praia de areia branca e piscinas naturais, com acesso por balsa ou barco.', 'Barra de Santo Antônio', 240, 20.0, 'barato', 1, 'manha', 'praia;natureza', NULL, NULL, 5),
(18, 'Piscinas naturais de São Miguel dos Milagres', 'São Miguel dos Milagres', NULL, 'praia', 'Na Rota Ecológica: passeio de jangada até as piscinas naturais de águas transparentes.', 'Rota Ecológica dos Milagres', 240, 70.0, 'medio', 1, 'manha', 'praia;natureza;tranquilidade', NULL, NULL, 5),
(19, 'Santuário do Peixe-Boi', 'Porto de Pedras', NULL, 'natureza', 'Passeio de jangada pelo rio Tatuamunha para ver o peixe-boi marinho em área de preservação.', 'Rio Tatuamunha, Porto de Pedras', 120, 40.0, 'barato', 0, 'manha', 'natureza;familia', NULL, NULL, 5),
(20, 'Galés de Maragogi', 'Maragogi', NULL, 'praia', 'Piscinas naturais em alto-mar, a cerca de 6 km da costa, conhecidas como o Caribe brasileiro.', 'Maragogi', 300, 120.0, 'pago', 1, 'manha', 'praia;natureza;aventura;familia', NULL, NULL, 5),
(21, 'Centro Histórico de Penedo', 'Penedo', 'Centro Histórico', 'historia', 'Cidade às margens do São Francisco com igrejas barrocas, como a de Nossa Senhora dos Anjos, e casario tombado.', 'Centro, Penedo', 180, 0.0, 'gratuito', 0, 'manha', 'historia;cultura;religiao', NULL, NULL, 1),
(22, 'Foz do Rio São Francisco', 'Piaçabuçu', NULL, 'natureza', 'Passeio de barco até o encontro do Velho Chico com o mar, com dunas e área de preservação.', 'Piaçabuçu', 300, 90.0, 'medio', 0, 'manha', 'natureza;aventura', NULL, NULL, 1),
(23, 'Centro Histórico de Piranhas', 'Piranhas', 'Centro', 'historia', 'Cidade no sertão às margens do São Francisco, com casario colorido e história do cangaço.', 'Centro, Piranhas', 150, 0.0, 'gratuito', 0, 'tarde', 'historia;cultura', NULL, NULL, 5),
(24, 'Cânions do Rio São Francisco', 'Piranhas', NULL, 'aventura', 'Passeio de catamarã pelos cânions do Velho Chico, com paredões de pedra e parada para banho.', 'Saída de Piranhas', 300, 150.0, 'caro', 0, 'manha', 'natureza;aventura', NULL, NULL, 5),
(25, 'Parque Memorial Quilombo dos Palmares', 'União dos Palmares', 'Serra da Barriga', 'cultura', 'Na Serra da Barriga, onde existiu o Quilombo dos Palmares, liderado por Zumbi. Sítio histórico da resistência negra.', 'Serra da Barriga, União dos Palmares', 180, 10.0, 'barato', 0, 'manha', 'historia;cultura', NULL, NULL, 1),
(26, 'Praia de Ponta Verde', 'Maceió', 'Ponta Verde', 'praia', 'Uma das praias mais famosas da capital.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;natureza;gastronomia', NULL, NULL, 1),
(27, 'Praia de Pajuçara', 'Maceió', 'Pajuçara', 'praia', 'Orla urbana com calçadão e feirinha de artesanato; ponto de saída das jangadas.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;compras;familia', NULL, NULL, 1),
(28, 'Praia de Jatiúca', 'Maceió', 'Jatiúca', 'praia', 'Bares, restaurantes e orla movimentada.', NULL, 120, 0.0, 'gratuito', 0, 'tarde', 'praia;gastronomia;vida_noturna', NULL, NULL, 1),
(29, 'Praia de Cruz das Almas', 'Maceió', 'Cruz das Almas', 'praia', 'Ondas fortes em vários trechos; procurada para surfe.', NULL, 90, 0.0, 'gratuito', 0, 'manha', 'praia;esporte;aventura', NULL, NULL, 1),
(30, 'Praia de Jacarecica', 'Maceió', 'Jacarecica', 'praia', 'Faixa de areia com trechos mais tranquilos.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;natureza;tranquilidade', NULL, NULL, 1),
(31, 'Praia de Guaxuma', 'Maceió', 'Guaxuma', 'praia', 'Clima de praia de bairro.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;tranquilidade', NULL, NULL, 1),
(32, 'Praia de Riacho Doce', 'Maceió', 'Riacho Doce', 'praia', 'Conhecida pelo ambiente simples e tradicional.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;cultura;tranquilidade', NULL, NULL, 1),
(33, 'Mirante São Gonçalo', 'Maceió', 'Farol', 'natureza', 'Vista panorâmica da cidade.', NULL, 30, 0.0, 'gratuito', 0, 'fim_de_tarde', 'fotografia;cultura', NULL, NULL, 1),
(34, 'Pavilhão do Artesanato', 'Maceió', NULL, 'compras', 'Artesanato e lembranças regionais.', NULL, 60, 0.0, 'gratuito', 0, 'tarde', 'compras;cultura', NULL, NULL, 1),
(35, 'Mercado do Artesanato', 'Maceió', 'Levada', 'compras', 'Produtos e trabalhos artesanais.', NULL, 60, 0.0, 'gratuito', 0, 'manha', 'compras;cultura', NULL, NULL, 1),
(36, 'Conjunto Franciscano de Santa Maria Madalena', 'Marechal Deodoro', 'Centro Histórico', 'historia', 'Inclui a Igreja de Santa Maria Madalena, a Igreja da Ordem Terceira de São Benedito e o Museu de Arte Sacra Dom Ranulpho.', NULL, 90, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1),
(37, 'Casa de Marechal Deodoro', 'Marechal Deodoro', 'Centro Histórico', 'cultura', 'Museu instalado na casa onde nasceu o Proclamador da República.', NULL, 45, 0.0, NULL, 0, 'manha', 'cultura;historia', NULL, NULL, 1),
(38, 'Palácio Provincial', 'Marechal Deodoro', 'Centro Histórico', 'historia', 'Antigo prédio do governo da província, hoje aberto para visitação.', NULL, 45, 0.0, NULL, 0, 'manha', 'cultura;historia', NULL, NULL, 1),
(39, 'Igreja do Rosário dos Pretos', 'Marechal Deodoro', 'Centro Histórico', 'historia', 'Templo histórico do século XVIII construído por e para pessoas negras.', NULL, 30, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1),
(40, 'Polo Gastronômico da Massagueira', 'Marechal Deodoro', 'Massagueira', 'gastronomia', 'Bares e restaurantes à beira da lagoa, conhecidos por pratos como sururu e siri-mole.', NULL, 120, 60.0, 'medio', 0, 'tarde', 'gastronomia;cultura', NULL, NULL, 1),
(41, 'Passeio de Lancha na Lagoa', 'Marechal Deodoro', NULL, 'natureza', 'Rota que passa pela Ilha de Santa Rita e pela Ilha do Paraíso.', NULL, 180, 80.0, 'pago', 0, 'manha', 'natureza;familia;aventura', NULL, NULL, 1),
(42, 'Piscinas Naturais do Francês', 'Marechal Deodoro', 'Francês', 'praia', 'Trecho protegido por recifes, com águas calmas.', NULL, 120, 0.0, 'gratuito', 1, 'manha', 'praia;natureza;familia', NULL, NULL, 1),
(43, 'Ponto dos Surfistas', 'Marechal Deodoro', 'Francês', 'praia', 'Trecho da Praia do Francês com ondas fortes, procurado por surfistas.', NULL, 90, 0.0, 'gratuito', 0, 'manha', 'praia;esporte;aventura', NULL, NULL, 1),
(44, 'La Rue (Rua La Rue)', 'Marechal Deodoro', 'Francês', 'gastronomia', 'Rua de pedestres no centrinho da praia, com cafés, lojas de artesanato, bares e restaurantes; ideal para fim de tarde ou jantar.', NULL, 90, 60.0, 'medio', 0, 'noite', 'gastronomia;compras;vida_noturna', NULL, NULL, 1),
(45, 'Praia do Saco', 'Marechal Deodoro', NULL, 'praia', 'Mais tranquila e rústica, para quem quer fugir do movimento.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;natureza;tranquilidade', NULL, NULL, 1),
(46, 'Prainha da Barra Nova', 'Marechal Deodoro', 'Barra Nova', 'praia', 'Encontro da lagoa com o mar, ótima para banhos calmos.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;natureza;familia', NULL, NULL, 1),
(47, 'Praia da Barra de São Miguel', 'Barra de São Miguel', NULL, 'praia', 'Praia principal, protegida por recifes.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;familia', NULL, NULL, 1),
(48, 'Praia do Niquim', 'Barra de São Miguel', NULL, 'praia', 'Praia da Barra de São Miguel, perto da foz do Rio Niquim.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia;tranquilidade', NULL, NULL, 1),
(49, 'Praia das Conchas', 'Barra de São Miguel', NULL, 'praia', 'Praia tranquila na Barra de São Miguel.', NULL, 120, 0.0, 'gratuito', 0, 'manha', 'praia', NULL, NULL, 1),
(50, 'Mirante da Barra', 'Barra de São Miguel', NULL, 'natureza', 'Na rodovia AL-101; vista panorâmica da orla, da Lagoa do Roteiro e da Praia do Gunga.', NULL, 30, 15.0, 'pago', 0, 'tarde', 'fotografia;natureza', NULL, NULL, 1),
(51, 'Letreiro Amo São Miguel', 'Barra de São Miguel', NULL, 'natureza', 'Na orla da praia principal; parada para fotos com o mar ao fundo.', NULL, 15, 0.0, 'gratuito', 0, 'qualquer', 'fotografia', NULL, NULL, 1),
(52, 'Pracinha do Vivá', 'Barra de São Miguel', 'Barra Mar', 'natureza', 'Espaço charmoso e bastante fotografado.', NULL, 20, 0.0, 'gratuito', 0, 'fim_de_tarde', 'fotografia;cultura', NULL, NULL, 1),
(53, 'Vila Niquim', 'Barra de São Miguel', 'Centro', 'gastronomia', 'Complexo gastronômico com restaurantes e bares de culinária regional e variada.', NULL, 90, 60.0, 'medio', 0, 'noite', 'gastronomia;vida_noturna', NULL, NULL, 1),
(54, 'Povoado de Palateia', 'Barra de São Miguel', 'Palateia', 'natureza', 'Comunidade tradicional de maricultura (ostras e mariscos) e extração de própolis vermelha; passeios ecológicos e de barco.', NULL, 120, 50.0, 'pago', 0, 'manha', 'natureza;cultura;gastronomia', NULL, NULL, 1),
(55, 'Passeio de Lancha ou Barco (Rio Niquim)', 'Barra de São Miguel', NULL, 'natureza', 'Percorre a foz do Rio Niquim, bancos de areia, manguezais e recifes, até a Praia do Gunga ou a Lagoa do Roteiro.', NULL, 180, 80.0, 'pago', 1, 'manha', 'natureza;aventura;familia', NULL, NULL, 1),
(56, 'Passeio de Buggy ou Quadriciclo (Barra)', 'Barra de São Miguel', NULL, 'aventura', 'Explora as falésias coloridas e as trilhas da região.', NULL, 120, 120.0, 'pago', 0, 'manha', 'aventura;natureza', NULL, NULL, 1),
(57, 'Igreja de Nossa Senhora da Corrente', 'Penedo', 'Centro Histórico', 'historia', 'Construída em 1729; estilo barroco/rococó, talha dourada, azulejos portugueses e um porão que servia de esconderijo para pessoas escravizadas.', NULL, 40, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1),
(58, 'Convento e Igreja de Santa Maria dos Anjos', 'Penedo', 'Centro Histórico', 'historia', 'Complexo franciscano iniciado em 1660, com rico acervo de arte sacra no museu interno.', NULL, 60, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1),
(59, 'Theatro Sete de Setembro', 'Penedo', 'Centro Histórico', 'historia', 'Inaugurado em 1884; considerado o teatro mais antigo de Alagoas e ainda em funcionamento.', NULL, 30, 0.0, NULL, 0, 'tarde', 'cultura;historia', NULL, NULL, 1),
(60, 'Museu do Paço Imperial', 'Penedo', 'Centro Histórico', 'cultura', 'Antigo palácio de governo onde Dom Pedro II se hospedou em 1859; guarda peças históricas.', NULL, 60, 0.0, NULL, 0, 'manha', 'cultura;historia', NULL, NULL, 1),
(61, 'Catedral de Nossa Senhora do Rosário', 'Penedo', 'Centro Histórico', 'historia', 'Templo no coração da cidade, com herança arquitetônica dos séculos XVII a XIX.', NULL, 30, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1),
(62, 'Igreja de São Gonçalo Garcia', 'Penedo', 'Centro Histórico', 'historia', 'Templo histórico tombado pelo IPHAN, de grande valor artístico e cultural.', NULL, 30, 0.0, NULL, 0, 'manha', 'cultura;historia;religiao', NULL, NULL, 1),
(63, 'Mirante da Rocheira', 'Penedo', NULL, 'natureza', 'Na parte alta da cidade; vista do Rio São Francisco e do pôr do sol.', NULL, 40, 0.0, 'gratuito', 0, 'fim_de_tarde', 'fotografia;natureza', NULL, NULL, 1),
(64, 'Vila Dão Pêdu', 'Penedo', 'Centro Histórico', 'gastronomia', 'Espaço no Centro Histórico para curtir a noite, a gastronomia e a cultura local.', NULL, 90, 60.0, 'medio', 0, 'noite', 'gastronomia;vida_noturna;cultura', NULL, NULL, 1),
(65, 'Passeio de Barco pelo Rio São Francisco', 'Penedo', NULL, 'natureza', 'Saídas para ilhas, bancos de areia e mirantes ao longo do Velho Chico.', NULL, 180, 80.0, 'pago', 0, 'manha', 'natureza;familia', NULL, NULL, 1),
(66, 'Praia do Pontal do Peba', 'Piaçabuçu', 'Pontal do Peba', 'praia', 'Praia marítima nos arredores de Penedo (aprox. 40 km).', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;natureza', NULL, NULL, 1),
(67, 'Praia de Burgalhau', 'Maragogi', NULL, 'praia', 'Praia mais reservada, com boa infraestrutura; famosa pelo ponto de apoio de bugueiros.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;tranquilidade', NULL, NULL, 1),
(68, 'Praia de Ponta de Mangue', 'Maragogi', 'Ponta de Mangue', 'praia', 'Águas calmas que parecem uma piscina natural gigante; ideal para famílias.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;familia', NULL, NULL, 1),
(69, 'Praia de Antunes', 'Maragogi', 'Antunes', 'praia', 'Famosa pelo mar azul-turquesa, areia branquinha, coqueiros tortos e redes dentro da água.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;fotografia;natureza', NULL, NULL, 1),
(70, 'Caminho de Moisés (Barra Grande)', 'Maragogi', 'Barra Grande', 'natureza', 'Banco de areia que se abre no meio do mar na maré baixa, permitindo caminhar mar adentro.', NULL, 90, 0.0, 'gratuito', 1, 'manha', 'natureza;fotografia', NULL, NULL, 1),
(71, 'Praia do Xaréu', 'Maragogi', NULL, 'praia', 'Conhecida como ''Praia da Bruna''; águas calmas e mornas, cenários para fotos com balanços e redes.', NULL, 180, 0.0, 'gratuito', 0, 'manha', 'praia;fotografia', NULL, NULL, 1),
(72, 'Piscinas Naturais de Taocas e Barra Grande', 'Maragogi', NULL, 'natureza', 'Outras opções de recifes de corais para mergulho na maré baixa.', NULL, 180, 80.0, 'pago', 1, 'manha', 'natureza;aventura', NULL, NULL, 1),
(73, 'Passeio de Buggy (Maragogi)', 'Maragogi', NULL, 'aventura', 'Roteiro pelo litoral norte ou sul, passando por praias e mirantes.', NULL, 240, 120.0, 'pago', 0, 'manha', 'aventura;praia', NULL, NULL, 1),
(74, 'Mirante de Maragogi (Alto do Cruzeiro)', 'Maragogi', NULL, 'natureza', 'Vista panorâmica da cidade e do mar, com letreiros para fotos.', NULL, 30, 0.0, 'gratuito', 0, 'fim_de_tarde', 'fotografia', NULL, NULL, 1),
(75, 'Ruínas da Igreja de São Bento', 'Maragogi', 'São Bento', 'historia', 'Vestígios históricos com vista privilegiada da região.', NULL, 30, 0.0, 'gratuito', 0, 'tarde', 'cultura;historia', NULL, NULL, 1);

-- Eventos fictícios com datas relativas à data de importação (sempre no futuro)
INSERT INTO eventos (titulo, descricao, cidade, local, inicio, fim, categoria, preco, organizador_id) VALUES
('Luau na Praia do Francês', 'Música ao vivo e fogueira na areia. (Evento fictício para demonstração.)', 'Marechal Deodoro', 'Praia do Francês', TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '19:00:00'), TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '23:30:00'), 'festa', 40, 2),
('Feira de Artesanato Alagoano', 'Rendeiras, ceramistas e doceiras de todo o estado. (Evento fictício para demonstração.)', 'Maceió', 'Praça Multieventos, Pajuçara', TIMESTAMP(CURDATE() + INTERVAL 2 DAY, '16:00:00'), TIMESTAMP(CURDATE() + INTERVAL 2 DAY, '22:00:00'), 'feira', 0, 2),
('Noite de Coco de Roda e Guerreiro', 'Apresentação de folguedos alagoanos. (Evento fictício para demonstração.)', 'Maceió', 'Bairro de Jaraguá', TIMESTAMP(CURDATE() + INTERVAL 3 DAY, '19:00:00'), TIMESTAMP(CURDATE() + INTERVAL 3 DAY, '22:30:00'), 'cultura', 0, 2),
('Forró Pé de Serra no Centro Histórico', 'Trio de forró tradicional na praça. (Evento fictício para demonstração.)', 'Marechal Deodoro', 'Praça Pedro Paulino', TIMESTAMP(CURDATE() + INTERVAL 4 DAY, '20:00:00'), TIMESTAMP(CURDATE() + INTERVAL 4 DAY, '23:59:00'), 'musica', 30, 2),
('Festival do Sururu', 'Pratos com sururu, o marisco símbolo da Lagoa Mundaú. (Evento fictício para demonstração.)', 'Maceió', 'Orla da Pajuçara', TIMESTAMP(CURDATE() + INTERVAL 5 DAY, '12:00:00'), TIMESTAMP(CURDATE() + INTERVAL 5 DAY, '20:00:00'), 'gastronomia', 20, 2),
('Penedo à Noite: visita guiada', 'Caminhada noturna pelas igrejas iluminadas. (Evento fictício para demonstração.)', 'Penedo', 'Praça Barão de Penedo', TIMESTAMP(CURDATE() + INTERVAL 6 DAY, '18:30:00'), TIMESTAMP(CURDATE() + INTERVAL 6 DAY, '21:00:00'), 'cultura', 15, 2),
('Mostra de Cinema Alagoano', 'Curtas de realizadores locais. (Evento fictício para demonstração.)', 'Maceió', 'Centro, Maceió', TIMESTAMP(CURDATE() + INTERVAL 7 DAY, '18:00:00'), TIMESTAMP(CURDATE() + INTERVAL 7 DAY, '22:00:00'), 'cultura', 0, 2),
('Corrida da Orla 5K', 'Corrida de rua na orla de Ponta Verde. (Evento fictício para demonstração.)', 'Maceió', 'Orla de Ponta Verde', TIMESTAMP(CURDATE() + INTERVAL 9 DAY, '06:00:00'), TIMESTAMP(CURDATE() + INTERVAL 9 DAY, '09:00:00'), 'esporte', 60, 2);

INSERT INTO avaliacoes (usuario_id, ponto_id, nota, comentario) VALUES
(3, 1, 5, 'Água transparente, vimos muitos peixes. Vá na maré baixa!'),
(4, 1, 4, 'Lindo, mas fica cheio no fim de semana.'),
(3, 13, 5, 'Melhor praia que fui em Alagoas.'),
(4, 13, 4, 'Ótima para surf.'),
(3, 15, 5, 'O mirante é de tirar o fôlego.'),
(4, 20, 5, 'Vale cada centavo.'),
(3, 4, 4, 'Acervo bonito sobre o folclore alagoano.'),
(4, 21, 5, 'Penedo é encantadora.'),
(3, 7, 4, 'Passeio longo, mas muito bonito.'),
(4, 19, 5, 'Ver o peixe-boi de perto foi emocionante.'),
(3, 10, 4, 'Sururu ao molho de coco imperdível.'),
(4, 24, 5, 'Paisagem incrível.');
