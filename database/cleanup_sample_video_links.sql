-- Remove the unrelated demo links attached to the DEV-2026 sample projects.
-- Project teams can add the real, project-specific video from the project form.
START TRANSACTION;

UPDATE `projects`
SET `video_url` = NULL
WHERE `code` REGEXP '^DEV-2026-0(0[1-9]|1[0-9]|2[0-6])$'
  AND `video_url` IS NOT NULL;

UPDATE `deliveries` AS d
INNER JOIN `projects` AS p ON p.`id` = d.`project_id`
SET d.`video_url` = NULL
WHERE p.`code` REGEXP '^DEV-2026-0(0[1-9]|1[0-9]|2[0-6])$'
  AND d.`video_url` IS NOT NULL;

COMMIT;
