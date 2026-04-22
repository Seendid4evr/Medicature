---
theme: default
class: text-center
---

# Medicature
## Project Risk & Cost Analysis
**CSE495: IT Project Management and Entrepreneurship**

---

# 1. Project Overview

**Medicature:** Bangladesh's Most Advanced Digital Health Platform

- **System Size:** ~6,000 Lines of Code (6 KLOC)
- **Key Features:**
    - 💊 Offline drug catalog (21,000+ records)
    - ⏰ Smart medication scheduling & alarms
    - 👨‍👩‍👦 Family & dependent management
    - 🤖 AI Symptom Triage integration
- **Stack:** PHP, MySQL, Progressive Web App (PWA)

---

# 2. Categorizing Project Costs

To establish our baseline, we categorize Medicature's expenses:

*   **Direct Costs:** Developer labor (3 person-months), Cloud VPS setup, 21k+ DB processing.
*   **Indirect Costs:** Administrative expenses, team meetings, workspace utilities.
*   **Opportunity Costs:** Value lost by developing our own offline DB instead of utilizing an expensive REST API.
*   **Risk-Related Costs:** Contingency reserves utilized for recovering our recent emoji and file encoding bugs.

---

# 3. Cost Estimation (Analogous / Ratio Model)

**Projecting costs based on Bangladesh market rates:**

*   **Market Benchmark:** BDT 45,000 per KLOC.
    *   *(Derived via Bottom-Up Estimation: 2 devs $\times$ 2.5 months = 5 Man-Months. 5 Man-Months $\times$ 90k BDT/month = 450,000 BDT for a typical 10 KLOC portal).*
*   **Medicature Baseline:** 6 KLOC $\times$ 45,000 = 270,000 BDT
*   **Complexity Factor:** 1.25 Multiplier (Due to Healthcare Data & Offline PWA)
*   **Calculation:** 
    $$270,000 \times 1.25$$
*   **Approved Budget (BAC):** **337,500 BDT**

---

# 4. Earned Value Management (EVM)
*Answering: Are we late? Are we over budget?*

**Mid-Project Status (50% Timeline Target):**
*   **BAC (Budget at Completion):** 337,500 BDT
*   **Planned Work:** 50% $\rightarrow$ **PV = 168,750 BDT**
*   **Actual Work Completed:** 55% $\rightarrow$ **EV = 185,625 BDT**
*   **Actual Cost Spent:** $\rightarrow$ **AC = 210,000 BDT** 

*(Note: AC is higher due to manual bug-fixing efforts during schema setups and file recoveries).*

---

# 5. EVM Performance Metrics

*   **Schedule Performance (SPI) = EV / PV = 1.10**
    *   *Result:* We are completing 110% of planned work. **Ahead of schedule!**
*   **Cost Performance (CPI) = EV / AC = 0.88**
    *   *Result:* Operating at 88% cost efficiency. **Slightly over budget.**
*   **Estimate at Completion (EAC):** 383,522 BDT
*   **Variance at Completion (VAC):** -46,022 BDT (Projected overage)

---

# 6. SWOT Analysis (Internal Strategy)

### 💪 Strengths
- **Massive Proprietary Data:** 21k+ offline local drug catalog eliminating constant API dependency.
- **Architectural Efficiency:** Highly optimized ~6,000 LOC codebase with PWA local caching.
- **Unified Health Wallet:** Ability to manage medication adherence for multiple family dependents natively.

### ⚠️ Weaknesses
- **Technical Fragility:** Legacy PHP backend history of severe character encoding (Mojibake) corruption.
- **High Maintenance Overhead:** Requires entirely manual CSV imports rather than automated syncs for drug updates.

---

# 6.1 SWOT Analysis (External Market)

### 🌟 Opportunities
- **Market Gap in Smart Adherence:** While giants handle delivery, nobody owns the 'daily tracking habit' in Bangladesh.
- **AI Triage Expansion:** Established daily active userbase can easily be transitioned to localized AI Symptom checking.

### 🛑 Threats
- **Regulatory Scrutiny:** Holding vulnerable patient data attracts deep legal demands under the BD Cyber Security Act.
- **Capital-Rich Competitors:** Heavily funded rivals (Arogga, Prava Health) could easily copy alarm functionality to crush our positioning.

---

# 7. Risk Quantification & EMV

*Calculating Expected Monetary Value (EMV) = Probability $\times$ Impact*

1.  **Data Breach / BD Cyber Security Act Lawsuit (Threat):**
    $P=0.30 \times I=-30k \rightarrow$ **-9,000 BDT**
2.  **Aggressive Competitors (Arogga, Prava Health) (Threat):**
    $P=0.50 \times I=-75k \rightarrow$ **-37,500 BDT**
3.  **High Local Market Adoption (Opportunity):**
    $P=0.60 \times I=+150k \rightarrow$ **+90,000 BDT**

**Total EMV = +35,500 BDT Net Positive Financial Exposure**

---

# 8. Conclusion

*   **Financial Health:** The highly optimized ~6,000 LOC structure allows an affordable estimated completion cost (EAC ~383k BDT). The schedule is excellent (SPI = 1.10).
*   **Risk Profile:** Highly favorable. Existing technical threats are low-impact, and the massive market opportunity heavily outweighs all threats (EMV = +35k BDT).
*   **Next Steps:** Finalize security tests and launch Premium AI Triage features.

---

# Thank You!
### Questions?

---

# 9. References
- **Chowdhury, Proma.** *"COST ANALYSIS"* & *"Risk"* (CSE495 Slides, East West University).
- **Cyber Security Act (CSA), 2023.** Government of Bangladesh.
- **DGDA Data Index.** Bangladesh medicine catalog mappings.
- **BD IT Salary Matrix.** Bottom-up parameter proxy for man-month estimations.
