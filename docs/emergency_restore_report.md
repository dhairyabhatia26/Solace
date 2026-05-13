# Emergency Restore Diagnosis Report

## 1. Broken Pages & Raw PHP Printing
The following pages are currently broken or printing raw PHP code due to missing opening `<?php` tags or improper refactoring:
- **`manage_counselors.php` / `app/pages/admin/counselors.php`**: Missing `<?php` on line 1 of the included page.
- **`manage_resources.php` / `app/pages/admin/manage_resources.php`**: Missing `<?php` on line 1 of the included page.
- **Root stubs**: All root stubs created in the previous phase (e.g., `dashboard_admin.php`) are purely delegating to `route.php` or `app/pages/...`, which the user wants to revert to direct execution.

## 2. Malformed PHP Tags & Raw Code Issues
- **`app/pages/admin/counselors.php`**: Missing `<?php` tag at the start.
- **`app/pages/admin/manage_resources.php`**: Missing `<?php` tag at the start.
- **Root Pages**: Several root pages currently only contain stubs and are not functioning as standalone pages.

## 3. Undefined Variables
- **$counselors**: Undefined in `manage_counselors.php` because the logic in the included file failed to execute due to missing tags.
- **$resources**: Undefined in `manage_resources.php` for the same reason.
- **$pdo**: Potentially undefined if `bootstrap.php` or `db.php` is not loaded correctly at the start of the page.

## 4. Dependencies on `route.php`/`action.php`
- The entire navigation system in `app/layout/header.php` currently uses `route.php?page=...`.
- All form actions currently point to `action.php?handler=...`.
- This architecture is being abandoned for primary page loading as per user request.

## 5. Availability of Legacy Versions
- **`app/pages/`**: Contains the logic that was recently moved from the root. This will be the primary source for restoring the root pages.
- **`app/actions/`**: Contains the logic for form handlers.

## 6. Recommended Restoration Approach
1. **Restore `includes/` folder**: Create a central `includes/` directory for `db.php`, `auth.php`, etc., to provide stable paths for root pages.
2. **Convert Stubs to Standalone Pages**: Move the logic from `app/pages/...` back into the corresponding root PHP files.
3. **Fix Tag Issues**: Ensure all files start with `<?php` and terminate properly.
4. **Update Layout**: Modify the header to use direct root URLs (e.g., `dashboard_admin.php` instead of `route.php?page=admin/dashboard`).
5. **Session Safety**: Update `auth.php` and pages to use `session_status()` checks before `session_start()`.
6. **Initialize Variables**: Ensure all variables used in loops (like `$counselors`) are initialized as empty arrays.
