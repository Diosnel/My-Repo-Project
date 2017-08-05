<?php
/*
 *  CONFIGURE EVERYTHING HERE
 */

 // require ReCaptcha class
 require('recaptcha-master/src/autoload.php');

// an email address that will be in the From field of the email.
$from = 'Contacto de Sur Arriba .net <contacto@surarriba.net';

// an email address that will receive the email with the output of the form
$sendTo = 'Webmaster <webmaster@surarriba.net>';

// subject of the email
$subject = 'Mensaje del formulario de contacto';

// form field names and their translations.
// array variable name => Text to appear in the email
$fields = array('name' => 'Name', 'surname' => 'Surname', 'phone' => 'Phone', 'email' => 'Email', 'message' => 'Message');

// message that will be displayed when everything is OK :)
$okMessage = 'Ha enviado su mensaje. &iexcl;Gracias, le responderemos lo antes posible!';

// If something goes wrong, we will display this message.
$errorMessage = 'Ocurri&oacute; un error al enviar el mensaje. Por favor, int&eacute;ntelo de nuevo m&aacute;s tarde';

$recaptchaSecret = '6LeXyCQTAAAAAN_yXyiMIL4Z0nhhqkR_NhcCKx1X';

/*
 *  LET'S DO THE SENDING
 */

// if you are not debugging and don't need error reporting, turn this off by error_reporting(0);
// error_reporting(E_ALL & ~E_NOTICE);

try {
    if (!empty($_POST)) {

        // validate the ReCaptcha, if something is wrong, we throw an Exception,
        // i.e. code stops executing and goes to catch() block

        if (!isset($_POST['g-recaptcha-response'])) {
            throw new \Exception('ReCaptcha is not set.');
        }

        // do not forget to enter your secret key in the config above
        // from https://www.google.com/recaptcha/admin

        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret, new \ReCaptcha\RequestMethod\CurlPost());

        // we validate the ReCaptcha field together with the user's IP address

        $response = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);


        if (!$response->isSuccess()) {
            throw new \Exception('ReCaptcha was not validated.');
        }

        // everything went well, we can compose the message, as usually

        $emailText = "You have new message from contact form\n=============================\n";

        foreach ($_POST as $key => $value) {
            if (isset($fields[$key])) {
                $emailText .= "$fields[$key]: $value\n";
            }
        }


        $headers = array('Content-Type: text/plain; charset="UTF-8";',
            'From: ' . $from,
            'Reply-To: ' . $from,
            'Return-Path: ' . $from,
        );

        mail($sendTo, $subject, $emailText, implode("\n", $headers));

        $responseArray = array('type' => 'success', 'message' => $okMessage);
    }
} catch (\Exception $e) {
    $responseArray = array('type' => 'danger', 'message' => $errorMessage);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);

    header('Content-Type: application/json');

    echo $encoded;
} else {
    echo $responseArray['message'];
}
