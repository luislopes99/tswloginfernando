<?php
    require_once "config.php"; 
    require_once "sesiones.php";
    require_once "logs.php";
    $error='';
    $disable_login_button = false;
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
        $email = trim($_POST['email']); 
        $password = trim($_POST['password']);
        $ip = $_SERVER['REMOTE_ADDR'];
        $failed_attempts = get_failed_login_attempts($ip, 1); // Verificar los intentos fallidos en las últimas 1 hora
        // Verificar si ha pasado el tiempo necesario desde el último intento fallido
        $time_limit = date('Y-m-d H:i:s', strtotime("-1 minute")); // Intervalo de 1 hora
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM logs WHERE estado = 0 AND ip = ? AND fecha_hora >= ?");
        $stmt->execute([$ip, $time_limit]);
        $attempts_within_time_limit = $stmt->fetchColumn();
        $max_attempts = 2; // Número máximo de intentos fallidos permitidos
        if ($attempts_within_time_limit === 0 && $failed_attempts >= $max_attempts) {
            // Restablecer contador de intentos fallidos para la dirección IP actual
            $stmt = $pdo->prepare("DELETE FROM logs WHERE estado = 0 AND ip = ?");
            $stmt->execute([$ip]);
            // Restablecer el contador a 0
            $failed_attempts = 0;
        }
        if ($failed_attempts >= $max_attempts) {
            $remaining_time = time() - strtotime($time_limit); //opcional  $remaining_time = abs(time() - strtotime($time_limit)); para obtener el valor absoluto a prueba de errores
            $error .= '<p class="error">Has alcanzado el límite de intentos fallidos de inicio de sesión. Por favor, inténtalo más tarde.</p>';
            $disable_login_button = true; // Variable para deshabilitar el botón de "Iniciar Sesión"
        }
         else {
            if (empty($email)) {
        $error .= '<p class="error">Por favor ingrese su Correo!</p>';
    }
    if (empty($password)) {
        $error .= '<p class="error">Por favor ingrese su contraseña!</p>';
    }
    if (empty($error)) {
        // Verificar el reCAPTCHA
        $captcha_response = $_POST['g-recaptcha-response'];
        $secret_key = '6LdDbVQmAAAAACvHShf4_zDbV3IEt4lq3VSdsMcC'; // Reemplazar con su clave secreta de reCAPTCHA
        $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $captcha_response);
        $response_data = json_decode($verify_response);
        if ($response_data->success) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bindParam(1, $email); 
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['userid'] = $row['id'];
                    $_SESSION['roles'] = [$row['role']];
                    write_login_log($email, true, "Login Exitoso");
                    if (in_array('admin', $_SESSION['roles'])) {
                        header("Location: dasboard/index.php");
                    } else {
                        header("Location: welcome.php");
                    }
                    exit;//
                }else {
                    $error .= '<p class="error">La contraseña no es valida!</p>';
                    write_login_log($email, false, "Contraseña invalida");
                }
            } else {
                $error .= '<p class="error">No se encontro usuario asociado al correo!</p>';
                write_login_log($email, false, "Usuario no encontrado");
            }
        } else {
            $error .= '<p class="error">Por favor intente de nuevo el Captcha!</p>';
            write_login_log($email, false, "Captcha invalido");
        }
    }
}
 }
?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.112.5">
    <title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .b-example-divider {
        width: 100%;
        height: 3rem;
        background-color: rgba(0, 0, 0, .1);
        border: solid rgba(0, 0, 0, .15);
        border-width: 1px 0;
        box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
      }

      .b-example-vr {
        flex-shrink: 0;
        width: 1.5rem;
        height: 100vh;
      }

      .bi {
        vertical-align: -.125em;
        fill: currentColor;
      }

      .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
      }

      .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
      }

      .btn-bd-primary {
        --bd-violet-bg: #712cf9;
        --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

        --bs-btn-font-weight: 600;
        --bs-btn-color: var(--bs-white);
        --bs-btn-bg: var(--bd-violet-bg);
        --bs-btn-border-color: var(--bd-violet-bg);
        --bs-btn-hover-color: var(--bs-white);
        --bs-btn-hover-bg: #6528e0;
        --bs-btn-hover-border-color: #6528e0;
        --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
        --bs-btn-active-color: var(--bs-btn-hover-color);
        --bs-btn-active-bg: #5a23c8;
        --bs-btn-active-border-color: #5a23c8;
      }
      .bd-mode-toggle {
        z-index: 1500;
      }
    </style>

    
    <!-- Custom styles for this template -->
    <link href="sign-in.css" rel="stylesheet">
  </head>
  <body class="d-flex align-items-center py-4 bg-body-tertiary">
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
      <symbol id="check2" viewBox="0 0 16 16">
        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
      </symbol>
      <symbol id="circle-half" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
      </symbol>
      <symbol id="moon-stars-fill" viewBox="0 0 16 16">
        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
      </symbol>
      <symbol id="sun-fill" viewBox="0 0 16 16">
        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
      </symbol>
    </svg>
    <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
      <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
              id="bd-theme"
              type="button"
              aria-expanded="false"
              data-bs-toggle="dropdown"
              aria-label="Toggle theme (auto)">
        <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#circle-half"></use></svg>
        <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#sun-fill"></use></svg>
            Light
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
            Dark
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
            <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#circle-half"></use></svg>
            Auto
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
      </ul>
    </div> 
<main class="form-signin w-100 m-auto ">
  <form class="" method="post" action="">
    <img class="mb-4" src="bootstrap-fill.svg" alt="" width="72" height="57">
    <h1 class="h3 mb-3 fw-normal">Inicia sesión</h1>

    <div class="form-floating">
      <input type="text" class="form-control" id="floatingInput" placeholder="name@example.com" required name="email">
      <label for="floatingInput">Correo Electronico</label>
    </div>
    <div class="form-floating">
      <input type="password" class="form-control" id="floatingPassword" placeholder="Password" required name="password">
      <label for="floatingPassword">Contraseña</label>
    </div>
    <div class="form-check text-start my-3">
      <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
      <label class="form-check-label" for="flexCheckDefault">
        Recuerdame
      </label>
    </div>
    <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
    <div class="g-recaptcha" data-sitekey="6LdDbVQmAAAAAAsqVYB53WuVjNZd-4jv8vZCxU_7"></div>
    </div>
    <button id="login_btn" class="btn btn-primary w-100 py-2" type="submit" name="submit" <?php if($disable_login_button) echo 'disabled'; ?>>Iniciar Sesión </button>
     <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
     <div id="countdown" class="text-danger"> </div>
     </div>
     <hr class="my-4">
     <h2 class="fs-5 fw-bold mb-3 text-center">Aún no tienes cuenta</h2>
     <button class="w-100 py-2 mb-2 btn btn-outline-secondary rounded-3" type="submit" onclick="window.location.href='register.php'">
        Registrate con tu correo aquí
        </button>
    <p class="mt-5 mb-3 text-center text-body-secondary">&copy; 2017–2023</p>
  </form>
</main>
<script>
      var remaining_time = <?php echo json_encode($remaining_time); ?>;
      var countdown_elem = document.getElementById("countdown");
      var countdown_interval = setInterval(function() {
          remaining_time--;
          if (remaining_time <= 0) {
              clearInterval(countdown_interval);
              countdown_elem.innerHTML = "Inicio de sesión disponible";
              document.getElementById("login_btn").disabled = false; // Habilitar el botón de inicio de sesión
          } else {
            countdown_elem.innerHTML = "Tiempo restante: " + remaining_time + " segundos";
          }
      }, 1000);
      document.getElementById("login_btn").disabled = true; // Inhabilitar el botón de inicio de sesión
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    </body>
</html>
