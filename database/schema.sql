-- ==============================================================================
-- DEVIOZ 24 — Plataforma de Gestión de Proyectos de Software
-- Database Schema v1.0.0 (MySQL 8.0+ / MariaDB 10.4+)
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-05:00"; -- America/Lima

CREATE DATABASE IF NOT EXISTS `devioz_24` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `devioz_24`;

-- ------------------------------------------------------------------------------
-- 1. Tabla: users
-- Almacena administradores (docentes/tutores) y desarrolladores (practicantes)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'developer') NOT NULL DEFAULT 'developer',
    `active` BOOLEAN NOT NULL DEFAULT TRUE,
    `avatar` VARCHAR(255) NULL DEFAULT NULL,
    `sede` ENUM('Cercado', 'Lima Norte', 'Villa') NULL DEFAULT NULL,
    `headline` VARCHAR(255) NULL DEFAULT NULL,
    `bio` TEXT NULL DEFAULT NULL,
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `last_login_ip` VARCHAR(45) NULL DEFAULT NULL,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `users_email_unique` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_sede` (`sede`),
    INDEX `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. Tabla: projects
-- Proyectos de práctica profesional asignados (1 practicante = 1 proyecto activo)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `client_name` VARCHAR(150) NULL DEFAULT 'Cliente Corporativo',
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `documentation` LONGTEXT NULL DEFAULT NULL,
    `status` ENUM(
        'assigned', 'draft', 'in_progress', 'pending_review',
        'in_review', 'approved', 'rejected', 'completed', 'archived'
    ) NOT NULL DEFAULT 'assigned',
    `sede` ENUM('Cercado', 'Lima Norte', 'Villa') NULL DEFAULT NULL,
    `tech_stack` JSON NULL DEFAULT NULL,
    `repository_url` VARCHAR(255) NULL DEFAULT NULL,
    `live_url` VARCHAR(255) NULL DEFAULT NULL,
    `video_url` VARCHAR(255) NULL DEFAULT NULL,
    `cover_image` VARCHAR(255) NULL DEFAULT NULL,
    `gallery_images` JSON NULL DEFAULT NULL,
    `developer_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `assigned_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `reviewed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `approved_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `assigned_at` TIMESTAMP NULL DEFAULT NULL,
    `accepted_at` TIMESTAMP NULL DEFAULT NULL,
    `submitted_at` TIMESTAMP NULL DEFAULT NULL,
    `approved_at` TIMESTAMP NULL DEFAULT NULL,
    `rejected_at` TIMESTAMP NULL DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `archived_at` TIMESTAMP NULL DEFAULT NULL,
    `rejection_reason` TEXT NULL DEFAULT NULL,
    `final_score` DECIMAL(5,1) NULL DEFAULT NULL,
    `is_public_in_gallery` BOOLEAN NOT NULL DEFAULT FALSE,
    `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `projects_code_unique` (`code`),
    UNIQUE KEY `projects_slug_unique` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_developer` (`developer_id`),
    INDEX `idx_sede` (`sede`),
    INDEX `idx_public` (`is_public_in_gallery`),
    CONSTRAINT `fk_projects_developer` FOREIGN KEY (`developer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_projects_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_projects_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_projects_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. Tabla: milestones
-- Hitos secuenciales de un proyecto (1 a 12 hitos por proyecto)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `milestones`;
CREATE TABLE `milestones` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `due_date` DATE NOT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `status` ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    `order_position` INT NOT NULL DEFAULT 0,
    `weight` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_project_status` (`project_id`, `status`),
    INDEX `idx_order` (`project_id`, `order_position`),
    CONSTRAINT `fk_milestones_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. Tabla: deliveries
-- Entregas o evidencias enviadas por los practicantes para cada hito
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `deliveries`;
CREATE TABLE `deliveries` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `milestone_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `type` ENUM('partial', 'final') NOT NULL DEFAULT 'partial',
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `documentation` TEXT NULL DEFAULT NULL,
    `video_url` VARCHAR(255) NULL DEFAULT NULL,
    `files` JSON NULL DEFAULT NULL,
    `submitted_at` TIMESTAMP NULL DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `score` DECIMAL(3,1) NULL DEFAULT NULL,
    `feedback` TEXT NULL DEFAULT NULL,
    `reviewed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_project_status` (`project_id`, `status`),
    INDEX `idx_milestone` (`milestone_id`),
    CONSTRAINT `fk_deliveries_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_deliveries_milestone` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_deliveries_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. Tabla: evaluations
-- Calificaciones académicas formales (0.0 a 10.0 o 20.0 con rúbrica JSON)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE `evaluations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `delivery_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `evaluator_id` BIGINT UNSIGNED NOT NULL,
    `score` DECIMAL(5,1) NOT NULL,
    `criteria` JSON NULL DEFAULT NULL,
    `feedback` TEXT NULL DEFAULT NULL,
    `week_number` INT NULL DEFAULT NULL,
    `evaluated_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_project_week` (`project_id`, `week_number`),
    CONSTRAINT `fk_evaluations_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_evaluations_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_evaluations_evaluator` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. Auditorías de calidad con evidencia verificable (ISO/IEC 25010:2023)
-- Los dictámenes son evaluaciones internas de Devioz, no certificados ISO.
-- ------------------------------------------------------------------------------
CREATE TABLE `quality_audits` (
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

CREATE TABLE `quality_audit_findings` (
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

CREATE TABLE `quality_tool_runs` (
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

-- ------------------------------------------------------------------------------
-- 7. Tabla: project_files
-- Metadatos de archivos adjuntos y documentación técnica almacenados
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `project_files`;
CREATE TABLE `project_files` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `uploaded_by` BIGINT UNSIGNED NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `disk` VARCHAR(50) NOT NULL DEFAULT 'local',
    `path` VARCHAR(500) NOT NULL,
    `mime_type` VARCHAR(100) NULL DEFAULT NULL,
    `size` BIGINT UNSIGNED NOT NULL,
    `hash` VARCHAR(64) NULL DEFAULT NULL,
    `category` ENUM('docs', 'code', 'designs', 'credentials', 'other') NOT NULL DEFAULT 'other',
    `is_encrypted` BOOLEAN NOT NULL DEFAULT FALSE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_project` (`project_id`),
    INDEX `idx_category` (`category`),
    CONSTRAINT `fk_files_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_files_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 7. Tabla: check_ins
-- Reportes diarios de avance (Morning / Midday / Afternoon standup)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `check_ins`;
CREATE TABLE `check_ins` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `window` ENUM('morning', 'midday', 'afternoon') NOT NULL,
    `content` TEXT NOT NULL,
    `hours_worked` DECIMAL(4,1) NULL DEFAULT NULL,
    `blockers` TEXT NULL DEFAULT NULL,
    `sprint` VARCHAR(50) NULL DEFAULT NULL,
    `story_points` DECIMAL(4,1) NULL DEFAULT NULL,
    `patterns_applied` JSON NULL DEFAULT NULL,
    `metrics` JSON NULL DEFAULT NULL,
    `next_steps` TEXT NULL DEFAULT NULL,
    `check_in_date` DATE NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_window_date` (`user_id`, `window`, `check_in_date`),
    INDEX `idx_project` (`project_id`),
    INDEX `idx_date` (`check_in_date`),
    CONSTRAINT `fk_check_ins_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_check_ins_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 8. Tabla: comments
-- Hilo de comentarios y retroalimentación directa en el proyecto
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_project` (`project_id`),
    CONSTRAINT `fk_comments_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 9. Tabla: messages
-- Mensajería interna y notificaciones entre admin y developers
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sender_id` BIGINT UNSIGNED NOT NULL,
    `receiver_id` BIGINT UNSIGNED NOT NULL,
    `project_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `subject` VARCHAR(255) NULL DEFAULT NULL,
    `body` TEXT NOT NULL,
    `type` ENUM('general', 'question', 'feedback', 'alert') NOT NULL DEFAULT 'general',
    `priority` ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_receiver_read` (`receiver_id`, `read_at`),
    CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 10. Tabla: audit_logs
-- Trazabilidad y auditoría de eventos de seguridad y cambios de estado
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `model_type` VARCHAR(100) NULL DEFAULT NULL,
    `model_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `changes` JSON NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_model` (`model_type`, `model_id`),
    INDEX `idx_action` (`action`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 11. ValoraciÃ³n de respuestas del asistente LABVA
-- Solo guarda la valoraciÃ³n y metadatos; no persiste el contenido de la conversaciÃ³n.
-- ------------------------------------------------------------------------------
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

SET FOREIGN_KEY_CHECKS = 1;
