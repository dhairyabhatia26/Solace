# Solace - Requirement Gathering

## Project Background
Institutions face a critical challenge in tracking and managing student wellness. Without a centralized system, counselors, faculty mentors, and leadership lack actionable intelligence on student stress, recurring concerns, and overall mental health trends.

## Problem Statement
Student wellness concerns are currently managed informally via WhatsApp, paper forms, and isolated notes. This leads to:
- Lack of visibility for HOD/Principal.
- Dropped cases or delayed interventions.
- Inability to track counselor workload.
- No aggregated insights for preventive support programs.

## Stakeholders
- Students
- Counselors / Faculty Mentors
- HOD / Principal (Admin)
- IT Support

## Pain Points
### Student
- Fear of judgment or lack of privacy when raising a concern.
- Unsure who to contact or how long it will take to get help.
### Counselor
- Scattered notes and disorganized case tracking.
- High manual effort to summarize cases for leadership.
### HOD/Principal
- Complete blind spot regarding overall student well-being.
- Unable to justify funding for wellness initiatives without data.

## Future-State Process
1. Student submits a concern securely via the Solace platform.
2. Case is queued or automatically assigned to a counselor.
3. Counselor manages the case, adds notes, and resolves it.
4. Leadership views real-time aggregated dashboards to monitor trends.
5. AI assists by summarizing concerns and generating institutional insights.

## In-Scope Features
- Role-based access (Student, Counselor, Admin)
- Secure case submission
- Dashboard analytics and charts
- Case management workflow
- Resource library
- AI-generated case summaries and insights
- Dark/Light mode theme

## Out-of-Scope Features
- Direct clinical treatment management.
- Live chat / Video calling.
- Complex calendar scheduling.

## Assumptions
- The institution has an existing student email domain.
- Counselors are trained to handle sensitive data.

## Constraints
- Must use free tier Gemini AI API.
- PHP/MySQL stack without heavy frameworks.

## Risks
- Data privacy concerns (mitigated by secure DB design and aggregated reporting).
- Hallucinations from AI (mitigated by strict prompting and disclaimers).

## Success Metrics
- 100% of wellness cases logged centrally.
- Reduction in time to first response.
- At least 80% satisfaction score from student feedback.
- Monthly AI insight reports generated and reviewed by leadership.
