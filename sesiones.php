<?php
    session_start();
    // Verificar si ya hay una sesión iniciada
    if(isset($_SESSION['userid'])) {
        // Verificar el rol del usuario y redirigir al panel correspondiente
        if($_SESSION['roles'][0] == 'admin') {
            header("Location: dashb.php");
            exit;
        } else {
            header("Location: welcome.php");
            exit;
        }
    }
?>