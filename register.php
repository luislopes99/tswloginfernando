<?php
require_once "config.php"; 
require_once "sesiones.php"; 
$error = '';
$success = '';
$result = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $fullname = trim($_POST['name']);
    $email = trim($_POST['email']); 
    $password = trim($_POST['password']); 
    $confirm_password = trim($_POST["confirm_password"]); 
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    if ($query->rowCount() > 0) { 
        $error .= '<p class="error">El correo electronico ya esta registrado!</p>';
    } else { 
        if (strlen($password) < 6) {
            $error .= '<p class="error">La contraseña debe ser mayor a 6 caracteres.</p>'; 
        } 

        if (empty($confirm_password)) { 
            $error .= '<p class="error">Por favor confirme la contraseña.</p>'; 
        } else { 
            if (empty($error) && ($password != $confirm_password)) { 
                $error .= '<p class="error">La contraseña no coicide.</p>'; 
            } 
        } 

        if (empty($error)) { 
            $insertQuery = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'usuario');");
            $result = $insertQuery->execute([$fullname, $email, $password_hash]);
            if ($result) {
                // Verificación del captcha
                $captcha_response = $_POST['g-recaptcha-response'];
                $secret_key = "6Le1sT4mAAAAAF0fwNjTl9b2snuIUL55Tzo0Hbh4";
                $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$captcha_response}");
                $captcha_success = json_decode($verify);

                if ($captcha_success->success == false) {
                    $error .= '<p class="error">Por favor intente de nuevo el Captcha!</p>';
                    $deleteQuery = $pdo->prepare("DELETE FROM users WHERE email = ?");
                    $deleteQuery->execute([$email]); // elimina el registro de la base de datos si el captcha no es válido
                } else {
                    $success .= '<p class="success annimation">Registro Exitoso!</p>';
                }
            } else {
                $error .= '<p class="error">Intentalo mas tarde!</p>';
            }
        }
    }
    $query = null;
    $insertQuery = null;
    $pdo = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrar usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style type="text/css">
        .gradient-custom-3 {
/* fallback for old browsers */
background: #84fab0;

/* Chrome 10-25, Safari 5.1-6 */
background: -webkit-linear-gradient(to right, rgba(132, 250, 176, 0.5), rgba(143, 211, 244, 0.5));

/* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
background: linear-gradient(to right, rgba(132, 250, 176, 0.5), rgba(143, 211, 244, 0.5))
}
.gradient-custom-4 {
/* fallback for old browsers */
background: #84fab0;

/* Chrome 10-25, Safari 5.1-6 */
background: -webkit-linear-gradient(to right, rgba(132, 250, 176, 1), rgba(143, 211, 244, 1));

/* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
background: linear-gradient(to right, rgba(132, 250, 176, 1), rgba(143, 211, 244, 1))
}
    </style>
</head>

<body>
<section class="vh-100 bg-image"
  style="background-image: url('https://mdbcdn.b-cdn.net/img/Photos/new-templates/search-box/img4.webp');">
  <div class="mask d-flex align-items-center h-100 gradient-custom-3">
    <div class="container h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-9 col-lg-7 col-xl-6">
          <div class="card" style="border-radius: 15px;">
            <div class="card-body p-5">
              <h2 class="text-uppercase text-center mb-5">Crea tu cuenta</h2>
              <form method="post" action="">
                <div class="form-outline mb-4">
                  <input type="text" id="form3Example1c" class="form-control form-control-lg" name="name" required/>
                  <label class="form-label" for="form3Example1cg">Nombre completo</label>
                </div>
                <div class="form-outline mb-4">
                  <input type="email" id="form3Example3c" class="form-control form-control-lg" name="email" required/>
                  <label class="form-label" for="form3Example3cg">Tu correo</label>
                </div>
                <div class="form-outline mb-4">
                  <input type="password" id="form3Example4c" class="form-control form-control-lg" name="password" required />
                  <label class="form-label" for="form3Example4cg">Contraseña</label>
                </div>
                <div class="form-outline mb-4">
                  <input type="password" id="form3Example4cd" class="form-control form-control-lg" name="confirm_password"  required />
                  <label class="form-label" for="form3Example4cdg">Repeat your password</label>
                </div>
                <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
                  <div class="g-recaptcha" data-sitekey="6Le1sT4mAAAAAFOlw9MaxuivCXU0DEv2QmEusgGt"></div>
                </div>
                <div class="form-check d-flex justify-content-center mb-5">
                  <input class="form-check-input me-2" type="checkbox" value="" id="form2Example3cg" />
                  <label class="form-check-label" for="form2Example3g">
                    I agree all statements in <a href="#!" class="text-body"><u>Terms of service</u></a>
                  </label>
                </div>
                <div class="d-flex justify-content-center">
                <input type="submit" name="submit" class="btn btn-success btn-block btn-lg gradient-custom-4 text-body" value="Enviar">
                </div>
                <p class="text-center text-muted mt-5 mb-0">Have already an account? <a href="login.php"
                    class="fw-bold text-body"><u>Login here</u></a></p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>    
</body>
</html>