<?php
    //start session
    session_start();


    session_unset();
    session_destroy();
    setcookie("userlogin", "", time() - 3600*3600);
    setcookie("userlogin", "", time() - 3600*3600, '/');

    //send user back to previous page
    header('location:../');



?>