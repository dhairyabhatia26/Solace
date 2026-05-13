# Solace - Student Wellness Intelligence Platform 🌿

Solace is a PHP/MySQL-based institutional wellness platform designed to centralize and secure student support workflows. It bridges the gap between informal communication and professional intervention by providing role-based dashboards, AI-assisted analysis, and leadership analytics.

## 🚀 Key Features

- **Role-Based Access**: Specialized interfaces for Students, Counselors, and Institutional Administrators.
- **Secure Case Submission**: Structured forms for students to report concerns with optional self-assessment metrics (stress, sleep, academic pressure).
- **Counselor Workflow**: Case prioritization, internal/external notes, and resource recommendation system.
- **AI-Powered Insights**: 
  *   **Case Summarization**: Condenses student concerns for quick counselor review.
  *   **Risk Pattern Identification**: AI-driven severity classification (Low to Critical).
  *   **Leadership Analytics**: Aggregated macro-level trend reports for HODs/Principals.
- **Resource Library**: A curated collection of 40+ wellness PDFs and guides.
- **Data Analytics**: Real-time visualization of wellness trends using Chart.js.

## 🛠️ Tech Stack

- **Backend**: PHP 8.0+ (Vanilla, PDO Prepared Statements)
- **Database**: MySQL 8.0+
- **Frontend**: Bootstrap 5, Custom Vanilla CSS, JavaScript
- **Visualization**: Chart.js
- **AI Integration**: Google Gemini API (1.5/2.0 Flash models)

## 📁 Project Structure

The project uses a standalone root-level file architecture for simplicity and performance:

- `/` (Root): Main application pages (dashboard, resources, login, etc.)
- `/app`: Core middleware, helpers, and layout components.
- `/assets`: CSS, JavaScript, and physical PDF resource library.
- `/config`: Configuration examples (local credentials ignored by Git).
- `/database`: SQL schema, migrations, and seed data.
- `/docs`: Project requirements, SRS, and architecture documentation.
- `/_archive_cleanup`: Legacy files and debug utilities (archived).

## 💻 Local Setup

### 1. Requirements
- XAMPP / WAMP / MAMP (PHP 8.0+ & MySQL)
- Google Gemini API Key ([AI Studio](https://aistudio.google.com/))

### 2. Installation
1.  Clone the repository into your `htdocs` directory:
    ```bash
    git clone https://github.com/yourusername/Solace.git
    ```
2.  **Database Setup**:
    *   Create a database named `solace`.
    *   Import `database/schema/solace_schema.sql`.
    *   (Optional) Import `database/seeds/resources_seed.sql` if available.
3.  **Configuration**:
    *   Copy `config/db.example.php` to `config/db.php` and add your local DB credentials.
    *   Copy `config/gemini_config.example.php` to `config/gemini_config.php` and add your Gemini API key.
4.  **Run**:
    *   Access via `http://localhost/Solace/`.

## 👤 Demo Credentials
All demo accounts use the password: `password123`
- **Admin**: `admin@solace.com`
- **Counselor**: `counselor@solace.com`
- **Student**: `student@solace.com`

## 🔒 Security Note
This project is for educational and demo purposes. Local configuration files containing secrets (`db.php`, `gemini_config.php`) are ignored by Git. Never commit real API keys or production database credentials.

## 📄 License
This project is for academic/demonstration use. See the institution's policy on software deployment.
