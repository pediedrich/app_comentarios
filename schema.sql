CREATE DATABASE IF NOT EXISTS app_comentarios
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE app_comentarios;

CREATE TABLE IF NOT EXISTS comments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  text VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  parent_id BIGINT UNSIGNED NULL,
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  deleted_at DATETIME(6) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_comments_parent
    FOREIGN KEY (parent_id) REFERENCES comments(id)
    ON DELETE RESTRICT,
  INDEX idx_comments_listing (deleted_at, created_at, id),
  INDEX idx_comments_parent (parent_id)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
