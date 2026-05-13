# Functional Requirements Document (FRD) - Solace

## Functional Modules
1. Authentication & Authorization
2. Student Portal
3. Counselor Workflow
4. Administrative Analytics
5. AI Integration Module
6. Settings & Preferences

## User Roles and Permissions
- **Student:** Can create cases, view own cases, submit feedback.
- **Counselor:** Can view assigned cases, add internal/external notes, update status, view resources.
- **Admin:** Can view all aggregated data, manage users, generate insights.

## Student Features
- **Register/Login:** Secure access.
- **Submit Concern:** Form capturing title, category, description, urgency, and optional self-rating scores.
- **View Cases:** Table showing status of current and past submissions.
- **Feedback:** Ability to rate support received after a case is closed.

## Counselor Features
- **Case Queue:** View assigned cases.
- **Case Details:** View student submissions.
- **Notes:** Add internal notes (hidden from student) and student-visible remarks.
- **Status Update:** Change case status (in progress, resolved, etc.).
- **Escalation:** Flag high-risk cases for admin review.

## Admin/HOD/Principal Features
- **Dashboard:** High-level metrics (Total cases, open cases, critical flags).
- **Charts:** Visual representation of case categories and monthly trends.
- **Counselor Management:** View workload per counselor.

## AI Features
- **Case Summary:** Gemini API summarizes student descriptions for counselors.
- **Guidance Generation:** Gemini API suggests non-clinical guidance points based on input data.
- **Admin Insights:** Gemini API generates monthly institutional insights from aggregated data.

## Dashboard Features
- Real-time updates based on database state.
- Integration of Chart.js for visualization (Admin/Counselor).

## Export/Reporting Features
- Export cases to CSV (Future phase).
- Print dashboard view (Future phase).

## Settings/Theme Features
- Toggle between Light and Dark mode.
- Store preference persistently in the database.

## Validation Rules
- All required form fields must be filled.
- Passwords must be hashed using bcrypt.
- Direct URL access to unauthorized roles must redirect to the correct dashboard.

## Use Cases
- **UC-01:** Student submits a high-stress academic concern.
- **UC-02:** Counselor logs in, views the new concern, uses AI to generate a summary, and adds an internal note.
- **UC-03:** Admin reviews the monthly dashboard and exports a report for a faculty meeting.
