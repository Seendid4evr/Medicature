# Medicature: Speaker Sheet (English)
*Use this sheet as your script or speaking notes while presenting the `risk_cost_presentation.md` slides.*

---

### Slide 1: Title Slide (Medicature: Project Risk & Cost Analysis)
**Speaker Notes:**
"Hello everyone. Today, I'll be presenting the comprehensive Risk and Cost Analysis for our project, **Medicature**, as part of our CSE495 IT Project Management course. We applied the core frameworks from our course lectures to evaluate the financial and strategic health of our digital platform."

---

### Slide 2: Project Overview
**Speaker Notes:**
"Let's start with a quick overview of what Medicature is. We aim to be Bangladesh's most advanced digital health platform. 
Our source code currently stands at a highly optimized 6,000 lines of code. The platform features an offline catalog of over 21,000 local medicines, smart scheduling alarms, and family health management tools. Technically, it is a Progressive Web App (PWA) built on PHP and MySQL, designed to be fast and functional even without constant internet access."

---

### Slide 3: Categorizing Project Costs
**Speaker Notes:**
"To properly manage our budget, we first categorized all project expenses:
- **Direct Costs** cover our core work: Three months of developer labor, cloud hosting, and processing our massive drug database.
- **Indirect Costs** include our team administrative tools and workspace utilities.
- For **Opportunity Costs**, we decided to invest time building our own offline database instead of paying a high monthly fee for a readymade commercial API. 
- Finally, our **Risk-Related Costs** represent our contingency reserves. In fact, we recently had to use these reserves to recover from a massive character encoding bug that corrupted our UI!"

---

### Slide 4: Cost Estimation (Analogous / Ratio Model)
**Speaker Notes:**
"Now, how did we estimate our budget? 
We used the **Analogous or Ratio Estimating model** based on current rates in the Bangladesh IT market. 
Typically, building a 10,000-line portal requires about 5 man-months of work, which costs around 450,000 BDT. That gives us a benchmark of 45,000 BDT per 1,000 lines of code.
Since Medicature is 6,000 lines, our base cost is 270,000 BDT. However, because dealing with healthcare data and PWA caching is complex, we applied a 1.25 multiplier. This brought our approved Budget at Completion (BAC) to **337,500 BDT**."

---

### Slide 5: Earned Value Management (EVM)
**Speaker Notes:**
"As project managers, we constantly ask: *Are we late? Are we over budget?* Let's look at our Earned Value Management (EVM) data precisely at the halfway mark of our timeline.
Our total budget is 337,500 BDT.
- By now, we **planned** to finish 50% of the work, so our Planned Value (PV) is **168,750 BDT**.
- However, we worked fast and **actually completed** 55% of the work—meaning our Earned Value (EV) is **185,625 BDT**.
- Our Actual Cost (AC) so far is **210,000 BDT**. This is a bit high because we needed extra time to manually fix those severe database scheme bugs I mentioned earlier."

---

### Slide 6: EVM Performance Metrics
**Speaker Notes:**
"What do those numbers mean for our performance?
- Our **Schedule Performance Index (SPI)** is 1.10. A number over 1.0 means we are officially **ahead of schedule**—completing 110% of planned work!
- Our **Cost Performance Index (CPI)** is 0.88. This means for every 1 BDT spent, we are getting 0.88 BDT in value. We are slightly over budget.
Because of this slight overage, we forecast our Estimate at Completion (EAC) will be around **383,000 BDT**, which is roughly a 46,000 BDT deficit from our original plan."

---

### Slide 7: SWOT Analysis (Internal Strategy)
**Speaker Notes:**
"Moving onto Risk Analysis, let's look at our internal SWOT.
Our biggest **Strength** is our massive proprietary asset: the offline 21,000+ drug catalog. Along with our highly efficient 6,000 lines of code, the app runs blazingly fast.
Our main **Weakness** is technical fragility. Our legacy PHP backend is highly vulnerable to 'Mojibake' or text encoding corruptions. Furthermore, our database updates still require manual CSV importing, which is a significant maintenance overhead."

---

### Slide 8: SWOT Analysis (External Market)
**Speaker Notes:**
"Looking outward at the market:
Our greatest **Opportunity** is an untapped niche. While giants like Arogga dominate the *delivery* of medicine, nobody currently owns the *daily tracking and smart alarm habit* in Bangladesh. We can quickly fill this gap and even upsell AI Triage later.
Our primary **Threats** are compliance and competition. Handling health records means we fall under the strict compliance demands of the BD Cyber Security Act. Secondly, heavily funded rivals like DocTime could easily copy our alarm features if they choose to pivot."

---

### Slide 9: Risk Quantification & EMV
**Speaker Notes:**
"To financially quantify these risks, we calculated the Expected Monetary Value (EMV).
1. The threat of a Data Breach attracting a Cyber Security lawsuit has a 30% probability and a 30k impact, resulting in a **-9,000 BDT** risk.
2. The threat of aggressive competitors copying our features has a 50% probability, adding a **-37,500 BDT** risk.
3. However, the opportunity of high local market adoption has a massive 60% probability to yield +150,000 BDT due to the extreme demand for digital health. That’s a **+90,000 BDT** expected gain.
Overall, our Net Expected Monetary Value is a positive **+35,500 BDT**!"

---

### Slide 10: Conclusion
**Speaker Notes:**
"To conclude:
From a financial perspective, Medicature's highly optimized code allows us to deliver enterprise-level features while keeping our projected completion cost incredibly affordable at just under 400k BDT. We are moving faster than our schedule dictates.
From a risk perspective, our profile is highly favorable. The slight technical threats are completely overshadowed by our massive financial opportunity in the local market.
Our immediate next steps are to conduct final security tests and roll out our backend AI features."

---

### Slide 11: References & Q&A
**Speaker Notes:**
"Our calculations use frameworks directly from the CSE495 Cost and Risk slides. We also mapped our assumptions to the local Bangladesh Cyber Security Act, DGDA catalogs, and prevailing IT salary matrices. 
Thank you for your time. I would now like to open the floor to any questions."
