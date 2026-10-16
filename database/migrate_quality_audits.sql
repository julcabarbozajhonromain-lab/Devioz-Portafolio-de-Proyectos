-- Additive migration. Keeps the existing evaluations and project data intact.
CREATE TABLE IF NOT EXISTS `quality_audits` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `delivery_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `evaluator_id` BIGINT UNSIGNED NOT NULL,
    `standard_version` VARCHAR(40) NOT NULL DEFAULT 'ISO/IEC 25010:2023',
    `release_version` VARCHAR(100) NULL DEFAULT NULL,
    `commit_sha` VARCHAR(64) NULL DEFAULT NULL,
    `status` ENUM('in_review', 'needs_changes', 'passed', 'blocked') NOT NULL DEFAULT 'in_review',
    `gate_status` ENUM('not_run', 'pending', 'passed', 'failed', 'blocked') NOT NULL DEFAULT 'pending',
    `score` DECIMAL(5,2) NULL DEFAULT NULL,
    `criteria` JSON NOT NULL,
    `feedback` TEXT NULL DEFAULT NULL,
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_quality_audit_project` (`project_id`, `created_at`),
    INDEX `idx_quality_audit_delivery` (`delivery_id`),
    INDEX `idx_quality_audit_gate` (`gate_status`, `status`),
    CONSTRAINT `fk_quality_audit_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quality_audit_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_quality_audit_evaluator` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quality_audit_findings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `audit_id` BIGINT UNSIGNED NOT NULL,
    `severity` ENUM('critical', 'high', 'medium', 'low', 'info') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `evidence` TEXT NULL DEFAULT NULL,
    `status` ENUM('open', 'in_progress', 'resolved', 'accepted') NOT NULL DEFAULT 'open',
    `resolution` TEXT NULL DEFAULT NULL,
    `assigned_to` BIGINT UNSIGNED NULL DEFAULT NULL,
    `due_date` DATE NULL DEFAULT NULL,
    `resolved_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_quality_finding_audit` (`audit_id`, `severity`, `status`),
    CONSTRAINT `fk_quality_finding_audit` FOREIGN KEY (`audit_id`) REFERENCES `quality_audits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quality_finding_assignee` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quality_tool_runs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `audit_id` BIGINT UNSIGNED NOT NULL,
    `tool_key` VARCHAR(80) NOT NULL,
    `tool_version` VARCHAR(80) NULL DEFAULT NULL,
    `source` ENUM('manual', 'integration') NOT NULL DEFAULT 'manual',
    `status` ENUM('not_run', 'passed', 'failed', 'error') NOT NULL DEFAULT 'not_run',
    `commit_sha` VARCHAR(64) NULL DEFAULT NULL,
    `metrics` JSON NULL DEFAULT NULL,
    `evidence_url` VARCHAR(500) NULL DEFAULT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `executed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `executed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_quality_tool_run_audit` (`audit_id`, `tool_key`, `created_at`),
    CONSTRAINT `fk_quality_tool_run_audit` FOREIGN KEY (`audit_id`) REFERENCES `quality_audits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quality_tool_run_executor` FOREIGN KEY (`executed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
