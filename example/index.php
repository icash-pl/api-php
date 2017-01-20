<?php
require 'vendor/autoload.php';

use iCashpl\ApiPhp\iCash;

$settings = [
    /**
     * APP key partnera nadawany po zarejestrowaniu konta (dostępny po zalogowaniu)
     */
    'app_key' => 'x5snnjaKUz7OkC0veJHGpBgOpiGQGCZd',
    
    /**
     * Numer identyfikacyjny kanału SMS
     */
    'service_id' => 'rGiDLltiS4OrAntBHae664P7BKbNWECL',
    
    /**
     * Treść wiadomości, która zostaje zainicjowana przez partnera w panelu.
     * Pamiętaj, że błąd powoduje nierozliczenie płatności!
     */
    'text' => 'ICH.TEST',
    
    /**
     * Numer z gamy zainicjowanych w panelu partnera
     */
    'number' => 7055,
    
    /**
     * Koszt wiadomości netto jaki poniesie klient podczas zakupu produktu.
     */
    'cost' => 1,
    
    /**
     * Podczas rozwoju aplikacji system może wyświetlić błąd
     */
    'debug' => true,
];

$status = '';

/**
 * Weryfikujemy, czy formularz został wysłany
 */
if (isset($_POST['code'])) {
    $icash = new iCash($settings['app_key']);
    $icash->getStatusCode([
        'service' => $settings['service_id'],
        'number' => $settings['number'],
        'code' => $_POST['code'],
    ]);
    
    /**
     * Jeśli kod jest prawidłowy
     */
    if ($icash->statusOk()) {
        $status .= '<div class="alert alert-success">Twój kod jest prawidłowy. Dziękujemy za zakupy.</div>';
        
        /**
         * Tutaj możesz również wykonywać inne operacje
         * Np. dodać zapytanie mysql, wysłać maila itp.
         */
    } else {
        if ($settings['debug'] && $icash->hasError()) {
            $error = $icash->getError();
            $status .= '<div class="alert alert-danger">Kod błędu: ' . $error->code . ' - ' . $error->value . '</div>';
        } else {
            $status .= '<div class="alert alert-danger">Przesłany kod jest nieprawidłowy, przepisz go ponownie.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="style.css">
        <title>Przykladowy skrypt platnosci SMS</title>
    </head>
    <body>
        <div class="container">            
            <div class="row">
                <div class="col-sm-6">
                    <img src="http://icash.pl/img/logo.png">
                </div>
                <div class="col-sm-6 text-right">
                    <h3>Payment Gateway</h3>
                </div>
            </div>
            
            <hr>
            
            <?php echo $status; ?>
            
            <div class="text-center margin-bottom-25">
                <h4>Zakup produktu - demo</h4>
            </div>
            
            <p class="margin-bottom-25">W celu zakupu produktu proszę wysłać SMS na numer <b><?php echo $settings['number']; ?></b> 
                o treści <b><?php echo $settings['text']; ?></b><br>
                Koszt wysłania wiadomości <?php echo $settings['cost']; ?> zł netto (<?php echo number_format($settings['cost'] * (1 + 23 / 100), 2); ?> zł z vat).</p>
            
            <div class="text-center margin-bottom-25">
                <h4 class="margin-bottom-15">Wprowadź otrzymany kod</h4>
                <form method="post">
                    <div class="form-group" style="width: 300px; margin: 0 auto;">
                        <div class="input-group">
                            <input name="code" placeholder="Kod sms" type="text" class="form-control" required />
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-success">Sprawdź kod</button>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
            
            <p><small>Właścicielem serwisu jest <b>Nazwa firmy</b>. Wysyłając wiadomość akceptujesz regulamin serwisu <b>Regulamin serwisu Partnera</b>, 
            oraz regulamin <a href="https://icash.pl/documents/regulamin-icash-pl-platnosci-sms.pdf" target="_blank">systemu płatności 
            <strong>iCash.pl</strong></a>, który jest dostawcą usług mikropłatności SMS Premium. 
            W razie problemów z płatnością prosimy o kontakt poprzez <a href="https://icash.pl/reklamacje">formularz reklamacyjny</a>.</small></p>
        </div>
    </body>
</html>
