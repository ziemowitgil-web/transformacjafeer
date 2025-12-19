<?php
session_start();

$statusMessage = '';
$statusClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $statusMessage = "Podaj adres e-mail!";
        $statusClass = 'warning';
    } else {
        // Tutaj możesz podłączyć logikę odblokowywania konta w Okta
        $statusMessage = "Konto <strong>$email</strong> zostało odblokowane!";
        $statusClass = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moduł Odblokowywania Kont – FEER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">FEER IT</a>
    </div>
</nav>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1>Odblokowywanie konta FEER</h1>
        <p>Wprowadź adres e-mail, aby odblokować konto użytkownika.</p>
    </div>

    <?php if ($statusMessage): ?>
        <div class="alert alert-<?= $statusClass ?> text-center">
            <?= $statusMessage ?>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Odblokuj konto</h5>
                    <p class="card-text">Jeśli konto zostało zablokowane po nieudanych logowaniach, użyj tej funkcji.</p>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email-unlock" class="form-label">Adres e-mail</label>
                            <input type="email" class="form-control" id="email-unlock" name="email" placeholder="np. jan.kowalski@feer.org.pl" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Odblokuj konto</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
