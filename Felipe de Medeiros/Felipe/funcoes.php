<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function h(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $pagina): never
{
    header('Location: ' . $pagina);
    exit;
}

function exigir_login(): void
{
    if (empty($_SESSION['usuario_id'])) {
        redirecionar('login.php');
    }

    // Encerra a sessão após 30 minutos sem atividade.
    $ultimaAtividade = (int) ($_SESSION['ultima_atividade'] ?? time());
    if (time() - $ultimaAtividade > 1800) {
        $_SESSION = [];
        session_regenerate_id(true);
        mensagem('aviso', 'Sua sessão expirou. Entre novamente.');
        redirecionar('login.php');
    }

    $_SESSION['ultima_atividade'] = time();
}

function token_csrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validar_csrf(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals(token_csrf(), $token)) {
        http_response_code(419);
        exit('A solicitação expirou. Atualize a página e tente novamente.');
    }
}

function mensagem(string $tipo, string $texto): void
{
    $_SESSION['mensagem'] = ['tipo' => $tipo, 'texto' => $texto];
}

function mostrar_mensagem(): void
{
    if (empty($_SESSION['mensagem'])) {
        return;
    }

    $msg = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']);
    echo '<div class="mensagem ' . h($msg['tipo']) . '">' . h($msg['texto']) . '</div>';
}

function salvar_dados_antigos(array $dados): void
{
    $_SESSION['form_antigo'] = $dados;
}

function dados_antigos(): array
{
    $dados = $_SESSION['form_antigo'] ?? [];
    unset($_SESSION['form_antigo']);
    return is_array($dados) ? $dados : [];
}

function somente_numeros(string $valor): string
{
    return preg_replace('/\D/', '', $valor) ?? '';
}

function criptografar_cpf(string $cpf): string
{
    $chave = hash('sha256', APP_KEY, true);
    $iv = random_bytes(12);
    $tag = '';
    $texto = openssl_encrypt($cpf, 'aes-256-gcm', $chave, OPENSSL_RAW_DATA, $iv, $tag);

    if ($texto === false) {
        throw new RuntimeException('Não foi possível proteger o CPF.');
    }

    return base64_encode($iv . $tag . $texto);
}

function descriptografar_cpf(string $valor): string
{
    $dados = base64_decode($valor, true);
    if ($dados === false || strlen($dados) < 29) {
        return '';
    }

    $iv = substr($dados, 0, 12);
    $tag = substr($dados, 12, 16);
    $texto = substr($dados, 28);
    $cpf = openssl_decrypt($texto, 'aes-256-gcm', hash('sha256', APP_KEY, true), OPENSSL_RAW_DATA, $iv, $tag);

    return $cpf === false ? '' : $cpf;
}

function hash_cpf(string $cpf): string
{
    return hash_hmac('sha256', somente_numeros($cpf), APP_KEY);
}

function formatar_cpf(string $cpf): string
{
    $cpf = somente_numeros($cpf);
    if (strlen($cpf) !== 11) {
        return $cpf;
    }
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

function formatar_data(?string $data): string
{
    if (!$data) {
        return '—';
    }
    $timestamp = strtotime($data);
    return $timestamp ? date('d/m/Y', $timestamp) : '—';
}

function formatar_data_hora(?string $data): string
{
    if (!$data) {
        return '—';
    }
    $timestamp = strtotime($data);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : '—';
}
