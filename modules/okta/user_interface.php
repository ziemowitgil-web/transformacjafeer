<?php
// user_interface.php

session_start();

// Obsługa formularzy (przykładowo, tylko wizualnie)
$statusMessage = '';
$statusClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $statusMessage = "Podaj adres e-mail!";
        $statusClass = 'warning';
    } else {
        switch ($action) {
            case 'unlock':
                // Tutaj wywołaj API Okta do odblokowania konta
                $statusMessage = "Konto <strong>$email</strong> zostało odblokowane!";
                $statusClass = 'success';
                break;
            case 'deactivate':
                // Tutaj wywołaj API Okta do dezaktywacji konta (RODO)
                $statusMessage = "Konto <strong>$email</strong> zostało dezaktywowane!";
                $statusClass = 'warning';
                break;
            default:
                $statusMessage = "Nieznana akcja!";
                $statusClass = 'warning';
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie kontem FEER – Okta</title>
    <link rel="stylesheet" href="styles.css"> <!-- Twój CSS FEER -->
</head>
<body>

<header class="header">
    <div class="container header-container">
        <div class="logo">
            <img src="https://feer.org.pl/files/ban/banner-1.png" alt="FEER Logo">
            <span>FEER IT</span>
        </div>
        <nav class="menu">
            <a href="index.php">Strona główna</a>
            <a href="#okta-module">Moduł Okta</a>
            <a href="#faq">FAQ</a>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="container text-center">
        <h1>Zarządzanie kontem FEER</h1>
        <p>Moduł umożliwia odblokowanie konta lub jego dezaktywację zgodnie z RODO.</p>
    </div>
</section>

<section id="okta-module" class="section bg-light">
    <div class="container">

        <?php if ($statusMessage): ?>
            <div class="<?= $statusClass ?> text-center" style="margin-bottom:20px;">
                <?= $statusMessage ?>
            </div>
        <?php endif; ?>

        <div class="two-columns">
            <!-- Odblokowanie konta -->
            <div class="column card">
                <h3>Odblokuj konto</h3>
                <p>Użyj tej funkcji, jeśli konto zostało zablokowane po nieudanych logowaniach.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="unlock">
                    <label for="email-unlock">Adres e-mail:</label><br>
                    <input type="email" id="email-unlock" name="email" required placeholder="np. jan.kowalski@feer.org.pl" style="width:100%; padding:8px; margin:10px 0; border-radius:6px; border:1px solid #d1d5db;">
                    <button type="submit" class="btn btn-primary btn-lg">Odblokuj konto</button>
                </form>
            </div>

            <!-- Dezaktywacja konta -->
            <div class="column card">
                <h3>Zamknij / dezaktywuj konto</h3>
                <p>Opcja dostępna zgodnie z RODO. Konto zostanie zablokowane i usunięte z systemów FEER.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="deactivate">
                    <label for="email-deactivate">Adres e-mail:</label><br>
                    <input type="email" id="email-deactivate" name="email" required placeholder="np. jan.kowalski@feer.org.pl" style="width:100%; padding:8px; margin:10px 0; border-radius:6px; border:1px solid #d1d5db;">
                    <button type="submit" class="btn btn-secondary btn-lg">Dezaktywuj konto</button>
                </form>
            </div>
        </div>

        <!-- FAQ -->
        <h2 class="section-title" id="faq" style="margin-top:60px;">FAQ – Moduł Okta</h2>
        <details>
            <summary>Jak działa odblokowanie konta?</summary>
            <p>Po wprowadzeniu adresu e-mail, system wysyła polecenie do Okta, aby odblokować konto. Konto odblokowane jest natychmiast.</p>
        </details>
        <details>
            <summary>Co oznacza dezaktywacja konta RODO?</summary>
            <p>Konto zostaje zablokowane i usunięte z systemów FEER, w tym z SSO i dostępu do wszystkich aplikacji.</p>
        </details>
        <details>
            <summary>Jak długo trwa odblokowanie konta?</summary>
            <p>Zwykle odblokowanie trwa kilka sekund – po odświeżeniu strony użytkownik może zalogować się ponownie.</p>
        </details>

    </div>
</section>

<footer class="footer">
    &copy; 2025 Fundacja Edukacja FEER | Wszystkie prawa zastrzeżone
</footer>

</body>
</html>
