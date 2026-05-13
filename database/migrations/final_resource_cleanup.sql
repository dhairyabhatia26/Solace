-- database/final_resource_cleanup.sql
-- Solace Resource Library Final Cleanup
-- Run this script in phpMyAdmin to ensure the resource library is clean and accurate.

-- 1. DEACTIVATE PLACEHOLDER RESOURCES
-- Hides records that don't have a real file assigned.
UPDATE resources
SET is_active = 0
WHERE file_path = '#'
   OR file_path IS NULL
   OR file_path = '';

-- 2. FIX OLD WRONG PATHS
-- Ensures all records point to the correct assets/resources/pdfs/ folder.
UPDATE resources
SET file_path = REPLACE(file_path, 'assets/resources_pdfs/', 'assets/resources/pdfs/')
WHERE file_path LIKE 'assets/resources_pdfs/%';

-- 3. CHECK FOR DUPLICATE FILE PATHS
-- Identifies if any PDF is listed multiple times.
SELECT file_path, COUNT(*) AS duplicate_count
FROM resources
WHERE file_path IS NOT NULL
  AND file_path <> '#'
  AND is_active = 1
GROUP BY file_path
HAVING COUNT(*) > 1;

-- 4. FINAL VERIFICATION: SHOW ACTIVE RESOURCES
-- Displays the current professional library content.
SELECT id, title, category, file_path, is_active
FROM resources
WHERE is_active = 1
ORDER BY category ASC, title ASC;
