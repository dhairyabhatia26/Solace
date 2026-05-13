# Software Requirements Specification (SRS) - Solace

## Introduction
This document specifies the software requirements for the Solace Student Wellness Intelligence & Support Management Platform.

## Purpose
To define the technical implementation guidelines, system behavior, and constraints for developing Solace using PHP and MySQL.

## Scope
The system handles user authentication, case submission, workflow management for counselors, analytical dashboards for admins, and Gemini AI API integration for case summarization and insights.

## System Overview
A typical 3-tier architecture:
- **Presentation Layer:** HTML, CSS, Bootstrap, JavaScript.
- **Application Layer:** PHP 8+ handling business logic, session management, and API calls.
- **Data Layer:** MySQL 8+ database for persistent storage.

## Functional Requirements
Refer to the FRD for detailed module capabilities. Core focus is on CRUD operations for wellness cases, role-based redirection, and API communication.

## Non-Functional Requirements
- **Performance:** Page load times should be under 2 seconds.
- **Usability:** The UI must be responsive, clean, and resemble a professional SaaS dashboard.
- **Reliability:** System should gracefully handle database connection errors.

## Security Requirements
- All passwords must be hashed using `password_hash()`.
- All database queries must use PDO Prepared Statements to prevent SQL Injection.
- Cross-Site Scripting (XSS) must be prevented by sanitizing outputs using `htmlspecialchars()`.
- Sessions must be strictly managed; access control checks on every protected page.

## Database Requirements
- Relational database (MySQL).
- Use InnoDB engine to support foreign key constraints and cascading deletes.
- Required tables: `users`, `wellness_cases`, `case_notes`, `resources`, `feedback`, `activity_logs`, `settings`.

## UI Requirements
- Bootstrap 5+ (or custom equivalent).
- Consistent typography (Inter font).
- Clear state indicators (badges, empty states).
- Dark/Light mode support.

## AI Integration Requirements
- Integration with Google Gemini API via cURL in PHP.
- Prompts must clearly enforce non-clinical output constraints.
- API keys must be externalized in `config/gemini_config.php`.

## Error Handling
- Never expose raw SQL errors or PHP stack traces to the end user.
- Log errors internally and display generic friendly error messages in the UI.

## Data Privacy Considerations
- Limit PII (Personally Identifiable Information) exposure in the Admin dashboard.
- AI should ideally process anonymized text where possible.

## Future Enhancements
- Email notifications for case updates.
- Real-time chat integration.
- Advanced export capabilities to PDF.
