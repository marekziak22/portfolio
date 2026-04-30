<?php
session_start();

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

if (!isset($_SESSION['users']['admin@studentjob.sk'])) {
    $_SESSION['users']['admin@studentjob.sk'] = [
        'name' => 'Hlavny admin',
        'email' => 'admin@studentjob.sk',
        'password_hash' => password_hash('Admin123', PASSWORD_DEFAULT),
        'role' => 'admin',
        'status' => 'approved',
        'phone' => '',
        'banned' => false,
    ];
}

if (!isset($_SESSION['users']['moderator@studentjob.sk'])) {
    $_SESSION['users']['moderator@studentjob.sk'] = [
        'name' => 'Moderator',
        'email' => 'moderator@studentjob.sk',
        'password_hash' => password_hash('Moderator123', PASSWORD_DEFAULT),
        'role' => 'admin',
        'status' => 'approved',
        'phone' => '',
        'banned' => false,
    ];
}

if (!isset($_SESSION['jobs'])) {
    $_SESSION['jobs'] = [
        [
            'id' => 1,
            'title' => 'Barista / študentská výpomoc',
            'company' => 'Kaviareň Mlyn',
            'location' => 'Bratislava - Staré Mesto',
            'pay' => '7,50 € / hod.',
            'type' => 'Po škole',
            'description' => 'Hľadáme spoľahlivého študenta na prípravu kávy, obsluhu hostí a jednoduchú prácu s pokladňou. Skúsenosti potešia, všetko dôležité ťa naučíme.',
            'views' => 126,
            'applications' => 18,
            'tags' => ['gastro', 'flexibilné', 'večery'],
            'created_by' => 'firma@demo.sk',
        ],
        [
            'id' => 2,
            'title' => 'Junior asistent marketingu',
            'company' => 'BrightLab s.r.o.',
            'location' => 'Hybrid / Bratislava',
            'pay' => '9 € / hod.',
            'type' => 'Hybrid',
            'description' => 'Pomôžeš so sociálnymi sieťami, krátkymi textami, prieskumom trhu a prípravou kampaní. Vhodné pre kreatívneho študenta, ktorý chce nazbierať prax.',
            'views' => 312,
            'applications' => 41,
            'tags' => ['marketing', 'remote', 'kreatíva'],
            'created_by' => 'firma@demo.sk',
        ],
        [
            'id' => 3,
            'title' => 'Doučovanie matematiky pre žiakov ZŠ',
            'company' => 'EduPoint',
            'location' => 'Košice',
            'pay' => '12 € / hod.',
            'type' => 'Flexibilné',
            'description' => 'Doučovanie žiakov základnej školy 2 až 3 popoludnia týždenne. Ideálne pre študentov pedagogiky, prírodných vied alebo technických odborov.',
            'views' => 89,
            'applications' => 9,
            'tags' => ['vzdelávanie', 'flexibilné', 'matematika'],
            'created_by' => 'firma@demo.sk',
        ],
    ];
}

if (!isset($_SESSION['chats'])) {
    $_SESSION['chats'] = [];
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect_home($params = []) {
    $url = 'index.php';
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
}

function find_job($jobId) {
    foreach ($_SESSION['jobs'] as $job) {
        if ((int) $job['id'] === (int) $jobId) {
            return $job;
        }
    }
    return null;
}

function chat_belongs_to_user($chat, $user) {
    if (!$user) return false;
    if ($user['role'] === 'admin') return true;
    if ($user['role'] === 'student') return $chat['student_email'] === $user['email'];
    return $chat['company_email'] === $user['email'] || $chat['company_name'] === $user['name'];
}

function get_user_chats($user) {
    if (!$user) return [];
    $items = [];
    foreach ($_SESSION['chats'] as $chat) {
        if (chat_belongs_to_user($chat, $user)) {
            $items[] = $chat;
        }
    }
    return $items;
}

function get_selected_chat($chats, $id) {
    foreach ($chats as $chat) {
        if ((string) $chat['id'] === (string) $id) return $chat;
    }
    return count($chats) ? $chats[0] : null;
}

function current_user_is_admin($user) {
    return ($user['role'] ?? '') === 'admin';
}

function company_is_approved($email) {
    if ($email === '') return true;
    $company = $_SESSION['users'][$email] ?? null;
    if (!$company) return true;
    return ($company['role'] ?? '') !== 'firma' || (($company['status'] ?? 'approved') === 'approved' && empty($company['banned']));
}

$error = '';
$authMode = $_GET['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = mb_strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role = ($_POST['role'] ?? 'student') === 'firma' ? 'firma' : 'student';
        $phone = trim($_POST['phone'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $error = 'Doplň meno, e-mail a heslo.';
            $authMode = 'register';
        } elseif ($role === 'firma' && $phone === '') {
            $error = 'Pri registrácii firmy doplň aj telefónne číslo na overenie.';
            $authMode = 'register';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Zadaj platnú e-mailovú adresu.';
            $authMode = 'register';
        } elseif (strlen($password) < 6) {
            $error = 'Heslo musí mať aspoň 6 znakov.';
            $authMode = 'register';
        } elseif (isset($_SESSION['users'][$email])) {
            $error = 'Účet s týmto e-mailom už existuje.';
            $authMode = 'register';
        } else {
            $_SESSION['users'][$email] = [
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'phone' => $phone,
                'status' => $role === 'firma' ? 'pending' : 'approved',
                'banned' => false,
            ];
            if ($role === 'firma') {
                $error = 'Registráciu firmy sme prijali. Admin ju schváli po overení telefonicky.';
                $authMode = 'login';
            } else {
                $_SESSION['user'] = ['name' => $name, 'email' => $email, 'role' => $role];
                redirect_home();
            }
        }
    }

    if ($action === 'login') {
        $email = mb_strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $existingUser = $_SESSION['users'][$email] ?? null;

        if (!$existingUser || !password_verify($password, $existingUser['password_hash'])) {
            $error = 'E-mail alebo heslo nesedí.';
            $authMode = 'login';
        } elseif (!empty($existingUser['banned'])) {
            $error = 'Tento účet je zablokovaný administrátorom.';
            $authMode = 'login';
        } elseif (($existingUser['role'] ?? '') === 'firma' && ($existingUser['status'] ?? 'approved') !== 'approved') {
            $error = 'Firemný účet ešte čaká na schválenie administrátorom.';
            $authMode = 'login';
        } else {
            $_SESSION['user'] = [
                'name' => $existingUser['name'],
                'email' => $existingUser['email'],
                'role' => $existingUser['role'],
            ];
            redirect_home();
        }
    }

    if ($action === 'logout') {
        unset($_SESSION['user']);
        redirect_home();
    }

    $user = $_SESSION['user'] ?? null;

    if ($action === 'add_job' && (($user['role'] ?? '') === 'firma' || current_user_is_admin($user))) {
        $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
        $companyName = current_user_is_admin($user) ? trim($_POST['company'] ?? 'StudentJob Admin') : $user['name'];
        $createdBy = current_user_is_admin($user) ? $user['email'] : $user['email'];
        $_SESSION['jobs'][] = [
            'id' => time(),
            'title' => trim($_POST['title'] ?? ''),
            'company' => $companyName === '' ? 'StudentJob Admin' : $companyName,
            'location' => trim($_POST['location'] ?? ''),
            'pay' => trim($_POST['pay'] ?? ''),
            'type' => trim($_POST['type'] ?? 'Flexibilné'),
            'description' => trim($_POST['description'] ?? ''),
            'views' => 0,
            'applications' => 0,
            'tags' => $tags,
            'created_by' => $createdBy,
        ];
        redirect_home(current_user_is_admin($user) ? ['view' => 'admin'] : []);
    }

    if (current_user_is_admin($user)) {
        if (in_array($action, ['approve_company', 'reject_company', 'ban_user', 'unban_user'], true)) {
            $email = mb_strtolower(trim($_POST['email'] ?? ''));
            if (isset($_SESSION['users'][$email]) && ($_SESSION['users'][$email]['role'] ?? '') !== 'admin') {
                if ($action === 'approve_company') $_SESSION['users'][$email]['status'] = 'approved';
                if ($action === 'reject_company') $_SESSION['users'][$email]['status'] = 'rejected';
                if ($action === 'ban_user') $_SESSION['users'][$email]['banned'] = true;
                if ($action === 'unban_user') $_SESSION['users'][$email]['banned'] = false;
            }
            redirect_home(['view' => 'admin']);
        }

        if ($action === 'delete_job') {
            $jobId = (int) ($_POST['job_id'] ?? 0);
            $_SESSION['jobs'] = array_values(array_filter($_SESSION['jobs'], fn($job) => (int)$job['id'] !== $jobId));
            $_SESSION['chats'] = array_values(array_filter($_SESSION['chats'], fn($chat) => (int)$chat['job_id'] !== $jobId));
            redirect_home(['view' => 'admin']);
        }

        if ($action === 'delete_chat') {
            $chatId = $_POST['chat_id'] ?? '';
            $_SESSION['chats'] = array_values(array_filter($_SESSION['chats'], fn($chat) => (string)$chat['id'] !== (string)$chatId));
            redirect_home(['view' => 'admin']);
        }
    }

    if ($action === 'send_application' && ($user['role'] ?? '') === 'student') {
        $jobId = (int) ($_POST['job_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $cv = trim($_POST['cv'] ?? '');
        $job = find_job($jobId);

        if ($job && company_is_approved($job['created_by'] ?? '') && $message !== '') {
            $exists = false;
            foreach ($_SESSION['chats'] as $chat) {
                if ((int) $chat['job_id'] === $jobId && $chat['student_email'] === $user['email']) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $chatId = time() . rand(100, 999);
                $_SESSION['chats'][] = [
                    'id' => $chatId,
                    'job_id' => $jobId,
                    'job_title' => $job['title'],
                    'company_name' => $job['company'],
                    'company_email' => $job['created_by'] ?? '',
                    'student_name' => $user['name'],
                    'student_email' => $user['email'],
                    'messages' => [
                        [
                            'author_name' => $user['name'],
                            'author_email' => $user['email'],
                            'author_role' => 'student',
                            'text' => $message,
                            'cv' => $cv,
                            'time' => date('d.m.Y H:i'),
                        ]
                    ],
                ];

                foreach ($_SESSION['jobs'] as &$sessionJob) {
                    if ((int) $sessionJob['id'] === $jobId) {
                        $sessionJob['applications']++;
                        break;
                    }
                }
                unset($sessionJob);

                redirect_home(['view' => 'chat', 'chat' => $chatId]);
            }
        }
        redirect_home(['job' => $jobId]);
    }

    if ($action === 'send_message' && $user) {
        $chatId = $_POST['chat_id'] ?? '';
        $text = trim($_POST['text'] ?? '');
        if ($text !== '') {
            foreach ($_SESSION['chats'] as &$chat) {
                if ((string) $chat['id'] === (string) $chatId && chat_belongs_to_user($chat, $user)) {
                    $chat['messages'][] = [
                        'author_name' => $user['name'],
                        'author_email' => $user['email'],
                        'author_role' => $user['role'],
                        'text' => $text,
                        'cv' => '',
                        'time' => date('d.m.Y H:i'),
                    ];
                    break;
                }
            }
            unset($chat);
        }
        redirect_home(['view' => 'chat', 'chat' => $chatId]);
    }
}

$user = $_SESSION['user'] ?? null;
$selectedJobId = isset($_GET['job']) ? (int) $_GET['job'] : null;
$selectedChatId = $_GET['chat'] ?? null;
$showChat = ($_GET['view'] ?? '') === 'chat';
$showAdmin = ($_GET['view'] ?? '') === 'admin' && current_user_is_admin($user);
$query = trim($_GET['q'] ?? '');

if ($selectedJobId) {
    foreach ($_SESSION['jobs'] as &$job) {
        if ((int) $job['id'] === $selectedJobId && empty($_SESSION['viewed_' . $selectedJobId])) {
            $job['views']++;
            $_SESSION['viewed_' . $selectedJobId] = true;
            break;
        }
    }
    unset($job);
}

$jobs = current_user_is_admin($user) ? $_SESSION['jobs'] : array_values(array_filter($_SESSION['jobs'], fn($job) => company_is_approved($job['created_by'] ?? '')));
$filteredJobs = array_filter($jobs, function ($job) use ($query) {
    if ($query === '') return true;
    $text = mb_strtolower($job['title'] . ' ' . $job['company'] . ' ' . $job['location'] . ' ' . $job['description'] . ' ' . implode(' ', $job['tags']));
    return str_contains($text, mb_strtolower($query));
});

$selectedJob = $selectedJobId ? find_job($selectedJobId) : null;
if ($selectedJob && !current_user_is_admin($user) && !company_is_approved($selectedJob['created_by'] ?? '')) {
    $selectedJob = null;
}
$userChats = get_user_chats($user);
$selectedChat = get_selected_chat($userChats, $selectedChatId);

$allUsers = $_SESSION['users'];
$pendingCompanies = array_filter($allUsers, fn($item) => ($item['role'] ?? '') === 'firma' && ($item['status'] ?? 'approved') === 'pending');
$bannedUsers = array_filter($allUsers, fn($item) => !empty($item['banned']));

$notifications = [];
foreach ($userChats as $chat) {
    $last = end($chat['messages']);
    if ($last && $last['author_role'] !== ($user['role'] ?? '')) {
        $notifications[] = $chat;
    }
}

$totalViews = array_sum(array_column($jobs, 'views'));
$totalApplications = array_sum(array_column($jobs, 'applications'));
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentJob - overené brigády pre študentov</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #f7f8f5;
            --bg-soft: #e9f3f0;
            --card: rgba(255, 255, 255, .88);
            --card-solid: #ffffff;
            --text: #151a22;
            --muted: #687386;
            --line: rgba(21, 26, 34, .11);
            --primary: #0f766e;
            --primary-2: #2563eb;
            --accent: #f59e0b;
            --accent-2: #ef476f;
            --danger: #e11d48;
            --shadow: 0 28px 80px rgba(21, 26, 34, .13);
            --small-shadow: 0 14px 34px rgba(21, 26, 34, .09);
            --input: #ffffff;
            --chip: #e7f4f1;
            --chip-text: #0f766e;
            --hero-grad:
                linear-gradient(135deg, rgba(15, 118, 110, .13), transparent 34%),
                linear-gradient(315deg, rgba(239, 71, 111, .10), transparent 34%),
                linear-gradient(rgba(21, 26, 34, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(21, 26, 34, .035) 1px, transparent 1px),
                #f7f8f5;
            --radius: 8px;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0c1017;
                --bg-soft: #121a24;
                --card: rgba(18, 24, 33, .86);
                --card-solid: #121821;
                --text: #edf2f7;
                --muted: #9aa7b7;
                --line: rgba(226, 232, 240, .12);
                --primary: #2dd4bf;
                --primary-2: #60a5fa;
                --accent: #fbbf24;
                --accent-2: #fb7185;
                --danger: #fb7185;
                --shadow: 0 28px 90px rgba(0, 0, 0, .42);
                --small-shadow: 0 14px 34px rgba(0, 0, 0, .30);
                --input: rgba(18, 24, 33, .92);
                --chip: rgba(45, 212, 191, .14);
                --chip-text: #99f6e4;
                --hero-grad:
                    linear-gradient(135deg, rgba(45, 212, 191, .14), transparent 36%),
                    linear-gradient(315deg, rgba(251, 113, 133, .10), transparent 34%),
                    linear-gradient(rgba(226, 232, 240, .035) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(226, 232, 240, .035) 1px, transparent 1px),
                    #0c1017;
            }
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background: var(--bg);
            letter-spacing: 0;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            background: var(--hero-grad);
            background-size: auto, auto, 34px 34px, 34px 34px, auto;
        }
        a { color: inherit; text-decoration: none; }
        button, input, textarea, select { font: inherit; }
        button, .button {
            border: 0;
            cursor: pointer;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 9px;
            border-radius: 999px;
            padding: 13px 18px;
            font-weight: 850;
            letter-spacing: 0;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 14px 30px color-mix(in srgb, var(--primary) 25%, transparent);
            transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease, filter .18s ease;
        }
        button:hover, .button:hover { transform: translateY(-1px); box-shadow: var(--small-shadow); filter: saturate(1.06); }
        button:disabled { opacity: .65; cursor: not-allowed; transform: none; }
        input, textarea, select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 14px 16px;
            font-size: 15px;
            color: var(--text);
            background: var(--input);
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        input:focus, textarea:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary) 18%, transparent);
        }
        textarea { min-height: 120px; resize: vertical; }
        .muted { color: var(--muted); }
        .container { width: min(1220px, calc(100% - 32px)); margin: auto; padding: 28px 0 60px; }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--small-shadow);
            backdrop-filter: blur(20px);
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 13px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--chip) 86%, white);
            color: var(--chip-text);
            font-size: 13px;
            font-weight: 850;
            border: 1px solid color-mix(in srgb, var(--primary) 16%, transparent);
        }

        .login-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px;
        }
        .login-shell {
            width: min(1180px, 100%);
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 34px;
            align-items: center;
        }
        .hero-copy { padding: 24px 0; position: relative; }
        .hero-copy::after {
            content: "";
            display: block;
            width: 170px;
            height: 5px;
            margin-top: 28px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--primary), var(--accent), var(--accent-2));
        }
        .hero-copy h1 {
            margin: 20px 0 18px;
            max-width: 760px;
            font-size: clamp(44px, 7vw, 88px);
            line-height: .92;
            letter-spacing: 0;
        }
        .hero-copy h1 span {
            background: linear-gradient(135deg, var(--primary), var(--primary-2), var(--accent-2));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .lead {
            max-width: 620px;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.75;
        }
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-top: 30px;
            max-width: 620px;
        }
        .hero-stat {
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--card);
            backdrop-filter: blur(18px);
        }
        .hero-stat strong { display: block; font-size: 28px; letter-spacing: 0; }
        .hero-stat span { color: var(--muted); font-size: 14px; }
        .login-card { padding: 28px; border-radius: var(--radius); box-shadow: var(--shadow); }
        .login-card h2 { margin: 0; font-size: 28px; letter-spacing: 0; }
        .auth-tabs {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            padding: 6px;
            border-radius: 999px;
            background: rgba(100, 116, 139, .12);
            margin-bottom: 22px;
            overflow: hidden;
        }
        .auth-tabs::before {
            content: "";
            position: absolute;
            top: 6px;
            bottom: 6px;
            left: 6px;
            width: calc(50% - 6px);
            border-radius: 999px;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 10px 24px color-mix(in srgb, var(--primary) 24%, transparent);
            transition: transform .28s cubic-bezier(.22, 1, .36, 1);
        }
        .auth-tabs.register::before {
            transform: translateX(100%);
        }
        .auth-tab {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 12px;
            border-radius: 999px;
            color: var(--muted);
            font-weight: 850;
            transition: color .22s ease;
        }
        .auth-tab.active {
            color: white;
        }
        .form-stack { display: grid; gap: 13px; margin-top: 18px; }
        .field label { display: block; margin: 0 0 7px; color: var(--muted); font-size: 13px; font-weight: 800; }
        .role-grid {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            padding: 6px;
            border-radius: 999px;
            background: rgba(100, 116, 139, .12);
        }
        .role-grid::before {
            content: "";
            position: absolute;
            top: 6px;
            bottom: 6px;
            left: 6px;
            width: calc(50% - 6px);
            border-radius: 999px;
            background: color-mix(in srgb, var(--primary) 18%, transparent);
            border: 1px solid color-mix(in srgb, var(--primary) 34%, transparent);
            transition: transform .28s cubic-bezier(.22, 1, .36, 1);
        }
        .role-grid.firma::before {
            transform: translateX(100%);
        }
        .role {
            position: relative;
            z-index: 1;
            cursor: pointer;
            border: 0;
            border-radius: 999px;
            padding: 14px;
            background: transparent;
            color: var(--muted);
            text-align: center;
            font-weight: 850;
            transition: color .22s ease;
        }
        .role:has(input:checked) {
            color: var(--text);
        }
        .role input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .alert {
            margin: 14px 0;
            padding: 13px 15px;
            border-radius: var(--radius);
            color: #fff;
            background: linear-gradient(135deg, var(--danger), #f97316);
            font-weight: 850;
        }

        .header {
            position: sticky;
            top: 0;
            z-index: 30;
            border-bottom: 1px solid var(--line);
            background: color-mix(in srgb, var(--bg) 82%, transparent);
            backdrop-filter: blur(20px);
        }
        .nav {
            width: min(1220px, calc(100% - 32px));
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 0;
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: var(--radius);
            color: white;
            font-weight: 950;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 14px 30px color-mix(in srgb, var(--primary) 28%, transparent);
        }
        .brand-title { font-weight: 950; letter-spacing: 0; }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .button-light {
            color: var(--text);
            background: var(--card);
            border: 1px solid var(--line);
            box-shadow: none;
        }
        .notification-wrap { position: relative; }
        .notification-button { position: relative; }
        .notification-count {
            position: absolute;
            right: -6px;
            top: -6px;
            width: 22px;
            height: 22px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            color: white;
            background: var(--danger);
            font-size: 11px;
            font-weight: 950;
        }
        .notification-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 54px;
            width: 350px;
            padding: 13px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--card-solid);
            box-shadow: var(--shadow);
        }
        .notification-wrap:hover .notification-dropdown,
        .notification-wrap:focus-within .notification-dropdown { display: block; }
        .notice {
            display: block;
            margin-top: 9px;
            padding: 13px;
            border-radius: var(--radius);
            background: rgba(100, 116, 139, .08);
        }
        .notice:hover { background: var(--chip); }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .stat-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            border-radius: var(--radius);
            background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 18%, transparent), color-mix(in srgb, var(--accent) 20%, transparent));
            font-size: 23px;
        }
        .stat-value { font-size: 34px; font-weight: 950; letter-spacing: 0; }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 390px;
            gap: 24px;
            margin-top: 24px;
            align-items: start;
        }
        .section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }
        .section-head h2 { margin: 0; font-size: 34px; letter-spacing: 0; }
        .search-box { position: relative; min-width: min(340px, 100%); }
        .search-box span { position: absolute; left: 15px; top: 13px; color: var(--muted); }
        .search-box input { padding-left: 44px; }
        .jobs-list { display: grid; gap: 14px; }
        .job-card {
            display: block;
            padding: 20px;
            border-radius: var(--radius);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            position: relative;
            overflow: hidden;
        }
        .job-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, var(--primary), var(--accent), var(--accent-2));
            opacity: .86;
        }
        .job-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
            border-color: color-mix(in srgb, var(--primary) 45%, var(--line));
        }
        .job-top { display: flex; justify-content: space-between; gap: 18px; }
        .job-card h3 { margin: 12px 0 5px; font-size: 24px; letter-spacing: 0; }
        .job-desc { color: var(--muted); line-height: 1.6; margin: 12px 0 0; }
        .tags { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag {
            display: inline-flex;
            border-radius: 999px;
            padding: 7px 10px;
            color: var(--muted);
            background: rgba(100, 116, 139, .10);
            font-size: 12px;
            font-weight: 850;
        }
        .tag-main { color: var(--chip-text); background: var(--chip); }
        .job-meta { display: flex; flex-wrap: wrap; gap: 13px; margin-top: 15px; color: var(--muted); font-size: 14px; }
        .pay { color: var(--accent); font-weight: 950; }
        .mini-stats {
            min-width: 152px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            align-self: start;
            padding: 10px;
            border-radius: var(--radius);
            background: rgba(100, 116, 139, .08);
            text-align: center;
        }
        .mini-stats strong { display: block; font-size: 20px; letter-spacing: 0; }
        .mini-stats small { color: var(--muted); font-size: 12px; }
        .side-panel { position: sticky; top: 96px; display: grid; gap: 16px; }
        .detail-card {
            color: white;
            background:
                linear-gradient(135deg, rgba(255,255,255,.08), transparent 38%),
                linear-gradient(145deg, #111827, #15332f 52%, #0f3f5f);
            border-color: rgba(255,255,255,.10);
        }
        .detail-card .muted { color: #cbd5e1; }
        .detail-card input, .detail-card textarea {
            color: white;
            background: rgba(255,255,255,.10);
            border-color: rgba(255,255,255,.14);
        }
        .detail-card input::placeholder, .detail-card textarea::placeholder { color: rgba(255,255,255,.62); }
        .detail-box {
            padding: 16px;
            border-radius: var(--radius);
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255,255,255,.10);
        }
        .form-grid { display: grid; gap: 12px; }
        .application-box { display: grid; gap: 11px; margin-top: 14px; }
        .empty { padding: 24px; text-align: center; color: var(--muted); }

        .chat-card { margin-top: 24px; padding: 0; overflow: hidden; }
        .chat-layout { display: grid; grid-template-columns: 330px 1fr; }
        .chat-list { padding: 18px; border-right: 1px solid var(--line); }
        .chat-list h2, .chat-main h2 { margin: 0 0 6px; letter-spacing: 0; }
        .chat-item {
            display: block;
            margin-top: 10px;
            padding: 14px;
            border-radius: var(--radius);
            background: rgba(100,116,139,.08);
        }
        .chat-item.active { background: var(--chip); box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--primary) 25%, transparent); }
        .chat-main { min-height: 460px; padding: 20px; display: flex; flex-direction: column; }
        .messages { flex: 1; display: grid; align-content: start; gap: 12px; padding: 20px 0; }
        .message-row { display: flex; }
        .message-row.mine { justify-content: flex-end; }
        .bubble {
            max-width: 78%;
            padding: 14px 16px;
            border-radius: var(--radius);
            background: rgba(100,116,139,.10);
            line-height: 1.55;
        }
        .mine .bubble { color: white; background: linear-gradient(135deg, var(--primary), var(--primary-2)); }
        .cv-box {
            margin-top: 10px;
            padding: 10px;
            border-radius: var(--radius);
            background: rgba(255, 255, 255, .20);
            font-size: 14px;
        }
        .reply-form { display: flex; gap: 10px; padding-top: 15px; border-top: 1px solid var(--line); }
        .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 24px; }
        .admin-wide { grid-column: 1 / -1; }
        .admin-list { display: grid; gap: 10px; margin-top: 14px; }
        .admin-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: rgba(100,116,139,.07);
        }
        .admin-actions { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
        .button-danger { background: linear-gradient(135deg, var(--danger), var(--accent-2)); }
        .button-ghost {
            color: var(--text);
            background: transparent;
            border: 1px solid var(--line);
            box-shadow: none;
        }
        .status {
            display: inline-flex;
            align-items: center;
            margin-left: 8px;
            padding: 4px 8px;
            border-radius: 999px;
            background: var(--chip);
            color: var(--chip-text);
            font-size: 12px;
            font-weight: 850;
        }
        .status.danger { color: white; background: var(--danger); }
        .status.pending { color: #7c2d12; background: #ffedd5; }
        .admin-login-note {
            display: grid;
            gap: 6px;
            margin-top: 16px;
            padding: 14px;
            border-radius: var(--radius);
            background: rgba(100,116,139,.08);
            color: var(--muted);
            font-size: 14px;
        }

        @media (max-width: 980px) {
            .login-shell, .layout, .chat-layout, .admin-grid { grid-template-columns: 1fr; }
            .side-panel { position: static; }
            .chat-list { border-right: 0; border-bottom: 1px solid var(--line); }
            .admin-wide { grid-column: auto; }
        }
        @media (max-width: 720px) {
            .login-page { padding: 18px; }
            .hero-stats, .dashboard-grid { grid-template-columns: 1fr; }
            .nav, .section-head, .job-top { flex-direction: column; align-items: stretch; }
            .nav-actions { flex-wrap: wrap; }
            .mini-stats { width: 100%; }
            .notification-dropdown { right: auto; left: 0; width: min(350px, 90vw); }
            .hero-copy h1 { font-size: 48px; }
            .reply-form { flex-direction: column; }
        }
    </style>
</head>
<body>
<?php if (!$user): ?>
    <main class="login-page">
        <section class="login-shell">
            <div class="hero-copy">
                <span class="pill">✨ Flexibilná práca popri škole</span>
                <h1>Nájdi brigádu, ktorá sedí tvojmu tempu.</h1>
                <p class="lead">Prehľadné ponuky, rýchle reakcie a chat s firmou na jednom mieste. Menej vybavovania, viac času na školu, prácu aj život.</p>
                <div class="hero-stats">
                    <div class="hero-stat"><strong>120+</strong><span>overených ponúk</span></div>
                    <div class="hero-stat"><strong>2 400+</strong><span>študentov v komunite</span></div>
                    <div class="hero-stat"><strong>950+</strong><span>začatých konverzácií</span></div>
                </div>
            </div>

            <div class="card login-card">
                <div class="auth-tabs <?= $authMode === 'register' ? 'register' : '' ?>">
                    <a class="auth-tab <?= $authMode !== 'register' ? 'active' : '' ?>" href="?mode=login" onclick="swipeAuth(event, 'login')">Prihlásenie</a>
                    <a class="auth-tab <?= $authMode === 'register' ? 'active' : '' ?>" href="?mode=register" onclick="swipeAuth(event, 'register')">Registrácia</a>
                </div>

                <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

                <?php if ($authMode === 'register'): ?>
                    <h2>Začni používať StudentJob</h2>
                    <p class="muted">Vyber typ účtu. Firmy pred zverejňovaním ponúk overuje administrátor.</p>
                    <form method="post" class="form-stack">
                        <input type="hidden" name="action" value="register">
                        <div class="field"><label>Meno alebo názov firmy</label><input name="name" placeholder="napr. Adam Novák / Kaviareň Mlyn" required></div>
                        <div class="field"><label>E-mail</label><input type="email" name="email" placeholder="napr. adam@email.sk" required></div>
                        <div class="field"><label>Heslo</label><input type="password" name="password" placeholder="minimálne 6 znakov" required minlength="6"></div>
                        <div class="field"><label>Telefón firmy</label><input name="phone" placeholder="povinný iba pre firemný účet"></div>
                        <div class="role-grid" id="roleSwipe">
                            <label class="role"><input type="radio" name="role" value="student" checked onchange="document.getElementById('roleSwipe').classList.remove('firma')"> 🎓 Študent</label>
                            <label class="role"><input type="radio" name="role" value="firma" onchange="document.getElementById('roleSwipe').classList.add('firma')"> 🏢 Firma</label>
                        </div>
                        <button>Vytvoriť účet</button>
                    </form>
                <?php else: ?>
                    <h2>Vitaj späť</h2>
                    <p class="muted">Prihlás sa a pokračuj tam, kde si naposledy skončil.</p>
                    <form method="post" class="form-stack">
                        <input type="hidden" name="action" value="login">
                        <div class="field"><label>E-mail</label><input type="email" name="email" placeholder="napr. adam@email.sk" required></div>
                        <div class="field"><label>Heslo</label><input type="password" name="password" placeholder="tvoje heslo" required></div>
                        <button>Pokračovať</button>
                    </form>
                    <div class="admin-login-note">
                        <strong>Demo administrátori</strong>
                        <span>admin@studentjob.sk / Admin123</span>
                        <span>moderator@studentjob.sk / Moderator123</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
<?php else: ?>
    <header class="header">
        <nav class="nav">
            <div class="brand">
                <div class="logo">SJ</div>
                <div>
                    <div class="brand-title">StudentJob</div>
                    <small class="muted"><?= e($user['name']) ?> · <?= $user['role'] === 'student' ? 'študent' : 'firma' ?></small>
                </div>
            </div>

            <div class="nav-actions">
                <?php if (current_user_is_admin($user)): ?>
                    <a class="button button-light" href="?view=admin">Administrácia</a>
                <?php endif; ?>
                <a class="button button-light" href="?view=chat">💬 Chat</a>
                <div class="notification-wrap">
                    <button class="button button-light notification-button" type="button">
                        🔔
                        <?php if (count($notifications) > 0): ?>
                            <span class="notification-count"><?= count($notifications) ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notification-dropdown">
                        <strong>Nové správy</strong>
                        <?php if (empty($userChats)): ?>
                            <p class="muted">Zatiaľ tu nemáš žiadne nové správy.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($userChats, 0, 6) as $chat): ?>
                                <?php $last = end($chat['messages']); ?>
                                <a class="notice" href="?view=chat&chat=<?= e($chat['id']) ?>">
                                    <strong><?= e($chat['job_title']) ?></strong><br>
                                    <small class="muted"><?= e($last['author_name']) ?>: <?= e(mb_substr($last['text'], 0, 70)) ?>...</small>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="logout">
                    <button class="button-light">Odhlásiť sa</button>
                </form>
            </div>
        </nav>
    </header>

    <main class="container">
        <section class="dashboard-grid">
            <div class="card stat-card"><div><span class="muted">Aktívne ponuky</span><div class="stat-value"><?= count($jobs) ?></div></div><div class="stat-icon">💼</div></div>
            <div class="card stat-card"><div><span class="muted">Zobrazenia ponúk</span><div class="stat-value"><?= $totalViews ?></div></div><div class="stat-icon">👀</div></div>
            <div class="card stat-card"><div><span class="muted">Reakcie študentov</span><div class="stat-value"><?= $totalApplications ?></div></div><div class="stat-icon">💬</div></div>
        </section>

        <?php if ($showAdmin): ?>
            <section class="admin-grid">
                <div class="card admin-wide">
                    <div class="section-head">
                        <div>
                            <span class="pill">Administrácia</span>
                            <h2>Správa platformy</h2>
                            <p class="muted">Schvaľuj firmy, moderuj používateľov, kontroluj inzeráty a udržiavaj komunikáciu v poriadku.</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h2>Firmy čakajúce na overenie</h2>
                    <p class="muted">Pred aktiváciou účtu môžeš firme zavolať a overiť údaje.</p>
                    <div class="admin-list">
                        <?php if (empty($pendingCompanies)): ?><div class="empty">Momentálne nečaká žiadna firma na schválenie.</div><?php endif; ?>
                        <?php foreach ($pendingCompanies as $company): ?>
                            <div class="admin-row">
                                <div>
                                    <strong><?= e($company['name']) ?></strong><span class="status pending">pending</span><br>
                                    <small class="muted"><?= e($company['email']) ?> · tel. <?= e($company['phone'] ?? '-') ?></small>
                                </div>
                                <div class="admin-actions">
                                    <form method="post"><input type="hidden" name="action" value="approve_company"><input type="hidden" name="email" value="<?= e($company['email']) ?>"><button>Schváliť</button></form>
                                    <form method="post"><input type="hidden" name="action" value="reject_company"><input type="hidden" name="email" value="<?= e($company['email']) ?>"><button class="button-danger">Odmietnuť</button></form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <form class="card form-grid" method="post">
                    <input type="hidden" name="action" value="add_job">
                    <h2>Pridať ponuku za firmu</h2>
                    <input name="company" placeholder="Názov firmy alebo zdroj ponuky" required>
                    <input name="title" placeholder="Názov pozície" required>
                    <input name="location" placeholder="Lokalita" required>
                    <input name="pay" placeholder="Odmena, napr. 8 EUR / hod." required>
                    <select name="type">
                        <option>Flexibilné</option>
                        <option>Po škole</option>
                        <option>Hybrid</option>
                        <option>Remote</option>
                        <option>Víkendy</option>
                    </select>
                    <input name="tags" placeholder="Tagy oddelené čiarkou">
                    <textarea name="description" placeholder="Stručne popíš náplň práce, čas a očakávania" required></textarea>
                    <button>Zverejniť ponuku</button>
                </form>

                <div class="card admin-wide">
                    <h2>Používatelia</h2>
                    <div class="admin-list">
                        <?php foreach ($allUsers as $account): ?>
                            <div class="admin-row">
                                <div>
                                    <strong><?= e($account['name']) ?></strong>
                                    <span class="status"><?= e($account['role']) ?></span>
                                    <?php if (($account['status'] ?? 'approved') !== 'approved'): ?><span class="status pending"><?= e($account['status']) ?></span><?php endif; ?>
                                    <?php if (!empty($account['banned'])): ?><span class="status danger">ban</span><?php endif; ?><br>
                                    <small class="muted"><?= e($account['email']) ?><?= ($account['role'] ?? '') === 'firma' ? ' · tel. ' . e($account['phone'] ?? '-') : '' ?></small>
                                </div>
                                <div class="admin-actions">
                                    <?php if (($account['role'] ?? '') !== 'admin'): ?>
                                        <?php if (($account['role'] ?? '') === 'firma' && ($account['status'] ?? 'approved') !== 'approved'): ?>
                                            <form method="post"><input type="hidden" name="action" value="approve_company"><input type="hidden" name="email" value="<?= e($account['email']) ?>"><button>Schváliť</button></form>
                                        <?php endif; ?>
                                        <?php if (!empty($account['banned'])): ?>
                                            <form method="post"><input type="hidden" name="action" value="unban_user"><input type="hidden" name="email" value="<?= e($account['email']) ?>"><button class="button-ghost">Odblokovať</button></form>
                                        <?php else: ?>
                                            <form method="post"><input type="hidden" name="action" value="ban_user"><input type="hidden" name="email" value="<?= e($account['email']) ?>"><button class="button-danger">Zablokovať</button></form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card admin-wide">
                    <h2>Inzeráty</h2>
                    <div class="admin-list">
                        <?php foreach ($_SESSION['jobs'] as $job): ?>
                            <div class="admin-row">
                                <div>
                                    <strong><?= e($job['title']) ?></strong><br>
                                    <small class="muted"><?= e($job['company']) ?> · <?= e($job['location']) ?> · <?= e($job['pay']) ?></small>
                                </div>
                                <div class="admin-actions">
                                    <a class="button button-ghost" href="?job=<?= e($job['id']) ?>">Zobraziť</a>
                                    <form method="post"><input type="hidden" name="action" value="delete_job"><input type="hidden" name="job_id" value="<?= e($job['id']) ?>"><button class="button-danger">Odstrániť</button></form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card admin-wide">
                    <h2>Chaty</h2>
                    <div class="admin-list">
                        <?php if (empty($_SESSION['chats'])): ?><div class="empty">Zatiaľ tu nie sú žiadne chaty na kontrolu.</div><?php endif; ?>
                        <?php foreach ($_SESSION['chats'] as $chat): ?>
                            <div class="admin-row">
                                <div>
                                    <strong><?= e($chat['job_title']) ?></strong><br>
                                    <small class="muted"><?= e($chat['student_name']) ?> · <?= e($chat['company_name']) ?> · <?= count($chat['messages']) ?> správ</small>
                                </div>
                                <div class="admin-actions">
                                    <a class="button button-ghost" href="?view=chat&chat=<?= e($chat['id']) ?>">Otvoriť</a>
                                    <form method="post"><input type="hidden" name="action" value="delete_chat"><input type="hidden" name="chat_id" value="<?= e($chat['id']) ?>"><button class="button-danger">Odstrániť chat</button></form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($showChat): ?>
            <section class="card chat-card">
                <div class="chat-layout">
                    <aside class="chat-list">
                        <h2>Správy</h2>
                        <p class="muted">Konverzácie k žiadostiam a ponukám nájdeš prehľadne tu.</p>
                        <?php if (empty($userChats)): ?><p class="empty">Zatiaľ tu nemáš žiadnu konverzáciu.</p><?php endif; ?>
                        <?php foreach ($userChats as $chat): ?>
                            <?php $last = end($chat['messages']); ?>
                            <a class="chat-item <?= $selectedChat && (string)$selectedChat['id'] === (string)$chat['id'] ? 'active' : '' ?>" href="?view=chat&chat=<?= e($chat['id']) ?>">
                                <strong><?= e($chat['job_title']) ?></strong><br>
                                <small class="muted"><?= $user['role'] === 'firma' ? e($chat['student_name']) : e($chat['company_name']) ?></small><br>
                                <small class="muted"><?= e($last['author_name']) ?>: <?= e(mb_substr($last['text'], 0, 48)) ?>...</small>
                            </a>
                        <?php endforeach; ?>
                    </aside>

                    <section class="chat-main">
                        <?php if ($selectedChat): ?>
                            <div>
                                <h2><?= e($selectedChat['job_title']) ?></h2>
                                <p class="muted"><?= e($selectedChat['student_name']) ?> ↔ <?= e($selectedChat['company_name']) ?></p>
                            </div>
                            <div class="messages">
                                <?php foreach ($selectedChat['messages'] as $message): ?>
                                    <?php $mine = $message['author_email'] === $user['email']; ?>
                                    <div class="message-row <?= $mine ? 'mine' : '' ?>">
                                        <div class="bubble">
                                            <small><strong><?= e($message['author_name']) ?></strong> · <?= e($message['time']) ?></small>
                                            <p><?= nl2br(e($message['text'])) ?></p>
                                            <?php if (!empty($message['cv'])): ?>
                                                <div class="cv-box">📄 Životopis / portfólio: <?= e($message['cv']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <form class="reply-form" method="post">
                                <input type="hidden" name="action" value="send_message">
                                <input type="hidden" name="chat_id" value="<?= e($selectedChat['id']) ?>">
                                <input name="text" placeholder="Napíš krátku odpoveď..." required>
                                <button>Odoslať</button>
                            </form>
                        <?php else: ?>
                            <div class="empty">Vyber konverzáciu alebo pošli prvú žiadosť na ponuku.</div>
                        <?php endif; ?>
                    </section>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!$showAdmin): ?>
        <section class="layout">
            <div>
                <div class="section-head">
                    <div>
                        <span class="pill">🔥 Aktuálne príležitosti</span>
                        <h2>Brigády pre študentov</h2>
                        <p class="muted">Vyber si ponuku, predstav sa firme a pokračuj v chate bez zbytočného vybavovania.</p>
                    </div>
                    <form method="get" class="search-box">
                        <span>🔎</span>
                        <input name="q" value="<?= e($query) ?>" placeholder="Hľadať pozíciu, firmu alebo mesto...">
                    </form>
                </div>

                <div class="jobs-list">
                    <?php if (empty($filteredJobs)): ?>
                        <div class="card empty">Pre tento filter sme nenašli žiadnu ponuku.</div>
                    <?php endif; ?>

                    <?php foreach ($filteredJobs as $job): ?>
                        <a class="card job-card" href="?job=<?= e($job['id']) ?>&q=<?= urlencode($query) ?>">
                            <div class="job-top">
                                <div>
                                    <div class="tags">
                                        <span class="tag tag-main"><?= e($job['type']) ?></span>
                                        <?php foreach ($job['tags'] as $tag): ?>
                                            <span class="tag">#<?= e($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <h3><?= e($job['title']) ?></h3>
                                    <strong class="muted"><?= e($job['company']) ?></strong>
                                    <p class="job-desc"><?= e($job['description']) ?></p>
                                    <div class="job-meta">
                                        <span>📍 <?= e($job['location']) ?></span>
                                        <span class="pay"><?= e($job['pay']) ?></span>
                                    </div>
                                </div>
                                <div class="mini-stats">
                                    <span><strong><?= e($job['views']) ?></strong><small>zobrazení</small></span>
                                    <span><strong><?= e($job['applications']) ?></strong><small>reakcií</small></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="side-panel">
                <?php if ($user['role'] === 'firma'): ?>
                    <form class="card form-grid" method="post">
                        <input type="hidden" name="action" value="add_job">
                        <h2>Pridať ponuku</h2>
                        <p class="muted">Napíš ponuku jasne, konkrétne a férovo. Študenti ocenia čas, odmenu aj očakávania.</p>
                        <input name="title" placeholder="Názov pozície" required>
                        <input name="location" placeholder="Lokalita" required>
                        <input name="pay" placeholder="Odmena, napr. 8 € / hod." required>
                        <select name="type">
                            <option>Flexibilné</option>
                            <option>Po škole</option>
                            <option>Hybrid</option>
                            <option>Remote</option>
                            <option>Víkendy</option>
                        </select>
                        <input name="tags" placeholder="Tagy oddelené čiarkou">
                        <textarea name="description" placeholder="Popíš náplň práce, zmeny, požiadavky a výhody" required></textarea>
                        <button>Zverejniť ponuku</button>
                    </form>
                <?php endif; ?>

                <div class="card detail-card">
                    <h2>Detail ponuky</h2>
                    <?php if ($selectedJob): ?>
                        <h3><?= e($selectedJob['title']) ?></h3>
                        <p class="muted"><?= e($selectedJob['description']) ?></p>
                        <div class="detail-box">
                            <strong><?= e($selectedJob['company']) ?></strong><br>
                            <span class="muted"><?= e($selectedJob['location']) ?></span><br><br>
                            <strong style="color:#86efac"><?= e($selectedJob['pay']) ?></strong>
                        </div>
                        <div class="mini-stats" style="width:100%; background:rgba(255,255,255,.10); color:white; margin-top:14px;">
                            <span><strong><?= e($selectedJob['views']) ?></strong><small style="color:#cbd5e1">zhliadnutí</small></span>
                            <span><strong><?= e($selectedJob['applications']) ?></strong><small style="color:#cbd5e1">chatov</small></span>
                        </div>

                        <?php if ($user['role'] === 'student'): ?>
                            <?php
                            $existingChat = null;
                            foreach ($userChats as $chat) {
                                if ((int)$chat['job_id'] === (int)$selectedJob['id']) {
                                    $existingChat = $chat;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($existingChat): ?>
                                <br><a class="button" style="width:100%" href="?view=chat&chat=<?= e($existingChat['id']) ?>">💬 Pokračovať v konverzácii</a>
                            <?php else: ?>
                                <form class="application-box" method="post">
                                    <input type="hidden" name="action" value="send_application">
                                    <input type="hidden" name="job_id" value="<?= e($selectedJob['id']) ?>">
                                    <strong>Predstav sa firme</strong>
                                    <textarea name="message" placeholder="Napíš, prečo ťa ponuka zaujala, kedy vieš pracovať a čo už máš za sebou..." required></textarea>
                                    <input name="cv" placeholder="Link na CV, portfólio alebo LinkedIn">
                                    <button>Odoslať žiadosť</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="muted">Vyber ponuku zo zoznamu a tu sa zobrazia detaily, odmena aj možnosť reagovať.</p>
                    <?php endif; ?>
                </div>
            </aside>
        </section>
        <?php endif; ?>
    </main>
<?php endif; ?>
<script>
function swipeAuth(event, mode) {
    const tabs = event.currentTarget.closest('.auth-tabs');
    const currentIsRegister = tabs.classList.contains('register');
    const targetIsRegister = mode === 'register';

    if (currentIsRegister === targetIsRegister) return;

    event.preventDefault();
    tabs.classList.toggle('register', targetIsRegister);

    tabs.querySelectorAll('.auth-tab').forEach(tab => tab.classList.remove('active'));
    event.currentTarget.classList.add('active');

    setTimeout(() => {
        window.location.href = mode === 'register' ? '?mode=register' : '?mode=login';
    }, 260);
}
</script>
</body>
</html>
