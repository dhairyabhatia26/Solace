# Solace Resource Library PDFs

This folder contains the institutional PDF resources for the Solace Wellness Platform.

## Correct Path
All PDFs MUST be stored in this directory:
`assets/resources/pdfs/`

## Subfolders (Categories)
- `stress-management/`
- `anxiety-support/`
- `emotional-wellbeing/`
- `mental-health-awareness/`
- `self-care/`
- `sleep-rest/`
- `peer-support/`
- `crisis-safety/`
- `counselor-guides/`

## Usage Instructions
1. Place your PDF files manually inside the appropriate category folder above.
2. Go to the Admin panel -> **Manage Resources**.
3. When adding a resource, the **File Path** should look like this:
   `assets/resources/pdfs/category/filename.pdf`
4. The system will automatically check if the file exists on the server before showing it to students.

## Note on Troubleshooting
If you see "PDF Not Found" in the library:
- Ensure the file is actually in the folder.
- Ensure the `file_path` in the database matches EXACTLY (including case and slashes).
- The folder `assets/resources_pdfs/` is OBSOLETE and should not be used.
