<?php
require_once '../includes/session.php';
require_once '../config/database.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$symptomsText = $data['symptoms'] ?? '';

if (empty(trim($symptomsText))) {
    echo json_encode(['response' => "Please describe your symptoms (e.g. 'I have fever and headache')."]);
    exit;
}

$knowledgeBase = [

    
    'emergency' => [
        'keywords' => ['chest pain', 'heart attack', 'stroke', 'unconscious', 'seizure', 'convulsion',
                       'difficulty breathing', 'cannot breathe', "can't breathe", 'severe bleeding',
                       'poisoning', 'overdose', 'paralysis', 'sudden blindness'],
        'severity' => 'emergency',
        'message'  => "🚨 **EMERGENCY ALERT**\n\nThe symptoms you described may indicate a **serious medical emergency**.\n\n**Do NOT take any medicine on your own. Call for help immediately:**\n- 🏥 Go to the nearest hospital Emergency\n- 📞 Bangladesh Emergency: **999** or **16430** (health hotline)\n\nDo not delay — time is critical.",
    ],

    
    'fever' => [
        'keywords' => ['fever', 'jor', 'temperature', 'high temp', 'body hot', 'গরম', 'জ্বর'],
        'severity' => 'mild',
        'generic'  => 'Paracetamol (Acetaminophen)',
        'brands'   => ['Napa', 'Ace', 'Renova', 'Tylenol'],
        'dose'     => 'Adults: 500mg–1000mg every 4–6 hours (max 4g/day). Children: as per weight.',
        'usage'    => 'Reduces fever and relieves mild pain. Drink plenty of water and rest.',
        'warning'  => 'If fever is above 103°F (39.4°C), lasts more than 3 days, or is accompanied by rash or stiff neck — see a doctor immediately.',
    ],

    
    'headache' => [
        'keywords' => ['headache', 'head pain', 'matha byatha', 'migraine', 'মাথাব্যথা', 'head ache'],
        'severity' => 'mild',
        'generic'  => 'Paracetamol or Ibuprofen',
        'brands'   => ['Napa', 'Ace', 'Ibufen', 'Brufen'],
        'dose'     => 'Paracetamol 500–1000mg or Ibuprofen 200–400mg. Take with food if using Ibuprofen.',
        'usage'    => 'Effective for tension headaches and mild migraines. Rest in a quiet, dark room.',
        'warning'  => 'Sudden severe headache ("thunderclap"), headache with fever and stiff neck, or headache after a head injury requires immediate medical attention.',
    ],

    
    'cold' => [
        'keywords' => ['cold', 'runny nose', 'stuffy nose', 'nasal congestion', 'sardi', 'সর্দি', 'sneezing', 'blocked nose'],
        'severity' => 'mild',
        'generic'  => 'Cetirizine (antihistamine) + Pseudoephedrine (decongestant)',
        'brands'   => ['Histacin', 'Alatrol-D', 'Sinarest', 'Ricon'],
        'dose'     => 'Cetirizine 10mg once daily. Pseudoephedrine as directed on pack.',
        'usage'    => 'Relieves runny nose, sneezing, and congestion. Stay hydrated and rest.',
        'warning'  => 'Avoid Pseudoephedrine if you have high blood pressure or heart conditions. If symptoms worsen after 7 days, consult a doctor.',
    ],

    
    'allergy' => [
        'keywords' => ['allergy', 'itching', 'rash', 'hives', 'skin rash', 'urticaria', 'চুলকানি', 'এলার্জি', 'allergic'],
        'severity' => 'mild',
        'generic'  => 'Cetirizine or Loratadine (antihistamine)',
        'brands'   => ['Alatrol', 'Loratin', 'Histadin', 'Fexo'],
        'dose'     => 'Cetirizine 10mg once daily at night OR Loratadine 10mg once daily.',
        'usage'    => 'Relieves allergic reactions including itching, rash and hives.',
        'warning'  => 'If you experience swelling of lips/tongue/throat or difficulty breathing, this is anaphylaxis — go to an ER immediately.',
    ],

    
    'cough' => [
        'keywords' => ['cough', 'khansi', 'কাশি', 'dry cough', 'wet cough', 'phlegm', 'mucus'],
        'severity' => 'mild',
        'generic'  => 'Dextromethorphan (dry cough) or Ambroxol (wet cough)',
        'brands'   => ['Tussca', 'Dextrocin', 'Ambrox', 'Mucosolvan'],
        'dose'     => 'As directed on packaging. Syrup form often more effective for cough.',
        'usage'    => 'Dry cough: suppressants. Wet/productive cough: expectorants to loosen mucus.',
        'warning'  => 'Cough lasting more than 3 weeks, or with blood, or with difficulty breathing — see a doctor. Do not give adult cough medicine to children under 6.',
    ],

    
    'sore_throat' => [
        'keywords' => ['sore throat', 'throat pain', 'gola byatha', 'গলা ব্যথা', 'tonsil', 'throat infection'],
        'severity' => 'mild',
        'generic'  => 'Benzydamine (topical) or Paracetamol for pain',
        'brands'   => ['Difflam', 'Strepsils', 'Napa', 'Betadine Gargle'],
        'dose'     => 'Gargle with warm salt water. Benzydamine spray or lozenges as directed.',
        'usage'    => 'Relieves throat pain and inflammation. Drink warm fluids.',
        'warning'  => 'If throat is severely swollen, you cannot swallow, or you have high fever — see a doctor. Bacterial tonsillitis may need antibiotics (prescription required).',
    ],

    
    'acidity' => [
        'keywords' => ['acidity', 'heartburn', 'gastric', 'acid reflux', 'indigestion', 'গ্যাস', 'বুক জ্বালা', 'পেট জ্বালা', 'ulcer', 'stomach burn'],
        'severity' => 'mild',
        'generic'  => 'Omeprazole (PPI) or Antacid (Magnesium Hydroxide)',
        'brands'   => ['Seclo', 'Losectil', 'Neoceptin-R', 'Antacid Plus'],
        'dose'     => 'Omeprazole 20mg once daily before breakfast. Antacid after meals and at bedtime.',
        'usage'    => 'Reduces stomach acid production. Avoid spicy food, coffee, and lying down after eating.',
        'warning'  => 'If you have black/tarry stool, vomiting blood, or severe stomach pain — see a doctor immediately (possible ulcer).',
    ],

    
    'diarrhea' => [
        'keywords' => ['diarrhea', 'loose motion', 'loose stool', 'পাতলা পায়খানা', 'dysentery', 'stomach upset', 'watery stool'],
        'severity' => 'mild',
        'generic'  => 'ORS (Oral Rehydration Salts) + Loperamide (for adults)',
        'brands'   => ['ORS Saline', 'Orsaline-N', 'Imodium', 'Lomotil'],
        'dose'     => 'ORS: dissolve 1 sachet in 1 litre clean water, sip frequently. Loperamide 2mg after each loose stool (adults only, max 8mg/day).',
        'usage'    => 'Rehydration is the priority. ORS replaces lost salts and water.',
        'warning'  => 'Children should NOT take Loperamide. If diarrhea lasts more than 3 days, contains blood, or is accompanied by high fever — see a doctor. Severe dehydration needs IV fluids at a hospital.',
    ],

    
    'nausea' => [
        'keywords' => ['nausea', 'vomiting', 'bomi', 'বমি', 'বমি বমি ভাব', 'feel like vomiting', 'motion sickness'],
        'severity' => 'mild',
        'generic'  => 'Domperidone or Ondansetron',
        'brands'   => ['Domstal', 'Motilium', 'Zofen', 'Vomistop'],
        'dose'     => 'Domperidone 10mg 3 times daily before meals. Ondansetron 4–8mg as needed.',
        'usage'    => 'Take 30 minutes before a meal. Rest and avoid strong smells.',
        'warning'  => 'Persistent vomiting (more than 24 hrs), blood in vomit, or vomiting after a head injury — see a doctor.',
    ],

    
    'constipation' => [
        'keywords' => ['constipation', 'কোষ্ঠকাঠিন্য', 'hard stool', 'cannot poop', 'no bowel movement', 'kabj'],
        'severity' => 'mild',
        'generic'  => 'Lactulose or Isabgol (Psyllium husk)',
        'brands'   => ['Duphalac', 'Lactitol', 'Softovac', 'Isabgol Husk'],
        'dose'     => 'Lactulose 15–30ml once daily. Isabgol 1–2 teaspoons with a full glass of water.',
        'usage'    => 'Increase fluid and fibre intake. Regular physical activity helps.',
        'warning'  => 'If constipation is accompanied by severe pain, blood in stool, or unexplained weight loss — see a doctor.',
    ],

    
    'pain' => [
        'keywords' => ['body pain', 'muscle pain', 'body ache', 'joint pain', 'back pain', 'ব্যথা', 'গা ব্যথা', 'ব্যাক পেইন', 'backache', 'arthritis'],
        'severity' => 'mild',
        'generic'  => 'Ibuprofen (NSAID) or Diclofenac',
        'brands'   => ['Ibufen', 'Brufen', 'Voltaren', 'Clofenac'],
        'dose'     => 'Ibuprofen 400mg 3 times daily with food. Diclofenac 50mg twice daily with food.',
        'usage'    => 'Take with food or milk to protect the stomach. Rest the affected area.',
        'warning'  => 'Avoid NSAIDs if you have stomach ulcers, kidney problems, or are on blood thinners. Do not use long-term without a doctor\'s advice.',
    ],

    
    'dizziness' => [
        'keywords' => ['dizziness', 'dizzy', 'vertigo', 'মাথা ঘোরা', 'lightheaded', 'spinning', 'balance problem'],
        'severity' => 'moderate',
        'generic'  => 'Cinnarizine or Meclizine',
        'brands'   => ['Stugeron', 'Cinaron', 'Bonamine'],
        'dose'     => 'Cinnarizine 25mg 3 times daily. Meclizine 25mg once daily.',
        'usage'    => 'Sit or lie down when dizzy. Avoid sudden head movements.',
        'warning'  => 'Sudden severe dizziness, especially with numbness, vision problems, or difficulty speaking — this may be a stroke. Call 999 or go to an ER immediately.',
    ],

    
    'insomnia' => [
        'keywords' => ['insomnia', 'cannot sleep', 'sleep problem', 'ঘুম না হওয়া', 'sleeplessness', 'restless'],
        'severity' => 'moderate',
        'generic'  => 'Melatonin (OTC sleep aid)',
        'brands'   => ['Melatonin supplement'],
        'dose'     => '0.5mg–5mg taken 30 minutes before bed.',
        'usage'    => 'Maintain a regular sleep schedule. Avoid screens and caffeine before bed.',
        'warning'  => 'Prescription sleep medicines (benzodiazepines) must only be taken under doctor supervision. If insomnia is chronic or linked to anxiety/depression — see a doctor.',
    ],

    
    'skin_infection' => [
        'keywords' => ['skin infection', 'wound', 'cut', 'eczema', 'fungal', 'ringworm', 'athlete\'s foot', 'ক্ষত'],
        'severity' => 'mild',
        'generic'  => 'Povidone-Iodine (wound) or Clotrimazole (fungal)',
        'brands'   => ['Betadine', 'Savlon', 'Candid', 'Daktarin'],
        'dose'     => 'Apply topically as directed. Clean wound first with clean water.',
        'usage'    => 'Clean the affected area before applying. Cover with a sterile dressing.',
        'warning'  => 'Deep wounds, animal bites, or signs of serious infection (spreading redness, pus, fever) — see a doctor. You may need antibiotics or a tetanus shot.',
    ],

    
    'eye' => [
        'keywords' => ['eye pain', 'red eye', 'eye irritation', 'conjunctivitis', 'চোখ লাল', 'চোখ জ্বালা', 'eye discharge', 'pink eye'],
        'severity' => 'mild',
        'generic'  => 'Sodium Cromoglicate (eye drops) or Artificial Tears',
        'brands'   => ['Opticrom', 'Refresh Tears', 'Visine'],
        'dose'     => '1–2 drops in each affected eye, up to 4 times daily.',
        'usage'    => 'Avoid touching eyes. Do not share eye drops or towels.',
        'warning'  => 'If there is sudden vision loss, eye injury, or severe pain — see a doctor immediately.',
    ],

    
    'uti' => [
        'keywords' => ['burning urination', 'painful urination', 'frequent urination', 'uti', 'urinary infection', 'পেশাবে জ্বালা'],
        'severity' => 'moderate',
        'generic'  => 'Nitrofurantoin or Trimethoprim (prescription needed)',
        'brands'   => ['Uvamin', 'Macrobid'],
        'dose'     => 'As prescribed by doctor. Drink plenty of water.',
        'usage'    => 'UTIs typically require a prescribed antibiotic course. Do not self-medicate with leftover antibiotics.',
        'warning'  => 'UTIs need a proper antibiotic prescribed by a doctor. If you have fever, back/flank pain, or blood in urine — see a doctor urgently (possible kidney infection).',
    ],

    
    'diabetes' => [
        'keywords' => ['diabetes', 'blood sugar', 'thirsty', 'frequent urination', 'sugar high', 'ডায়াবেটিস'],
        'severity' => 'moderate',
        'generic'  => 'Consult doctor — Metformin is commonly prescribed',
        'brands'   => ['Glucophage', 'Diaben', 'Metformin'],
        'dose'     => 'Only as prescribed by a licensed physician.',
        'usage'    => 'Diabetes management requires professional medical supervision, dietary changes, and regular blood sugar monitoring.',
        'warning'  => 'Do NOT self-prescribe diabetes medication. A doctor must confirm diagnosis via blood tests (HbA1c, fasting glucose) before starting treatment.',
    ],

    
    'hypertension' => [
        'keywords' => ['high blood pressure', 'hypertension', 'bp high', 'উচ্চ রক্তচাপ', 'blood pressure'],
        'severity' => 'moderate',
        'generic'  => 'Amlodipine or Atenolol (prescription required)',
        'brands'   => ['Amdocal', 'Aten'],
        'dose'     => 'Only as prescribed by a doctor after BP measurement.',
        'usage'    => 'Reduce salt intake, exercise regularly, and monitor BP at home.',
        'warning'  => 'Antihypertensive medications must be prescribed by a doctor. Do NOT stop your BP medicine suddenly without consulting your doctor — this can cause a dangerous rebound.',
    ],

];

$userId  = getUserId();
$lowered = strtolower(strip_tags($symptomsText));
$clean   = preg_replace('/[^\w\s]/', ' ', $lowered);

$emergencyEntry = $knowledgeBase['emergency'];
foreach ($emergencyEntry['keywords'] as $kw) {
    if (strpos($clean, $kw) !== false) {
        echo json_encode(['response' => $emergencyEntry['message'], 'severity' => 'emergency']);
        exit;
    }
}

$matched = [];
foreach ($knowledgeBase as $key => $entry) {
    if ($key === 'emergency') continue;
    foreach ($entry['keywords'] as $kw) {
        if (strpos($clean, $kw) !== false) {
            $matched[$key] = $entry;
            break;
        }
    }
}

$stopWords   = ['i','have','feeling','like','my','is','am','are','the','and','to','in','of','that','it','with',
                 'as','for','was','on','at','by','an','be','this','or','from','but','not','what','all','were',
                 'when','your','can','said','there','use','each','she','do','how','their','if','will','up',
                 'about','out','many','then','them','these','so','some','her','would','him','into','time',
                 'has','look','two','more','get','come','made','may','please','help','severe','really','very',
                 'getting','just','also','experiencing','bit','little','lot','bad','been','since','iam'];
$keywords    = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
$searchTerms = array_values(array_unique(array_filter($keywords, fn($w) => strlen($w) > 2 && !in_array($w, $stopWords))));

$dbMatches = [];
try {
    $db   = new Database();
    $conn = $db->getConnection();

    if (!empty($searchTerms)) {
        $likeClauses = array_map(fn($t) => "LOWER(bg.indication_description) LIKE ?", $searchTerms);
        $likeParams  = array_map(fn($t) => '%' . $t . '%', $searchTerms);
        $stmt = $conn->prepare("
            SELECT
                bg.name AS generic_name,
                bg.drug_class,
                bg.indication_description,
                bg.side_effects_description,
                GROUP_CONCAT(DISTINCT bm.brand_name ORDER BY bm.brand_name SEPARATOR ', ') AS brand_examples
            FROM bd_generics bg
            LEFT JOIN bd_medicines bm ON bg.name = bm.generic
            WHERE (" . implode(' OR ', $likeClauses) . ")
              AND bg.indication_description IS NOT NULL
              AND LENGTH(bg.indication_description) > 20
            GROUP BY bg.name
            ORDER BY LENGTH(bg.indication_description) ASC
            LIMIT 3
        ");
        $stmt->execute($likeParams);
        $dbMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    $userMedStmt = $conn->prepare("
        SELECT bm.generic, m.name AS brand_name
        FROM medicines m
        LEFT JOIN bd_medicines bm ON m.bd_medicine_id = bm.id
        WHERE m.user_id = ? AND m.active = 1
    ");
    $userMedStmt->execute([$userId]);
    $userMeds        = $userMedStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $userMedGenerics = array_map('strtolower', array_keys($userMeds));

} catch (Exception $e) {
    $userMedGenerics = [];
    $dbMatches       = [];
}

if (empty($matched) && empty($dbMatches)) {
    $response = "I couldn't find a strong match for: **" . htmlspecialchars($symptomsText) . "**\n\n";
    $response .= "Your symptoms may need professional evaluation. Please consult a licensed doctor or visit a nearby clinic.\n\n";
    $response .= "---\n";
    $response .= "**Tip:** Try describing your symptoms in more detail (e.g. 'fever for 2 days', 'stomach pain after eating').";
    echo json_encode(['response' => $response, 'severity' => 'unknown']);
    exit;
}

$response = "**Symptom Analysis for:** *" . htmlspecialchars($symptomsText) . "*\n\n";

$response .= "---\n";
$response .= "**[!] DOCTOR IS ALWAYS PREFERRED**\n";
$response .= "The suggestions below are general OTC (over-the-counter) information only. A qualified doctor should always be your first choice for any health concern. Self-medication can be dangerous.\n";
$response .= "---\n\n";

if (!empty($matched)) {
    $response .= "## General Medicine Guide\n\n";
    $num = 1;
    foreach ($matched as $key => $entry) {
        $response .= "**{$num}. {$entry['generic']}**\n";
        $response .= "**Common BD brands:** " . implode(', ', $entry['brands']) . "\n";
        $response .= "**Typical dose:** " . $entry['dose'] . "\n";
        $response .= "**Usage tip:** " . $entry['usage'] . "\n";
        $response .= "**When to see a doctor:** " . $entry['warning'] . "\n\n";
        $num++;
    }
    $response .= "---\n\n";
}

if (!empty($dbMatches)) {
    $response .= "## From Medicine Database\n\n";
    foreach ($dbMatches as $match) {
        $genericName    = $match['generic_name'];
        $drugClass      = $match['drug_class'] ?? 'General';
        $indicationRaw  = strip_tags($match['indication_description'] ?? '');
        $indicationShort = (strlen($indicationRaw) > 150) ? substr($indicationRaw, 0, 150) . '...' : $indicationRaw;
        $brands         = $match['brand_examples'] ? array_slice(explode(', ', $match['brand_examples']), 0, 3) : [];
        $brandStr       = !empty($brands) ? implode(', ', $brands) : 'See pharmacy';
        $alreadyHas     = in_array(strtolower($genericName), $userMedGenerics);
        $statusNote     = $alreadyHas ? " *(You already have this in your profile)*" : '';

        $response .= "**{$genericName}** ({$drugClass}){$statusNote}\n";
        $response .= "*{$indicationShort}*\n";
        $response .= "**Example brands:** {$brandStr}\n\n";
    }
    $response .= "---\n\n";
}

$response .= "*This information is for general awareness only and does NOT replace professional medical advice. Always consult a licensed physician before taking any medication.*";

$overallSeverity = 'mild';
foreach ($matched as $m) {
    if (($m['severity'] ?? 'mild') === 'moderate') { $overallSeverity = 'moderate'; }
}

echo json_encode(['response' => trim($response), 'severity' => $overallSeverity]);
?>
