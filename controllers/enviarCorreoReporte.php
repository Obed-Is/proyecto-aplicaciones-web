<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asunto = trim($_POST['asunto']);
    $mensaje = trim($_POST['mensaje']);
    $archivo = $_FILES['adjunto'];
    $maxSize = 10 * 1024 * 1024; // 10 MB SERIA EL MAXIMO TAMAÑO PARA EL ARCHIVO

    if (strlen($asunto) < 3 || strlen($asunto) > 50) {
        header("Location: ../views/reportarProblema.php?asunto=El asunto debe contener entre 3 y 50 caracteres");
        exit();
    }

    if (strlen($mensaje) < 3) {
        $_SESSION["errorEmail"]["mensaje"] = "El mensaje debe contener minimo 3 caracteres";
        $_SESSION["emailMensaje"] = $mensaje;
        header("Location: ../views/reportarProblema.php?mensaje=El mensaje debe contener minimo 3 caracteres");
        exit();
    }


    if (isset($archivo) && $archivo['error'] === UPLOAD_ERR_OK) {
        if ($archivo['size'] > $maxSize) {
            $_SESSION['errorEmail']['archivo'] = "Solo se reciben archivos con un tamaño maximo de 5MB";
            header('Location: ../views/reportarProblema.php?archivo=Solo se admiten archivos con un tamaño maximo de 10 MB');
            exit();
        }
    }

    $userEnvioCorreo = $_ENV['DIRECCION_CORREO_USUARIO'];
    $passUserEnvio = $_ENV['CLAVE_CORREO'];
    $userReciboCorreo = $_ENV['DIRECCION_CORREO_ADMIN_DESTINO'];
    $mail = new PHPMailer(true);

    try {
        //Configuraciones
        $mail->isSMTP();                                            
        $mail->Host = 'smtp.gmail.com';                   
        $mail->SMTPAuth = true;                                
        $mail->Username = $userEnvioCorreo;                   
        $mail->Password = $passUserEnvio;                          
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port = 465;                                   

        //Remitentes
        $mail->setFrom($userEnvioCorreo, 'Oro Verde');
        $mail->addAddress($userReciboCorreo);   


        //Contenido
        $mail->isHTML(true);                          
        $mail->Subject = htmlspecialchars($asunto);
        $mail->Body = "<html>
                        <head>
                            <style>
                                body {
                                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                    background-color: #f7f7f7;
                                    padding: 20px;
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    min-height: 100vh;
                                }
                                .container {
                                    background-color: #ffffff;
                                    border-radius: 10px;
                                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                    width: 100%;
                                    max-width: 600px;
                                    overflow: hidden;
                                }
                                img{
                                    width: 80px;
                                    object-fit: contain;
                                }
                                .header {
                                    background-color: #535854;
                                    color: white;
                                    padding: 10px;
                                    text-align: center;
                                    border-radius: 10px 10px 0 0;
                                }
                                .header h1 {
                                    margin: 0;
                                    font-size: 2.5em;
                                    font-weight: 600;
                                }
                                .content {
                                    padding: 30px;
                                    line-height: 1.7;
                                    color: #333;
                                }
                                .user-info {
                                    margin-top: 20px;
                                    background-color: #f4fdf4;
                                    padding: 15px;
                                    border-left: 4px solid #4CAF50;
                                    border-radius: 5px;
                                }
                                .mensaje{
                                    font-size: 1.1em;
                                }
                                .user-info p {
                                    margin: 5px 0;
                                }
                                .section-title {
                                    color: #4CAF50;
                                    font-size: 1.5em;
                                    margin-top: 20px;
                                    margin-bottom: 10px;
                                    border-bottom: 2px solid #8BC34A;
                                    padding-bottom: 5px;
                                }
                                .highlight {
                                    font-weight: bold;
                                    color: #2E7D32;
                                }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>🌱 Oro Verde</h1>
                                </div>
                                <div class='content'>
                                    <div class='section-title'>{$asunto}</div>
                                    <p class='mensaje'>$mensaje</p>

                                    <div class='section-title'>Enviado por</div>
                                    <div class='user-info'>
                                        <p><strong>Nombre:</strong> {$_SESSION['usuario']}</p>
                                    </div>

                                    <p style='margin-top: 30px;'>Saludos cordiales,<br>El equipo de <span class='highlight'>Oro Verde</span></p>
                                    <p style='margin: 0 auto; width: 230px;'>© 2025 Oro Verde - El Salvador</p>
                                </div>
                            </div>
                        </body>
                        </html>";

        // se sube el archivo si se cargo bien y no esta vacio si no cumple no se sube
        if (isset($archivo) && $archivo['error'] === UPLOAD_ERR_OK && $archivo['size'] > 0) {
            $mail->addAttachment($archivo['tmp_name'], $archivo['name']);

        }

        $mail->send();
        header('Location: ../views/reportarProblema.php?envio=El correo ha sido enviado correctamente&icon=success');
    } catch (Exception $e) {
        header('Location: ../views/reportarProblema.php?envio=Ocurrio un error al intentar enviar el correo&icon=error');
        echo "Ocurrio un error al intentar mandar el correo: {$mail->ErrorInfo}";
    }

}

?>