<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pobranie i filtrowanie danych z formularza
    $name    = strip_tags(trim($_POST["name"]));
    $email   = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = trim($_POST["message"]);

    // Walidacja podstawowa
    if (empty($name) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Proszę uzupełnić formularz poprawnie.";
        exit;
    }

    // Ustawienia wiadomości
    $recipient = "kontakt@twojadomena.pl"; // Zmień na adres odbiorcy
    $email_subject = "Wiadomość z formularza kontaktowego od: $name";
    $email_content  = "Imię i nazwisko: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Temat: $subject\n\n";
    $email_content .= "Wiadomość:\n$message\n";

    $email_headers = "From: $name <$email>";

    // Wysyłanie maila
    if (mail($recipient, $email_subject, $email_content, $email_headers)) {
        echo "Dziękujemy! Twoja wiadomość została wysłana.";
    } else {
        echo "Wystąpił błąd podczas wysyłania wiadomości. Spróbuj ponownie później.";
    }
} else {
    echo "Błąd: nieprawidłowe żądanie.";
}
?>
