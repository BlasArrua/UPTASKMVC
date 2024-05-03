<?php
namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email{
    protected $email;
    protected $nombre;
    protected $token;

    public function __construct($email,$nombre,$token){
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion(){
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->setFrom('b.paginasweb@gmail.com');
        $mail->addAddress($this->email);
        $mail->Subject = 'Confirma tu Cuenta';
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $contenido = "<html>";
        $contenido .= " Hola <strong>".$this->nombre."</strong>. Has creado tu cuenta correctamente en Uptask, confirmala en el siguiente enlace.</p>";
        $contenido .= "<p>Presiona aqui: <a href='". $_ENV['APP_URL'] ."/confirmar?token=".$this->token."'>Confirmar Cuenta</a></p>";
        $contenido .= "<p>Si tu no creaste esta cuenta puedes ignorar el mensaje.</p>";
        $contenido .= "<p>- Soporte UPTASK -</p>";
        $contenido .= "</html>";

        $mail->Body = $contenido;
        $mail->send();
    }


        

    public function enviarInstrucciones(){
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->setFrom('b.paginasweb@gmail.com');
        $mail->addAddress($this->email);
        $mail->Subject = 'Reestablecer Password';
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $contenido = "<html>";
        $contenido .= " Hola <strong>".$this->nombre."</strong>. Parece que olvidaste tu Password, sigue el siguiente enlace para reestablecerla.</p>";
        $contenido .= "<p>Presiona aqui: <a href='". $_ENV['APP_URL'] ."/reestablecer?token=".$this->token."'>Reestablecer Password</a></p>";
        $contenido .= "<p>Si tu no creaste esta cuenta puedes ignorar el mensaje.</p>";
        $contenido .= "<p>- Soporte UPTASK -</p>";
        $contenido .= '</html>';

        $mail->Body = $contenido;
        $mail->send();
    }
}