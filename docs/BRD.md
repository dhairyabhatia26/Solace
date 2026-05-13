# Business Requirements Document (BRD) - Solace

## Business Objective
To establish a structured, data-driven platform that streamlines the management of student wellness cases, ensuring timely support and providing institutional leadership with actionable intelligence to foster a healthier campus environment.

## Business Problem
The current ad-hoc methods of handling student distress result in poor accountability, lack of trend analysis, and potential risks of severe cases falling through the cracks. The institution needs a unified system to manage these cases securely.

## Expected Benefits
- **Improved Student Support:** Faster, structured response to concerns.
- **Operational Efficiency:** Counselors can track and manage their workload effectively.
- **Strategic Insight:** Leadership can identify macro trends (e.g., spike in financial stress) and deploy targeted interventions.

## Stakeholder Roles
1. **Student:** Primary user seeking support.
2. **Counselor:** Professional managing and resolving the cases.
3. **Admin (HOD/Principal):** Leadership overseeing the wellness landscape.

## High-Level Business Requirements
- **BR-01:** The system shall provide distinct portals for Students, Counselors, and Admins.
- **BR-02:** The system shall allow students to securely submit wellness requests.
- **BR-03:** The system shall allow counselors to track, update, and resolve assigned cases.
- **BR-04:** The system shall provide leadership with an aggregated analytical dashboard.
- **BR-05:** The system shall utilize AI to generate case summaries and non-clinical guidance.

## Business Rules
- Only assigned counselors and admins can view specific student case details.
- AI features must explicitly include a non-clinical disclaimer.
- Critical severity cases must be flagged for immediate escalation to Admin.

## Success KPIs
- Total cases handled per month.
- Average case resolution time.
- Counselor utilization rate.
- Volume of high-urgency cases escalated appropriately.

## Process Flow
1. Student Registration/Login -> 2. Submission of Concern -> 3. Case Queued -> 4. Counselor Assigns/Reviews -> 5. Intervention/Notes Added -> 6. Case Resolved -> 7. Student Feedback -> 8. Admin Aggregated Reporting.

## Reporting Needs
- Monthly Case Trend Report
- Category-wise Distribution
- Counselor Workload Summary

## Institutional Impact
Enhances the institution's reputation by actively demonstrating a commitment to student mental health and well-being through professional, structured support mechanisms.
