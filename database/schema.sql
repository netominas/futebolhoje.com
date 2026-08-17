-- Schema do banco de dados do futebolhoje.com
-- Execute este arquivo no banco criado para o site (via phpMyAdmin do ServerAvatar ou linha de comando)

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS ligas (
    id INT UNSIGNED NOT NULL,
    nome VARCHAR(160) NOT NULL,
    tipo ENUM('League', 'Cup') NOT NULL DEFAULT 'League',
    pais VARCHAR(100) NOT NULL,
    pais_codigo VARCHAR(10) DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    bandeira_pais VARCHAR(255) DEFAULT NULL,
    slug VARCHAR(200) NOT NULL,
    temporada_atual SMALLINT UNSIGNED DEFAULT NULL,
    -- Controlados pelo painel admin: liga aparece na sidebar/footer/topo da home
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    ordem_destaque SMALLINT UNSIGNED DEFAULT NULL,
    -- Controlado pelo painel admin: jogos dessa liga ganham conteudo escrito por IA (ver jogo_conteudo)
    conteudo_ia TINYINT(1) NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_ligas_slug (slug),
    KEY idx_ligas_destaque (destaque, ordem_destaque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS liga_temporadas (
    liga_id INT UNSIGNED NOT NULL,
    temporada SMALLINT UNSIGNED NOT NULL,
    data_inicio DATE DEFAULT NULL,
    data_fim DATE DEFAULT NULL,
    atual TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (liga_id, temporada),
    CONSTRAINT fk_temporadas_liga FOREIGN KEY (liga_id) REFERENCES ligas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS times (
    id INT UNSIGNED NOT NULL,
    nome VARCHAR(160) NOT NULL,
    nome_curto VARCHAR(20) DEFAULT NULL,
    pais VARCHAR(100) DEFAULT NULL,
    fundado SMALLINT UNSIGNED DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    slug VARCHAR(200) NOT NULL,
    venue_id INT UNSIGNED DEFAULT NULL,
    -- Controlados pelo painel admin: time aparece na sidebar
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    ordem_destaque SMALLINT UNSIGNED DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_times_slug (slug),
    KEY idx_times_destaque (destaque, ordem_destaque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS estadios (
    id INT UNSIGNED NOT NULL,
    nome VARCHAR(160) DEFAULT NULL,
    cidade VARCHAR(120) DEFAULT NULL,
    capacidade INT UNSIGNED DEFAULT NULL,
    imagem VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jogos (
    id INT UNSIGNED NOT NULL,
    liga_id INT UNSIGNED NOT NULL,
    temporada SMALLINT UNSIGNED NOT NULL,
    rodada VARCHAR(80) DEFAULT NULL,
    data_utc DATETIME NOT NULL,
    status_curto VARCHAR(4) NOT NULL DEFAULT 'NS',
    status_longo VARCHAR(60) DEFAULT NULL,
    minuto SMALLINT UNSIGNED DEFAULT NULL,
    mandante_id INT UNSIGNED NOT NULL,
    visitante_id INT UNSIGNED NOT NULL,
    gols_mandante TINYINT UNSIGNED DEFAULT NULL,
    gols_visitante TINYINT UNSIGNED DEFAULT NULL,
    gols_mandante_intervalo TINYINT UNSIGNED DEFAULT NULL,
    gols_visitante_intervalo TINYINT UNSIGNED DEFAULT NULL,
    estadio_id INT UNSIGNED DEFAULT NULL,
    arbitro VARCHAR(120) DEFAULT NULL,
    -- Quando eventos/estatisticas detalhados foram buscados pela ultima vez (sob demanda, ver JogoController)
    eventos_atualizados_em DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_jogos_data (data_utc),
    KEY idx_jogos_status (status_curto),
    KEY idx_jogos_liga_temporada (liga_id, temporada),
    KEY idx_jogos_mandante (mandante_id),
    KEY idx_jogos_visitante (visitante_id),
    CONSTRAINT fk_jogos_liga FOREIGN KEY (liga_id) REFERENCES ligas (id) ON DELETE CASCADE,
    CONSTRAINT fk_jogos_mandante FOREIGN KEY (mandante_id) REFERENCES times (id) ON DELETE CASCADE,
    CONSTRAINT fk_jogos_visitante FOREIGN KEY (visitante_id) REFERENCES times (id) ON DELETE CASCADE,
    CONSTRAINT fk_jogos_estadio FOREIGN KEY (estadio_id) REFERENCES estadios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jogo_eventos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jogo_id INT UNSIGNED NOT NULL,
    minuto SMALLINT UNSIGNED DEFAULT NULL,
    minuto_extra SMALLINT UNSIGNED DEFAULT NULL,
    time_id INT UNSIGNED DEFAULT NULL,
    jogador VARCHAR(160) DEFAULT NULL,
    jogador_assistencia VARCHAR(160) DEFAULT NULL,
    tipo VARCHAR(30) NOT NULL,
    detalhe VARCHAR(80) DEFAULT NULL,
    comentario VARCHAR(160) DEFAULT NULL,
    ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    KEY idx_eventos_jogo (jogo_id, ordem),
    CONSTRAINT fk_eventos_jogo FOREIGN KEY (jogo_id) REFERENCES jogos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jogo_estatisticas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jogo_id INT UNSIGNED NOT NULL,
    time_id INT UNSIGNED NOT NULL,
    tipo VARCHAR(60) NOT NULL,
    valor VARCHAR(20) DEFAULT NULL,
    UNIQUE KEY uk_estatisticas (jogo_id, time_id, tipo),
    CONSTRAINT fk_estatisticas_jogo FOREIGN KEY (jogo_id) REFERENCES jogos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS classificacao (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    liga_id INT UNSIGNED NOT NULL,
    temporada SMALLINT UNSIGNED NOT NULL,
    grupo VARCHAR(60) NOT NULL DEFAULT '',
    time_id INT UNSIGNED NOT NULL,
    posicao TINYINT UNSIGNED NOT NULL,
    pontos SMALLINT NOT NULL DEFAULT 0,
    jogos TINYINT UNSIGNED NOT NULL DEFAULT 0,
    vitorias TINYINT UNSIGNED NOT NULL DEFAULT 0,
    empates TINYINT UNSIGNED NOT NULL DEFAULT 0,
    derrotas TINYINT UNSIGNED NOT NULL DEFAULT 0,
    gols_pro SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    gols_contra SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    saldo_gols SMALLINT NOT NULL DEFAULT 0,
    forma VARCHAR(20) DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_classificacao (liga_id, temporada, grupo, time_id),
    CONSTRAINT fk_classificacao_time FOREIGN KEY (time_id) REFERENCES times (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Conteudo escrito (padrao por template, ou IA para ligas marcadas) de cada jogo.
-- Gerado sob demanda/pelo worker e cacheado aqui para nao reprocessar a cada visita.
CREATE TABLE IF NOT EXISTS jogo_conteudo (
    jogo_id INT UNSIGNED NOT NULL,
    tipo ENUM('template', 'ia') NOT NULL DEFAULT 'template',
    conteudo_html MEDIUMTEXT NOT NULL,
    gerado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (jogo_id),
    CONSTRAINT fk_jogo_conteudo_jogo FOREIGN KEY (jogo_id) REFERENCES jogos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_admin_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Registro simples de execução dos workers de sincronização, útil para depuração/monitoramento
CREATE TABLE IF NOT EXISTS sync_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker VARCHAR(60) NOT NULL,
    status ENUM('ok', 'erro') NOT NULL,
    mensagem VARCHAR(500) DEFAULT NULL,
    duracao_ms INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_sync_log_worker (worker, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
