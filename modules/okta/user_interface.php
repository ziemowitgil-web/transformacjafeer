<?php
session_start();

$statusMessage = '';
$statusClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_unlock'])) {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $statusMessage = "Podaj adres e-mail!";
        $statusClass = 'warning';
    } else {
        // --- LOGIKA ODPOWIADAJĄCA API OKTA ---
        $oktaDomain = 'https://twoja-domena.okta.com';
        $apiToken = 'TWÓJ_OKTA_API_TOKEN';

        $url = $oktaDomain . '/api/v1/users/' . urlencode($email) . '/lifecycle/unlock';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: SSWS $apiToken",
            "Accept: application/json",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $statusMessage = "Błąd połączenia z API: $curlError";
            $statusClass = 'danger';
        } elseif ($httpCode >= 200 && $httpCode < 300) {
            $statusMessage = "Konto <strong>$email</strong> zostało odblokowane!";
            $statusClass = 'success';
        } else {
            $statusMessage = "Nie udało się odblokować konta. Kod odpowiedzi API: $httpCode";
            $statusClass = 'danger';
        }
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
                    <form id="unlockForm" method="POST">
                        <div class="mb-3">
                            <label for="email-unlock" class="form-label">Adres e-mail</label>
                            <input type="email" class="form-control" id="email-unlock" name="email" placeholder="np. jan.kowalski@feer.org.pl" required>
                        </div>
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#confirmModal">
                            Odblokuj konto
                        </button>
                        <input type="hidden" name="confirm_unlock" value="1">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal potwierdzenia -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Potwierdzenie odblokowania</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button>
            </div>
            <div class="modal-body">
                Czy na pewno chcesz odblokować konto <strong id="modal-email"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                <button type="button" class="btn btn-success" id="confirmBtn">Tak, odblokuj</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const emailInput = document.getElementById('email-unlock');
    const modalEmail = document.getElementById('modal-email');
    const confirmBtn = document.getElementById('confirmBtn');
    const unlockForm = document.getElementById('unlockForm');

    const confirmModal = document.getElementById('confirmModal');
    confirmModal.addEventListener('show.bs.modal', function () {
        modalEmail.textContent = emailInput.value;
    });

    confirmBtn.addEventListener('click', function () {
        unlockForm.submit();
    });
</script>

</body>
</html>
