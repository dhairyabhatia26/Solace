# Solace Project Cleanup Report

This report outlines the files and folders identified for cleanup to maintain a professional, recruiter-ready codebase.

## Cleanup Strategy
- **Core Files**: Kept in place.
- **Diagnostic/Test Files**: Moved to `_archive_cleanup/`.
- **Legacy Template Files**: Moved to `_archive_cleanup/legacy_root/`.
- **Old SQL Migrations**: Moved to `_archive_cleanup/database/`.
- **Wrong Path References**: Updated in active code.

---

| File / Folder Path | Classification | Recommendation | Reason | Risk Level |
| :--- | :--- | :--- | :--- | :--- |
| `test_gemini.php` | Diagnostic | Archive | Temporary test file for API validation. | Low |
| `test_gemini_models.php` | Diagnostic | Archive | Temporary test file for model listing. | Low |
| `scan_resource_files.php` | Diagnostic | Archive | Admin utility used during initial setup. | Low |
| `header.php` (root) | Legacy | Archive | Duplicate of `includes/header.php`. | Low |
| `footer.php` (root) | Legacy | Archive | Duplicate of `includes/footer.php`. | Low |
| `about.php` | Legacy | Archive | Old template page not integrated into main UI. | Low |
| `help.php` | Legacy | Archive | Old template page not integrated into main UI. | Low |
| `cart.php` | Legacy | Archive | Unused e-commerce template file. | Low |
| `checkout.php` | Legacy | Archive | Unused e-commerce template file. | Low |
| `wishlist.php` | Legacy | Archive | Unused e-commerce template file. | Low |
| `ebooks.php` | Legacy | Archive | Unused e-commerce template file. | Low |
| `downloads.php` | Legacy | Archive | Unused legacy download page. | Low |
| `downloads/` (folder) | Legacy | Archive | Contains PDFs used by the legacy download page. | Low |
| `database/*.sql` (except solace.sql) | SQL Migrations | Archive | Migrations already applied to the database. | Low |
| `assets/resources_pdfs/` | Legacy | Delete | Obsolete folder path (if exists). | Low |

---

## Active Code Updates
- All references to `assets/resources_pdfs/` will be updated to `assets/resources/pdfs/`.
- Navigation links to archived files will be removed.

## Database Cleanup
A safe SQL script `database/final_resource_cleanup.sql` will be provided for manual execution.
