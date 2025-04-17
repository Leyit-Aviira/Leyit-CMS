<?php
// pages/kontakt.php
?>
<section class="page-content">
    <div class="container">
        <h1>Kontakt</h1>
        <p>Skontaktuj się z nami, aby uzyskać więcej informacji lub umówić się na konsultację.</p>
        <div class="contact-content">
            <div class="contact-left">
                <form action="mail.php" method="post" class="contact-form">
                    <label for="name">Imię i nazwisko:</label>
                    <input type="text" id="name" name="name" required>
                    
                    <label for="email">Adres email:</label>
                    <input type="email" id="email" name="email" required>
                    
                    <label for="subject">Temat:</label>
                    <input type="text" id="subject" name="subject" required>
                    
                    <label for="message">Wiadomość:</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                    
                    <!-- Przykładowy element zgody na przetwarzanie danych -->
                    <div class="consent-container">
                        <input type="checkbox" id="consent" name="consent" required>
                        <span>Informacja o przetwarzaniu danych osobowych <span class="required-asterisk">*</span></span>
                    </div>
                    
                    <button type="submit">Wyślij wiadomość</button>
                </form>
            </div>
            <div class="contact-right">
                <div class="company-info">
                    <table>
                        <tr>
                            <th colspan="2">Dane firmy</th>
                        </tr>
                        <tr>
                            <td><strong>Nazwa:</strong></td>
                            <td>HP Moduły Betonowe Patryk Hernik</td>
                        </tr>
                        <tr>
                            <td><strong>Adres:</strong></td>
                            <td>16, 26-631 Kolonia Lesiów</td>
                        </tr>
                        <tr>
                            <td><strong>Telefon:</strong></td>
                            <td>+48 508535416</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>garazehp@gmail.com</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="map-table">
            <div class="map-container">
                <h2>Lokalizacja firmy</h2>
                <iframe src="https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_MAPS_API_KEY&amp;q=26-631+Kolonia+Lesiów+16" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</section>
