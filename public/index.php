<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\PageViewTracker;

$ip = client_ip();
try {
    PageViewTracker::record($ip, '/', $config['page_view_cooldown_minutes']);
} catch (\Throwable $e) {
    error_log('Page view tracking failed: ' . $e->getMessage());
    // Never let analytics failures break the page for a real visitor.
}

$success = flash('contact_success');
$error = flash('contact_error');
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$siteUrl = $config['app']['url'] !== '' ? $config['app']['url'] : (
    (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost')
);
$ogImage = $siteUrl . '/assets/img/riya-portrait.png';

$pageTitle = 'Riya Pradhan — Strategic HR Business Partner | Certified McHRBP';
$pageDescription = 'Riya Pradhan is a Certified HR Business Partner (McHRBP) with 7+ years building HR functions across tech, fintech, and logistics in Kathmandu, Nepal — strategic HR, organizational development, talent acquisition, and HR technology & analytics.';

$projects = [
    [
        'title' => 'Lead — HR & OD', 'client' => 'Upaya', 'year' => 'Feb 2026 – Present',
        'tags' => ['HR Strategy', 'Org Development'], 'spanClass' => 'rp-lg',
        'logoSrc' => '/assets/img/logo-upaya.png', 'logoAlt' => 'Upaya logo',
        'description' => "Currently leading HR & organizational development at Upaya, driving strategic people initiatives and cross-team collaboration using tools including Mattermost.",
        'metrics' => [['value' => '6 mos', 'label' => 'Tenure'], ['value' => 'Kathmandu', 'label' => 'Location'], ['value' => 'On-site', 'label' => 'Work Mode']],
    ],
    [
        'title' => 'Human Resources Manager', 'client' => 'YCO Solutions Pvt. Ltd.', 'year' => 'Feb 2025 – Feb 2026',
        'tags' => ['HR Operations', 'Compliance'], 'spanClass' => '',
        'logoSrc' => '/assets/img/logo-yco.png', 'logoAlt' => 'YCO Solutions logo',
        'description' => "Lead end-to-end HR operations, including recruitment, payroll management, performance evaluation, HR budgeting, and compliance.\n\nHandled employee grievances, supported strategic human resource planning, and led the development and implementation of HR policies.",
        'metrics' => [['value' => '1 yr 1 mo', 'label' => 'Tenure'], ['value' => 'Kathmandu', 'label' => 'Location'], ['value' => 'Contract', 'label' => 'Type']],
    ],
    [
        'title' => 'Assistant Manager — HR & OD', 'client' => 'IME Khalti Ltd.', 'year' => 'Mar 2024 – Dec 2024',
        'tags' => ['Talent Acquisition', 'Performance Mgmt'], 'spanClass' => '',
        'logoSrc' => '/assets/img/logo-imekhalti.png', 'logoAlt' => 'IME Khalti logo',
        'description' => "Oversaw the complete recruitment cycle, entailing job requisitions, candidate evaluation, offer negotiations, and contract preparations.\nOversaw and streamlined various HR processes, including attendance and leave approvals, payroll updates, salary adjustments, resignation approvals, exit interviews, smooth handovers, asset return, and clearance.\nFacilitated goal setting, continuous feedback sessions, performance reviews, and managed Performance Improvement Plans (PIPs) to enhance employee performance.\nSuccessfully planned and executed team-building events to promote employee involvement.\nPrepared and submitted comprehensive reports such as Monthly Operating Reports (MOR), MIS reports, attendance and leave reports, and recruitment tracking reports.\nEnhanced employer branding through internal event photography and college placement visits.\nConducted training needs analysis, prepared training calendars, organized internal and external training sessions, and collected and evaluated training feedback.\nConducted research, created, and communicated HR policies and forms, ensuring compliance with employment legislation and providing training on new systems.\nAddressed team member concerns, conducted satisfaction surveys, and facilitated one-on-one management discussions.\nOversee the implementation and maintenance of the HRIS system i.e. Nimble, to streamline HR processes and enhance data accuracy.",
        'metrics' => [['value' => '10 mos', 'label' => 'Tenure'], ['value' => 'Kathmandu', 'label' => 'Location'], ['value' => 'Full-time', 'label' => 'Type']],
    ],
    [
        'title' => 'HR & Operation Manager', 'client' => 'PortPro - Asia', 'year' => 'Jun 2023 – Mar 2024',
        'tags' => ['HR Strategy', 'People Analytics'], 'spanClass' => 'rp-lg',
        'logoSrc' => '/assets/img/logo-portpro.png', 'logoAlt' => 'PortPro Asia logo',
        'description' => "Develop and implement HR strategies aligned with the company's goals and objectives.\nCollaborate with the leadership team to anticipate HR needs and proactively address challenges.\nAnalyze HR metrics and trends to provide insights and make data-driven recommendations.\nOversee the full-cycle recruitment process, including sourcing, screening, interviewing, and selection of candidates.\nDesign and implement programs to enhance employee engagement, satisfaction, and retention.\nConduct regular employee surveys, analyze feedback, and recommend actions to improve the employee experience.\nDevelop and manage performance management processes, including goal setting, performance reviews, and feedback mechanisms.\nEnsure compliance with employment laws and regulations, updating policies and procedures as needed.\nOversee the implementation and maintenance of the HRIS system i.e Nimble to streamline processes and enhance data accuracy.",
        'metrics' => [['value' => '10 mos', 'label' => 'Tenure'], ['value' => 'Kathmandu', 'label' => 'Location'], ['value' => 'Contract', 'label' => 'Type']],
    ],
    [
        'title' => 'Sr. HR & Finance Officer', 'client' => 'Bottle', 'year' => 'Dec 2019 – Jun 2023',
        'tags' => ['HR & Finance', 'Compliance'], 'spanClass' => '',
        'logoSrc' => '/assets/img/logo-bottle.png', 'logoAlt' => 'Bottle logo',
        'description' => "Human Resource:\n\nOversaw HR functions, including recruitment, onboarding, employee engagement, performance management, and employee relations.\nDeveloped and implemented HR policies and procedures aligned with organizational goals and best practices.\nEnsured compliance with labor laws, regulations, and internal policies.\nManaged employee payroll and benefits administration, ensuring accurate and timely processing of payroll and maintaining employee records.\nCoordinated and conducted training and development programs to enhance employee skills and knowledge.\nFostered a positive work environment, promoting employee well-being and satisfaction.\nHandled employee grievances and disciplinary issues promptly and fairly.\nMaintained HR documentation and records, including personnel files, contracts, and HR-related correspondence.\n\nFinance:\nEnsured the receipt and booking of expenses and income bills/invoices into the accounting software, adhering to the established Chart of Accounts.\nCreated and implemented financial policies to enhance operational efficiency and ensure compliance with regulations.\nPrepared Quarterly Cash Management reports based on projected cash inflow and outflow for OpeX and CapeX.\nMaintained an up-to-date project management report, monitored project completion percentage, and prepared variance reports based on project budget, completion, and actual expenses.\nEffectively managed contracts and Memorandum of Understanding (MOU) for expenses and income.\nDeveloped and maintained a fixed assets register, ensuring regular updates and verification.\nPrepared accurate monthly financial statements, including Income Statements, Balance Sheets, and Cash Flow Statements with relevant schedules.\nManaged payables to ensure timely payments based on the aging policy, while proactively following up on receivables for timely collection.",
        'metrics' => [['value' => '3 yrs 7 mos', 'label' => 'Tenure'], ['value' => 'Kathmandu', 'label' => 'Location'], ['value' => 'Full-time', 'label' => 'Type']],
    ],
    [
        'title' => 'Audit Associate', 'client' => 'Nirmal Associates, CA', 'year' => 'Jul 2018 – Sep 2019',
        'tags' => ['Finance', 'Audit'], 'spanClass' => '',
        'logoSrc' => null, 'logoInitials' => 'NA',
        'description' => "Supported the accounting and auditing team in their daily functions.\nPlanned and performed company financial audits.\nCreated and managed internal auditing systems for organizations.\nPrepared audit reports and statements for company managers.\nResolved client audit queries efficiently.\nEnsuring compliance with state and company best practices.",
        'metrics' => [['value' => '1 yr 3 mos', 'label' => 'Tenure'], ['value' => 'Kathmandu', 'label' => 'Location'], ['value' => 'Full-time', 'label' => 'Type']],
    ],
];

$skills = [
    ['category' => 'HR Strategy & Talent', 'items' => ['Strategic Human Resource Planning', 'Global Talent Acquisition', 'Full Life Cycle Recruiting', 'Talent Sourcing', 'Resume Screening', 'Employee Grievance', 'Employee Relations', 'Leadership Development']],
    ['category' => 'Compensation & Compliance', 'items' => ['Payroll Administration', 'Payroll Management', 'Compensation & Benefits', 'Employee Benefits Design', 'Statutory Compliances', 'Labor and Employment Law', 'Strategic Policy Development']],
    ['category' => 'HR Technology & Analytics', 'items' => ['HRIS Database Management', 'Human Resources Information Systems (HRIS)', 'Mattermost', 'Microsoft Power BI', 'HR Metrics', 'HR Operations']],
    ['category' => 'Finance & Operations', 'items' => ['Financial Audits', 'Financial Reporting', 'Financial Analysis', 'Account Reconciliation', 'Treasury Management', 'QuickBooks', 'Odoo', 'ERP Software', 'Accounting', 'Advanced Excel']],
    ['category' => 'Leadership & Delivery', 'items' => ['Performance Management', 'Training and Development (HR)', 'Operational Risk Management', 'Project Management', 'Cross-functional Team Leadership', 'Design Thinking', 'Office Administration']],
];

$timeline = [
    ['period' => 'Feb 2026 — Present', 'role' => 'Lead — HR & OD', 'org' => 'Upaya'],
    ['period' => 'Feb 2025 — Feb 2026', 'role' => 'Human Resources Manager', 'org' => 'YCO Solutions Pvt. Ltd.'],
    ['period' => 'Mar 2024 — Dec 2024', 'role' => 'Assistant Manager — HR & OD', 'org' => 'IME Khalti Ltd.'],
    ['period' => 'Jun 2023 — Mar 2024', 'role' => 'HR & Operation Manager', 'org' => 'PortPro - Asia'],
    ['period' => 'Dec 2019 — Jun 2023', 'role' => 'Sr. HR & Finance Officer', 'org' => 'Bottle'],
    ['period' => 'Jul 2018 — Sep 2019', 'role' => 'Audit Associate', 'org' => 'Nirmal Associates, CA'],
    ['period' => 'MBA', 'role' => 'Human Resources Management', 'org' => 'Ace Institute of Management'],
    ['period' => "Bachelor's", 'role' => 'Accounting and Business/Management', 'org' => 'Tribhuvan University'],
];

$contactEmail = 'reeya.pradhan16@gmail.com';
$linkedinUrl = 'https://www.linkedin.com/in/riya-p-363191191/';

$allSkillNames = [];
foreach ($skills as $g) {
    $allSkillNames = array_merge($allSkillNames, $g['items']);
}

$jsProjects = array_map(function ($p) use ($siteUrl) {
    return [
        'title' => $p['title'],
        'client' => $p['client'],
        'year' => $p['year'],
        'tags' => $p['tags'],
        'description' => $p['description'],
        'metrics' => $p['metrics'],
        'logoSrc' => $p['logoSrc'] ?? null,
        'logoInitials' => $p['logoInitials'] ?? null,
    ];
}, $projects);
$projectsJson = str_replace('</', '<\/', json_encode($jsProjects, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

$personLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => 'Riya Pradhan',
    'jobTitle' => 'Strategic HR Business Partner',
    'url' => $siteUrl . '/',
    'image' => $ogImage,
    'email' => 'mailto:' . $contactEmail,
    'sameAs' => [$linkedinUrl],
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Kathmandu', 'addressCountry' => 'NP'],
    'worksFor' => ['@type' => 'Organization', 'name' => 'Upaya'],
    'alumniOf' => [
        ['@type' => 'CollegeOrUniversity', 'name' => 'Ace Institute of Management'],
        ['@type' => 'CollegeOrUniversity', 'name' => 'Tribhuvan University'],
    ],
    'knowsAbout' => $allSkillNames,
    'hasCredential' => [
        '@type' => 'EducationalOccupationalCredential',
        'credentialCategory' => 'certification',
        'name' => 'Certified HR Business Partner (McHRBP)',
        'recognizedBy' => ['@type' => 'Organization', 'name' => 'World Academy — UK'],
    ],
];
$personLdJson = str_replace('</', '<\/', json_encode($personLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= e($siteUrl) ?>/">
<meta name="theme-color" content="#0a0a0a">

<meta property="og:type" content="profile">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:url" content="<?= e($siteUrl) ?>/">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:site_name" content="Riya Pradhan">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($pageTitle) ?>">
<meta name="twitter:description" content="<?= e($pageDescription) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">

<link rel="stylesheet" href="<?= e(asset_url('/assets/css/style.css')) ?>">
<script type="application/ld+json"><?= $personLdJson ?></script>
</head>
<body>

<canvas id="rp-firefly" class="rp-firefly" aria-hidden="true"></canvas>

<!-- NAV -->
<nav class="rp-nav" id="rp-nav" aria-label="Primary">
    <a href="#top" class="rp-logo" aria-label="Riya Pradhan — home">RP<span>.</span></a>
    <div class="rp-mascot" id="rp-mascot" aria-hidden="true">
        <svg class="rp-mascot-head" width="52" height="52" viewBox="0 0 100 100">
            <path d="M22 34 L18 12 L40 24 Z" fill="#1a1a1a" stroke="#ccff00" stroke-width="3" stroke-linejoin="round"></path>
            <path d="M78 34 L82 12 L60 24 Z" fill="#1a1a1a" stroke="#ccff00" stroke-width="3" stroke-linejoin="round"></path>
            <ellipse cx="50" cy="56" rx="33" ry="29" fill="#141414" stroke="#ccff00" stroke-width="3"></ellipse>
            <g class="rp-mascot-eyes">
                <ellipse class="rp-eye-l" cx="37" cy="52" rx="7.5" ry="8.5" fill="#ccff00"></ellipse>
                <ellipse class="rp-eye-r" cx="63" cy="52" rx="7.5" ry="8.5" fill="#ccff00"></ellipse>
                <circle class="rp-pupil-l" cx="37" cy="52" r="3.4" fill="#0a0a0a"></circle>
                <circle class="rp-pupil-r" cx="63" cy="52" r="3.4" fill="#0a0a0a"></circle>
            </g>
            <path d="M46 66 L50 70 L54 66" fill="none" stroke="#ccff00" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
            <g stroke="rgba(204,255,0,0.45)" stroke-width="2" stroke-linecap="round">
                <line x1="16" y1="62" x2="2" y2="58"></line>
                <line x1="16" y1="68" x2="3" y2="70"></line>
                <line x1="84" y1="62" x2="98" y2="58"></line>
                <line x1="84" y1="68" x2="97" y2="70"></line>
            </g>
        </svg>
    </div>
    <div class="rp-nav-links">
        <a href="#work">Work</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>
        <a href="<?= e($linkedinUrl) ?>" target="_blank" rel="noopener">LinkedIn</a>
    </div>
</nav>

<!-- HERO -->
<header id="top" class="rp-hero">
    <canvas id="rp-hero-flies" class="rp-hero-flies" aria-hidden="true"></canvas>
    <div class="rp-wrap rp-hero-grid">
        <div class="rp-hero-left">
            <div class="rp-hero-pill" data-reveal>
                <span class="dot"></span>
                Open to strategic HR &amp; OD conversations
            </div>
            <h1 data-reveal>Strategic HR<br><span>Business Partner</span>.</h1>
            <p class="rp-hero-sub" data-reveal>
                RIYA PRADHAN — Certified HR Business Partner (McHRBP). 7+ years building HR functions across tech, fintech, and logistics that create measurable business impact — not just efficient process.
            </p>
            <a href="#work" id="rp-cta" class="rp-hero-cta" data-reveal>
                View Career Highlights
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0a0a0a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
            </a>
        </div>
        <div class="rp-hero-img" data-reveal>
            <img src="/assets/img/riya-portrait.png" alt="Riya Pradhan, Strategic HR Business Partner" width="520" height="640">
        </div>
    </div>
</header>

<!-- TICKER -->
<div class="rp-ticker" aria-hidden="true">
    <div class="rp-ticker-track">
        <span>STRATEGIC HR — ORGANIZATIONAL DEVELOPMENT — TALENT ACQUISITION — HR TECHNOLOGY &amp; ANALYTICS — COMPLIANCE &amp; GOVERNANCE — PEOPLE-FIRST STRATEGY — STRATEGIC HR — ORGANIZATIONAL DEVELOPMENT — TALENT ACQUISITION — HR TECHNOLOGY &amp; ANALYTICS — COMPLIANCE &amp; GOVERNANCE — PEOPLE-FIRST STRATEGY —&nbsp;</span>
        <span>STRATEGIC HR — ORGANIZATIONAL DEVELOPMENT — TALENT ACQUISITION — HR TECHNOLOGY &amp; ANALYTICS — COMPLIANCE &amp; GOVERNANCE — PEOPLE-FIRST STRATEGY — STRATEGIC HR — ORGANIZATIONAL DEVELOPMENT — TALENT ACQUISITION — HR TECHNOLOGY &amp; ANALYTICS — COMPLIANCE &amp; GOVERNANCE — PEOPLE-FIRST STRATEGY —&nbsp;</span>
    </div>
</div>

<main>
<!-- WORK -->
<section id="work" class="rp-wrap" style="padding:120px 40px 40px">
    <div class="rp-sec-header" data-reveal>
        <span class="num">002</span>
        <h2>Career Highlights</h2>
    </div>
    <div class="rp-work-grid">
        <?php foreach ($projects as $i => $p): ?>
        <div class="<?= e($p['spanClass']) ?>" data-reveal>
            <button type="button" class="rp-card rp-proj-card" data-project-index="<?= $i ?>">
                <span class="top-line"></span>
                <div class="rp-card-body">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px">
                        <span class="rp-proj-logo">
                            <?php if (!empty($p['logoSrc'])): ?>
                                <img src="<?= e($p['logoSrc']) ?>" alt="<?= e($p['logoAlt'] ?? '') ?>">
                            <?php else: ?>
                                <span class="rp-proj-initials"><?= e($p['logoInitials']) ?></span>
                            <?php endif; ?>
                        </span>
                        <span style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#5f5f5f;letter-spacing:0.08em;padding-top:6px"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                    </div>
                    <div class="rp-tags">
                        <?php foreach ($p['tags'] as $t): ?>
                            <span class="rp-tag"><?= e($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <h3><?= e($p['title']) ?></h3>
                    <div class="rp-proj-meta"><?= e($p['client']) ?> — <?= e($p['year']) ?></div>
                    <div class="rp-proj-view">
                        View details
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#ccff00" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                    </div>
                </div>
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- SKILLS -->
<section id="about" class="rp-wrap" style="padding:120px 40px">
    <div class="rp-sec-header" data-reveal>
        <span class="num">003</span>
        <h2>Skills &amp; Capabilities</h2>
    </div>
    <div class="rp-skills-grid">
        <?php foreach ($skills as $g): ?>
        <div data-reveal>
            <div class="rp-card rp-skill-card">
                <div class="rp-card-body">
                    <div class="rp-skill-head">
                        <span class="cat"><?= e($g['category']) ?></span>
                        <span class="count"><?= e(str_pad((string) count($g['items']), 2, '0', STR_PAD_LEFT)) ?></span>
                    </div>
                    <div class="rp-skill-items">
                        <?php foreach ($g['items'] as $s): ?>
                            <span class="rp-skill-item"><?= e($s) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- TIMELINE -->
<section class="rp-wrap" style="padding:0 40px 120px" aria-labelledby="rp-timeline-heading">
    <div style="display:flex;align-items:baseline;gap:16px;margin-bottom:64px">
        <span class="num" data-reveal style="font-family:'JetBrains Mono',monospace;color:#ccff00;font-size:13px">004</span>
        <h2 id="rp-timeline-heading" style="font-size:clamp(2.2rem,5vw,3.6rem);font-weight:800;letter-spacing:-0.02em;margin:0">Full Timeline</h2>
    </div>
    <div class="rp-timeline-wrap">
        <div class="rp-tl-track">
            <div class="rp-tl-progress" style="height:0%"></div>
        </div>
        <?php foreach ($timeline as $i => $tl): $left = $i % 2 === 0; ?>
        <div class="rp-tl-row">
            <div class="rp-tl-dot"></div>
            <div class="rp-tl-year" style="grid-column:<?= $left ? 2 : 1 ?>;text-align:<?= $left ? 'left' : 'right' ?>"><?= e($tl['period']) ?></div>
            <div class="rp-tl-card" style="grid-column:<?= $left ? 1 : 2 ?>;text-align:<?= $left ? 'right' : 'left' ?>">
                <div class="rp-card rp-tl-card-inner">
                    <div class="rp-card-body">
                        <div class="role"><?= e($tl['role']) ?></div>
                        <div class="org"><?= e($tl['org']) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CERTIFICATION -->
<section class="rp-wrap" style="padding:0 40px 120px" aria-labelledby="rp-cert-heading">
    <div style="display:flex;align-items:baseline;gap:16px;margin-bottom:44px">
        <span class="num" data-reveal style="font-family:'JetBrains Mono',monospace;color:#ccff00;font-size:13px">005</span>
        <h2 id="rp-cert-heading" style="font-size:clamp(2.2rem,5vw,3.6rem);font-weight:800;letter-spacing:-0.02em;margin:0">Certification</h2>
    </div>
    <div class="rp-card rp-cert-grid" data-reveal>
        <div class="rp-card-body rp-cert-img">
            <img src="/assets/img/cert-mchrbp.png" alt="Certified HR Business Partner (McHRBP) certificate" loading="lazy">
        </div>
        <div class="rp-card-body rp-cert-meta">
            <div class="issued">ISSUED JUL 2026 — CREDENTIAL ID 212635</div>
            <div class="title">Certified HR Business Partner (McHRBP)</div>
            <div class="org">World Academy — UK</div>
        </div>
    </div>
</section>

<!-- CONTACT -->
<section id="contact" class="rp-wrap" style="padding:100px 40px 60px;border-top:1px solid rgba(245,245,247,0.12)">
    <div class="rp-sec-header" data-reveal>
        <span class="num">006</span>
        <h2>Get in Touch</h2>
    </div>

    <?php if ($success): ?>
        <p class="alert alert-success" style="margin-bottom:24px"><?= e($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="alert alert-error" style="margin-bottom:24px"><?= e($error) ?></p>
    <?php endif; ?>

    <div class="rp-contact-grid">
        <div class="rp-contact-left" data-reveal-x="left">
            <p>Open to conversations about HR transformation, organizational development, HR technology, and people analytics.</p>

            <button type="button" class="rp-card rp-contact-item" id="rp-copy-email" data-email="<?= e($contactEmail) ?>">
                <span class="rp-card-body icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ccff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m2 7 10 6 10-6"></path></svg>
                </span>
                <span class="rp-card-body" style="min-width:0">
                    <span class="label rp-copy-label">Email — Click to copy</span>
                    <span class="value"><?= e($contactEmail) ?></span>
                </span>
            </button>

            <a class="rp-card rp-contact-item linkedin-link" href="<?= e($linkedinUrl) ?>" target="_blank" rel="noopener">
                <span class="rp-card-body icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ccff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                </span>
                <span class="rp-card-body" style="min-width:0;flex:1">
                    <span class="label">LinkedIn</span>
                    <span class="value">linkedin.com/in/riya-p</span>
                </span>
                <svg class="rp-card-body arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ccff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
            </a>
        </div>

        <div class="rp-card rp-contact-form-card" data-reveal-x="right">
            <form class="rp-card-body rp-form" id="rp-contact-form" method="post" action="/contact">
                <?= csrf_field() ?>
                <div class="hp-field" aria-hidden="true">
                    <label for="hp_check">Leave this field blank</label>
                    <input type="text" id="hp_check" name="hp_check" tabindex="-1" autocomplete="off">
                </div>

                <div class="rp-form-row">
                    <label>
                        <span>Name</span>
                        <input class="rp-input" name="name" type="text" required maxlength="150" placeholder="Your name" value="<?= e($old['name'] ?? '') ?>">
                    </label>
                    <label>
                        <span>Email</span>
                        <input class="rp-input" name="email" type="email" required maxlength="190" placeholder="you@company.com" value="<?= e($old['email'] ?? '') ?>">
                    </label>
                </div>
                <label>
                    <span>Subject</span>
                    <input class="rp-input" name="subject" type="text" maxlength="255" placeholder="What is this about?" value="<?= e($old['subject'] ?? '') ?>">
                </label>
                <label>
                    <span>Message</span>
                    <textarea class="rp-input" name="message" rows="5" required placeholder="Tell me a little more…"><?= e($old['message'] ?? '') ?></textarea>
                </label>
                <button type="submit" class="rp-submit">
                    Send Message
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0a0a0a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            </form>

            <div class="rp-card-body rp-form-state" id="rp-form-sending" style="display:none">
                <span class="rp-spinner"></span>
                <span class="status-text">Sending your message…</span>
            </div>

            <div class="rp-card-body rp-form-state rp-form-sent" id="rp-form-sent" style="display:none">
                <span class="check">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#ccff00" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </span>
                <div class="headline">Message received</div>
                <p></p>
                <button type="button" class="rp-again-btn" id="rp-form-again">Send another</button>
            </div>
        </div>
    </div>
</section>
</main>

<footer class="rp-footer">
    <div class="rp-footer-inner">
        <span>© <?= date('Y') ?> RIYA PRADHAN. ALL RIGHTS RESERVED.</span>
        <span>Created By — <a href="https://joshibipin.com.np" target="_blank" rel="noopener">Bipin Joshi</a></span>
    </div>
</footer>

<!-- CASE STUDY MODAL -->
<div id="rp-modal-overlay" class="rp-modal-overlay">
    <div class="rp-modal">
        <div id="rp-modal-body"></div>
    </div>
</div>

<script>window.__PROJECTS__ = <?= $projectsJson ?>;</script>
<script src="<?= e(asset_url('/assets/js/main.js')) ?>"></script>
</body>
</html>
