# Solace Database Setup

Follow these steps to set up the database for the Solace platform:

1. **Create Database**: Create a new MySQL database named `solace`.
2. **Import Schema**: Import the `database/schema/solace_schema.sql` file first. This will create all necessary tables.
3. **Import Seeds**: If available, import any files in `database/seeds/` to populate the database with demo data.
4. **Migrations**: Run any SQL files in `database/migrations/` only if you need to update an existing database structure.
5. **PDF Resources**: Note that physical resource files (PDFs) are stored in `assets/resources/pdfs/`. The database records in the `resources` table link to these files.

## Local Configuration
After importing the database, ensure you copy `config/db.example.php` to `config/db.php` and update your local credentials.
