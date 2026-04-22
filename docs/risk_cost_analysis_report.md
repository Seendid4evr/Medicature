# Medicature: Comprehensive Risk and Cost Analysis Report

**Course Context:** CSE495 IT Project Management and Entrepreneurship
**Project:** Medicature (Digital Health Platform)
**System Size:** ~6,000 Lines of Code (6 KLOC)

## 1. Executive Summary
This report provides a detailed Cost and Risk Analysis for the **Medicature** project, a digital health platform designed for the Bangladesh market featuring a 21,000+ drug database, family health management, and PWA capabilities. The analysis is built upon current market rates for software development in Bangladesh, utilizing the framework from the instructional materials.

---

## 2. Cost Analysis

### 2.1 Cost Categorization
In project management, costs must be clearly categorized to establish an accurate baseline:
- **Direct Costs:** Developer labor (estimated at 3 person-months internally), Cloud VPS server hosting, and initial data processing for the 21,000+ medical records.
- **Indirect Costs:** Support expenses such as administrative tools, utilities, and internal team communication software.
- **Opportunity Costs:** The potential benefits lost by investing time into building an offline, standalone drug database rather than paying a premium for a readymade online medical API.
- **Risk-Related Costs:** Contingency reserves utilized for recovering unexpected technical debt (e.g., the recent UTF-8/Windows-1252 file encoding corruption and complex database schema migrations).

### 2.2 Cost Estimation (Analogous / Ratio Model)
Using the **Analogous Estimating** technique based on local market trends in Bangladesh:
- **Benchmark Derivation (Bottom-Up Estimation Proxy):** In the modern Bangladesh IT market, costs are estimated by developer man-months, not directly by KLOC. A mid-level web developer writes an average of 50-100 lines of production code daily. Developing a 10 KLOC (10,000 lines) application typically requires 2 developers working for 2.5 months (5 Person-Months). At an average agency cost of 90,000 BDT per month per resource, the baseline cost is logically estimated at `5 Person-Months × 90,000 BDT = 450,000 BDT` (equivalent to 45,000 BDT per KLOC).
- **Medicature Project Size:** Verified at exactly **5,605 Lines of Code** (~6 KLOC), built with custom PHP, MySQL, and Javascript.
- **Base Cost for 6 KLOC:** 6 * 45,000 = 270,000 BDT.
- **Complexity Factor:** Because Medicature involves sensitive healthcare data logic and PWA offline capabilities, a High Complexity factor of **1.25** is applied.
- **Calculated Baseline Budget (BAC):** 270,000 BDT * 1.25 = **337,500 BDT**.

### 2.3 Earned Value Management (EVM) Analysis
To answer the core managerial questions (*Are we spending too much? Are we late?*), we apply EVM at the halfway point of the Medicature project lifecycle.

**Project Status Data:**
- **Budget at Completion (BAC):** 337,500 BDT (Approved Baseline)
- **Planned Work Completion:** 50%
- **Actual Work Completion:** 55% (Core UI, family module, and drug DB fully repaired and imported)
- **Actual Cost Spent (AC):** 210,000 BDT (Elevated due to manual bug fixing and data recovery efforts)

**EVM Calculations:**
1. **Planned Value (PV) =** 50% of 337,500 = **168,750 BDT**
2. **Earned Value (EV) =** 55% of 337,500 = **185,625 BDT**

**Performance Assessment:**
- **Schedule Variance (SV):** `EV - PV = 185,625 - 168,750 = +16,875 BDT` *(Ahead of schedule)*
- **Schedule Performance Index (SPI):** `EV / PV = 185,625 / 168,750 = 1.10` *(Completing 110% of planned work)*
- **Cost Variance (CV):** `EV - AC = 185,625 - 210,000 = -24,375 BDT` *(Over budget strictly due to unexpected tech debt recovery)*
- **Cost Performance Index (CPI):** `EV / AC = 185,625 / 210,000 = 0.88` *(Yielding 0.88 BDT of value for every 1 BDT spent)*

**Forecasting Final Costs:**
- **Estimate at Completion (EAC) =** `BAC / CPI = 337,500 / 0.88 = 383,522 BDT`
- **Variance at Completion (VAC) =** `BAC - EAC = 337,500 - 383,522 = -46,022 BDT`
- *Conclusion:* The project is currently projected to cost roughly 46K BDT more than planned, but it is moving 10% faster than scheduled.

---

## 3. Risk Analysis

### 3.1 Comprehensive SWOT Analysis
Given the competitive healthtech landscape in Bangladesh, a deep dive into the SWOT factors is critical for strategic alignment.

**Strengths (Internal Advantages)**
- **Massive Proprietary Data Asset:** The successful integration of an offline 21,000+ local drug catalog (with generic, dosage, and side-effect data) provides an immense edge. Most basic apps require constant online API calls for this data.
- **Architectural Efficiency:** Built with an optimized ~6,000 LOC structure natively on PHP/MySQL. The implementation of Progressive Web App (PWA) caching strategies significantly reduces recurring server load and provides a seamless "app-like" experience without requiring App Store approvals.
- **Unified Family Health Wallet:** Allowing a primary user to manage medication adherence for multiple dependents is a unique value proposition that drives high daily active usage.

**Weaknesses (Internal Challenges)**
- **Technical Fragility:** The legacy monolithic PHP backend has shown vulnerability to severe character encoding errors (Mojibake), which previously corrupted core UI files and delayed the schedule.
- **High Maintenance Overhead:** Currently, updating the 21k medicine database depends completely on manual CSV imports. There is no automated sync with Bangladesh Directorate General of Drug Administration (DGDA) databases.

**Opportunities (External Potential)**
- **Market Gap for Smart Medication Adherence:** While giants like Arogga dominate "medicine delivery", there is a distinct lack of tools focusing strictly on smart alarms, daily adherence tracking, and family health management in Bangladesh.
- **AI Triage Expansion:** Once the baseline user data is established, the platform can easily pivot to offer localized AI symptom triage, preparing patients automatically before tele-consultations.

**Threats (External Risks)**
- **Data Privacy & Legal Compliance:** Managing sensitive patient health records and family connections attracts strict regulatory scrutiny under the newly formed Bangladesh Cyber Security Act (CSA) and Personal Data Protection acts. Non-compliance limits scalability.
- **Capital-Rich Competitors:** Local giants like DocTime, Prava Health, and Arogga possess massive marketing budgets. If they decide to introduce "medicine alarms" into their existing apps, Medicature limits its unique market positioning.

### 3.2 Expected Monetary Value (EMV) Analysis
Assigning expected probabilities and financial impacts around post-launch scenarios:

| Risk Scenario | Probability (P) | Impact (I) [BDT] | Risk Score (EMV) [BDT] |
| :--- | :--- | :--- | :--- |
| **Risk A: Data Breach / Cyber Security Act Penalty (Threat)** | 0.30 | -30,000 | -9,000 |
| **Risk B: Established Competitor Updates (Threat)** | 0.50 | -75,000 | -37,500 |
| **Risk C: Tech Bugs / UI Issues Post-Launch (Threat)** | 0.40 | -20,000 | -8,000 |
| **Risk D: High Local Market Adoption (Opportunity)** | 0.60 | +150,000 | +90,000 |
| **Total Expected Monetary Value (EMV)** | | | **+35,500 BDT** |

*Interpretation:* Total EMV is positive (+35,500 BDT). The high probability (60%) of a successful reception in the Bangladesh market more than offsets the financial threats related to technical bugs and competition.

## 4. Conclusion
Medicature's highly efficient core codebase (~6,000 LOC) allows it to maintain a low budget (EAC of ~383k BDT) while delivering enterprise-level features (21k database, PWA). Although recent bug-fixing drove the cost efficiency (CPI) slightly down to 0.88, the project boasts an excellent schedule index (SPI = 1.1) and a positive risk profile, making it a viable and profitable venture.

---

## 5. References
1. **Chowdhury, Proma.** *"COST ANALYSIS"* (CSE495 Lecture Slides). East West University. (Primary source for Analogous Estimating & EVM application).
2. **Chowdhury, Proma.** *"Risk"* (CSE495 Lecture Slides). East West University. (Primary source for SWOT framework and EMV quantification).
3. **Cyber Security Act (CSA), 2023.** Government of the People's Republic of Bangladesh. (Reference for data privacy compliance threats).
4. **Directorate General of Drug Administration (DGDA).** (Reference matrix for the offline Bangladesh medicine database compilation).
5. **Software Industry Salary Matrix BD.** (Basis for the bottom-up man-month cost calculation of 450,000 BDT per 10 KLOC).
