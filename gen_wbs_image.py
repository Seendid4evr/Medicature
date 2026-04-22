import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
from matplotlib.patches import FancyBboxPatch

fig, ax = plt.subplots(figsize=(22, 10))
ax.set_xlim(0, 22)
ax.set_ylim(0, 10)
ax.axis('off')
fig.patch.set_facecolor('#F8FAFF')

ROOT_C='#1A3C5E'; PH1_C='#2563EB'; PH2_C='#7C3AED'
PH3_C='#059669'; PH4_C='#D97706'; PH5_C='#DC2626'
LEAF_BG='#EFF6FF'; TEXT_W='#FFFFFF'; TEXT_D='#1A3C5E'; LINE_C='#94A3B8'

def box(ax,x,y,w,h,label,bg,fg=TEXT_W,fs=9,bold=False):
    p=FancyBboxPatch((x-w/2,y-h/2),w,h,
        boxstyle="round,pad=0.05,rounding_size=0.22",
        linewidth=1.8,edgecolor=bg,facecolor=bg,zorder=3)
    ax.add_patch(p)
    ax.text(x,y,label,ha='center',va='center',fontsize=fs,
            fontweight='bold' if bold else 'normal',color=fg,zorder=4,linespacing=1.3)

def leaf(ax,x,y,w,h,label,bc):
    p=FancyBboxPatch((x-w/2,y-h/2),w,h,
        boxstyle="round,pad=0.05,rounding_size=0.18",
        linewidth=1.5,edgecolor=bc,facecolor=LEAF_BG,zorder=3)
    ax.add_patch(p)
    ax.text(x,y,label,ha='center',va='center',fontsize=7.2,
            color=TEXT_D,zorder=4,linespacing=1.25)

def elbow(ax,px,py,cx,cy,c,lw=1.6):
    my=(py+cy)/2
    ax.plot([px,px],[py,my],color=c,lw=lw,zorder=2,solid_capstyle='round')
    ax.plot([px,cx],[my,my],color=c,lw=lw,zorder=2,solid_capstyle='round')
    ax.plot([cx,cx],[my,cy],color=c,lw=lw,zorder=2,solid_capstyle='round')

# ── Positions ──────────────────────────────────────────────────────────────────
ROOT_X,ROOT_Y=11,9.4; ROOT_W,ROOT_H=5.6,0.65
PH_Y=7.8; PH_W=3.1; PH_H=0.72
PH_XS=[2.1,6.0,11.0,16.0,20.0]
LEAF_TOP=6.25; LEAF_H=0.52; LEAF_W=2.75; LEAF_GAP=1.1

phases=[('1.1\nInitiation Phase',PH1_C),('1.2\nDesign Phase',PH2_C),
        ('1.3\nDevelopment Phase',PH3_C),('1.4\nTesting Phase',PH4_C),
        ('1.5\nDeployment\n& Closure',PH5_C)]

leaves=[
    [('1.1.1 Requirements\n& Scope Analysis',PH1_C),('1.1.2\nFeasibility Study',PH1_C),('1.1.3 Project Charter\n& Planning',PH1_C)],
    [('1.2.1 UI/UX Wireframes\n& Mockups',PH2_C),('1.2.2 Database\nSchema Design',PH2_C),('1.2.3 System Architecture\nPlanning',PH2_C)],
    [('1.3.1 Backend\n(PHP, Auth, APIs)',PH3_C),('1.3.2 Frontend\n(HTML/CSS/JS)',PH3_C),
     ('1.3.3 Symptom Checker\nAI Module',PH3_C),('1.3.4 Pharmacy &\nMedicines Module',PH3_C),
     ('1.3.5 Family Health\nManagement',PH3_C),('1.3.6 Reports &\nCalculators',PH3_C)],
    [('1.4.1 Unit &\nIntegration Testing',PH4_C),('1.4.2 User Acceptance\nTesting (UAT)',PH4_C),('1.4.3 Bug Fixing\n& QA Review',PH4_C)],
    [('1.5.1 Server Setup\n& Deployment',PH5_C),('1.5.2 Documentation\n& User Manual',PH5_C),('1.5.3 Final Handover\n& Training',PH5_C)],
]

# Title
ax.text(11,9.85,'Medicure — Work Breakdown Structure (WBS)',ha='center',va='center',
        fontsize=14,fontweight='bold',color=ROOT_C)
ax.text(11,9.60,'Phase-Based  |  2 Developers  |  3 Months  |  18 Work Packages  |  708 Total Hours',
        ha='center',va='center',fontsize=8.5,color='#555555',style='italic')

# Root
box(ax,ROOT_X,ROOT_Y,ROOT_W,ROOT_H,'1.0  Medicure Digital Health Platform',ROOT_C,fs=11,bold=True)

# Phases + leaves
for ph_x,(ph_label,ph_c),leaf_list in zip(PH_XS,phases,leaves):
    elbow(ax,ROOT_X,ROOT_Y-ROOT_H/2,ph_x,PH_Y+PH_H/2,ph_c)
    box(ax,ph_x,PH_Y,PH_W,PH_H,ph_label,ph_c,fs=8.5,bold=True)

    n=len(leaf_list)
    if n<=3:
        for i,(lbl,lc) in enumerate(leaf_list):
            ly=LEAF_TOP-i*LEAF_GAP
            elbow(ax,ph_x,PH_Y-PH_H/2,ph_x,ly+LEAF_H/2,ph_c,lw=1.4)
            leaf(ax,ph_x,ly,LEAF_W,LEAF_H,lbl,lc)
    else:  # 6 items – two columns
        col_gap=1.55
        for i,(lbl,lc) in enumerate(leaf_list):
            row=i//2; col=i%2
            lx=ph_x-col_gap/2+col*col_gap
            ly=LEAF_TOP-row*LEAF_GAP
            elbow(ax,ph_x,PH_Y-PH_H/2,lx,ly+LEAF_H/2,ph_c,lw=1.4)
            leaf(ax,lx,ly,1.42,LEAF_H,lbl,lc)

# Legend
items=[mpatches.Patch(color=ROOT_C,label='1.0  Project Root'),
       mpatches.Patch(color=PH1_C,label='1.1  Initiation (44 hrs)'),
       mpatches.Patch(color=PH2_C,label='1.2  Design (72 hrs)'),
       mpatches.Patch(color=PH3_C,label='1.3  Development (440 hrs)'),
       mpatches.Patch(color=PH4_C,label='1.4  Testing (92 hrs)'),
       mpatches.Patch(color=PH5_C,label='1.5  Deployment & Closure (60 hrs)')]
ax.legend(handles=items,loc='lower left',fontsize=8,framealpha=0.92,
          edgecolor='#CCCCCC',fancybox=True,
          title='Phase Legend  |  Total: 708 hrs',title_fontsize=8.5,
          bbox_to_anchor=(0.0,0.0))

plt.tight_layout(pad=0.1)
out=r'c:\xampp\htdocs\medicure\wbs_diagram.png'
plt.savefig(out,dpi=180,bbox_inches='tight',facecolor=fig.get_facecolor())
plt.close()
print(f'[SUCCESS] {out}')
