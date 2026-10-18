-- Feedback útil/no útil de LABVA. No almacena mensajes ni respuestas.
CREATE TABLE IF NOT EXISTS `labva_feedback` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `response_id` CHAR(32) NOT NULL,
    `rating` ENUM('helpful', 'not_helpful') NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_labva_feedback_user_response` (`user_id`, `response_id`),
    INDEX `idx_labva_feedback_rating_created` (`rating`, `created_at`),
    CONSTRAINT `fk_labva_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
