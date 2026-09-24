<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function answer(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    answer(405, 'Método não permitido.');
}

if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 8192) {
    answer(413, 'Dados enviados acima do limite.');
}

$expectedOrigin = rtrim((string) (getenv('SITE_ORIGIN') ?: ''), '/');
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($expectedOrigin !== '' && $requestOrigin !== '' && $requestOrigin !== $expectedOrigin) {
    answer(403, 'Origem não permitida.');
}

// A hidden field catches basic automated submissions without telling bots what failed.
if (!empty($_POST['website'])) {
    answer(200, 'Recebemos seu interesse. Entraremos em contato em breve.');
}

$name = trim(preg_replace('/\s+/u', ' ', (string) ($_POST['name'] ?? '')) ?? '');
$phone = preg_replace('/\D+/', '', (string) ($_POST['whatsapp'] ?? '')) ?? '';
$email = trim((string) ($_POST['email'] ?? ''));
$consent = ($_POST['consent'] ?? '') === '1';

if ($name === '' || preg_match_all('/./us', $name) > 120 || preg_match('/[<>]/u', $name)) {
    answer(422, 'Informe um nome válido com até 120 caracteres.');
}
if (strlen($phone) < 10 || strlen($phone) > 13) {
    answer(422, 'Informe um WhatsApp com DDD válido.');
}
if ($email !== '' && (strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    answer(422, 'Informe um e-mail válido ou deixe o campo vazio.');
}
if (!$consent) {
    answer(422, 'Confirme que podemos entrar em contato sobre o curso.');
}

require dirname(__DIR__, 2) . '/app/database.php';

try {
    $statement = database()->prepare(
        'INSERT INTO course_leads (full_name, whatsapp, email, source, consent_at) '
        . 'VALUES (:name, :phone, :email, :source, CURRENT_TIMESTAMP)'
    );
    $statement->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email === '' ? null : $email,
        ':source' => 'landing_page',
    ]);
    answer(201, 'Recebemos seu interesse. Entraremos em contato em breve.');
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        answer(200, 'Seu contato já está registrado. Entraremos em contato em breve.');
    }
    error_log('Saber Dizer lead insert failed; SQLSTATE ' . $exception->getCode());
    answer(503, 'Não foi possível registrar agora. Você pode falar conosco pelo WhatsApp.');
} catch (RuntimeException $exception) {
    error_log('Saber Dizer database configuration is incomplete.');
    answer(503, 'Não foi possível registrar agora. Você pode falar conosco pelo WhatsApp.');
}
