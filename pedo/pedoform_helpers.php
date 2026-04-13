<?php

function get_form_value($name, $default = '') {
    if (!isset($_SESSION['application_data'])) {
        return htmlspecialchars($default, ENT_QUOTES);
    }
    return htmlspecialchars($_SESSION['application_data'][$name] ?? $default, ENT_QUOTES);
}

function get_form_array_value($name, $index, $default = '') {
    if (empty($_SESSION['application_data'][$name]) || !is_array($_SESSION['application_data'][$name])) {
        return htmlspecialchars($default, ENT_QUOTES);
    }
    return htmlspecialchars($_SESSION['application_data'][$name][$index] ?? $default, ENT_QUOTES);
}

function save_form_data($post) {
    if (!isset($_SESSION['application_data']) || !is_array($_SESSION['application_data'])) {
        $_SESSION['application_data'] = [];
    }

    foreach ($post as $name => $value) {
        if (in_array($name, ['next', 'save_next', 'save', 'back', 'reset_form', 'submit_application'], true)) {
            continue;
        }
        $_SESSION['application_data'][$name] = $value;
    }
}

function save_temp_uploaded_file($inputName, $sessionKey, $prefix) {
    if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $uploadDir = __DIR__ . '/uploads/temp';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
    $filename = sprintf('%s_%s_%s.%s', $prefix, session_id(), time() . '_' . rand(1000, 9999), $extension);
    $targetPath = $uploadDir . '/' . $filename;
    if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetPath)) {
        $relativePath = 'uploads/temp/' . $filename;
        $_SESSION['application_data'][$sessionKey] = $relativePath;
        return $relativePath;
    }
    return null;
}

function clear_form_data() {
    if (!empty($_SESSION['application_data']) && is_array($_SESSION['application_data'])) {
        foreach ($_SESSION['application_data'] as $value) {
            if (is_string($value) && strpos($value, 'uploads/temp/') === 0) {
                @unlink(__DIR__ . '/' . $value);
            }
        }
    }
    unset($_SESSION['application_data']);
}

function set_form_message($message) {
    $_SESSION['form_message'] = $message;
}

function show_form_message() {
    if (!empty($_SESSION['form_message'])) {
        echo '<div class="note-text" style="background: #e8f7e8; color: #1f5c2c; margin-bottom: 18px;">' . htmlspecialchars($_SESSION['form_message'], ENT_QUOTES) . '</div>';
        unset($_SESSION['form_message']);
    }
}
