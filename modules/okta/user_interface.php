<?php
session_start();

// --- Konfiguracja Okta ---
$oktaDomain = 'https://feerorg.okta.com';
$apiToken = getenv('OKTA_API_TOKEN'); // Token w .env

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    header('Content-Type: application/json');

    $email = trim($_POST['email']);

    if ($email === '') {
        echo json_encode(['status' => 'warning', 'message' => 'Podaj adres e-mail!']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'warning', 'message' => 'Podaj poprawny adres e-mail!']);
        exit;
    }

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
        echo json_encode(['status' => 'danger', 'message' => "Błąd połączenia z API: $curlError"]);
        exit;
    }

    $responseData = json_decode($response, true);

    if ($httpCode === 404) {
        echo json_encode(['status' => 'danger', 'message' => "Nie znaleziono konta o adresie $email"]);
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(['status' => 'success', 'message' => "Konto <strong>$email</strong> zostało odblokowane!"]);
    } else {
        $errorMsg = $responseData['errorSummary'] ?? 'Nieznany błąd';
        echo json_encode(['status' => 'danger', 'message' => "Błąd API: $errorMsg"]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Odblokowywanie konta FEER</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
#loader {
    display: none;
    width: 1.5rem;
    height: 1.5rem;
    border: 0.25em solid #f3f3f3;
    border-top: 0.25em solid #28a745;
    border-radius: 50%;
    animation: spin 0.75s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
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

    <div id="statusMessage"></div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Odblokuj konto TC</h5>
                    <p class="card-text">Jeśli konto zostało zablokowane po nieudanych logowaniach, użyj tej funkcji.</p>
                    <form id="unlockForm">
                        <div class="mb-3">
                            <label for="email-unlock" class="form-label">Adres e-mail</label>
                            <input type="email" class="form-control" id="email-unlock" name="email" placeholder="np. jan.kowalski@feer.org.pl" required>
                        </div>
                        <button type="button" class="btn btn-success w-100 d-flex justify-content-center align-items-center" data-bs-toggle="modal" data-bs-target="#confirmModal">
                            Odblokuj konto
                            <div id="loader" class="ms-2"></div>
                        </button>
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
const statusMessage = document.getElementById('statusMessage');
const loader = document.getElementById('loader');
const modalElement = document.getElementById('confirmModal');
const modalInstance = new bootstrap.Modal(modalElement);

modalElement.addEventListener('show.bs.modal', function () {
    modalEmail.textContent = emailInput.value;
});

confirmBtn.addEventListener('click', function () {
    loader.style.display = 'inline-block';
    confirmBtn.disabled = true;

    const formData = new FormData(unlockForm);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const alertClass = `alert alert-${data.status} text-center`;
        statusMessage.innerHTML = `<div class="${alertClass}">${data.message}</div>`;
        loader.style.display = 'none';
        confirmBtn.disabled = false;
        modalInstance.hide();
    })
    .catch(err => {
        statusMessage.innerHTML = `<div class="alert alert-danger text-center">Błąd połączenia z serwerem!</div>`;
        loader.style.display = 'none';
        confirmBtn.disabled = false;
        modalInstance.hide();
    });
});
</script>

</body>
</html>
