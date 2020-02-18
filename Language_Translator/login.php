<?php
    session_start();
    //loggedin ussers should not see this page if loggedin
    if(isset($_SESSION['loged_in_user'])):
        header('location:index.php');
    endif;
    if(isset($_COOKIE["userlogin"])){
        $cookieId=hex2bin($_COOKIE["userlogin"]);
        $_SESSION['loged_in_user']=$cookieId;
        echo "If you are not redirected automatically, please click <a href='index.php'>here</a>";
        header('location:index.php');
        
    }
    if (!isset($_SESSION['count'])) $_SESSION['count'] = 0;
    else ++$_SESSION['count'];
    $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id();
        $_SESSION['initiated'] = 1;
    }


    //get the function.php file from resources
    require_once "functions.php";
    //to get user database
    require_once "database.php";
    if(!empty($_SESSION['regSuccessMessage']) ): 
        echo $_SESSION['regSuccessMessage']; 
        echo "<br/>";
        unset($_SESSION['regSuccessMessage']);   
    endif; 
    if(!empty($_SESSION['signuperr']) ): 
        echo $_SESSION['signuperr']; 
        echo "<br/>";
        unset($_SESSION['signuperr']);   
    endif;

    //check if form is submitted
    if($_SERVER['REQUEST_METHOD']=='POST'):
        if(!empty($_POST['username'])  &&  !empty($_POST['password'])){
            //variables to hold form inputes, and test them
            $username= testTextInput($_POST['username']);
            $password= testPasswordInput($_POST['password']);
            if(isset($_POST['rememberme'])){
                $rememberme=true;
            }else{
                $rememberme=false;
            }
      
            //make sure space value does not enter the data base
            if($username!=""){
                //varibae is +1 if email or username exist
                $userExist=NULL;
                $resetPass=NULL;

                //error messages
                $userExistMessage=NULL;
                $passwordMessage=NULL;

                //create quarry
                $checkUsername="SELECT id, username, password, salt1, salt2 FROM finalexam1 WHERE username=:username";               
                $checkUsernameConn=$conn->prepare($checkUsername);      
                //bind parameters to their respective values
                $checkUsernameConn->bindParam(':username', $username);
                //execute connections
                if(!$checkUsernameConn->execute()){
                    die("failed to check database");
                };
                //get user's data
                $usernameData=$checkUsernameConn->fetch(PDO::FETCH_ASSOC);
                //check if username exist
                if(count($usernameData) > 0  && $usernameData['username']==$username):
                    $userExist++;
                endif;
                    
                //try to login user if user exist
                if($userExist > 0){
                    $password=$usernameData['salt1'].$password.$usernameData['salt2'];
                    //check is $username is a username, and try to login if true
                    if(count($usernameData) > 0  && $usernameData['username']==$username && password_verify($password,$usernameData['password'])){
                        $_SESSION['loged_in_user']=$usernameData['id'];
                        if($rememberme){
                            $remembermeId=bin2hex($usernameData['id']);
                            setcookie("userlogin", $remembermeId, time() + (86400 * 3),"/");
                        }
                        echo "If you are not redirected automatically, please click <a href='index.php'>here</a>";
                        header('location:index.php');
                    }else{
                        $passwordMessage='Username and password do not match';
                    }
            
                }else{
                    $userExistMessage='User is not registered';
                }
      
            }else{
                $userExistMessage="Please enter your login ditail";
            }
      
        }else{
            $emptField="Please enter your login ditail";
        }      

    endif;

    if(!empty($userExistMessage)){ echo  $userExistMessage; echo "<br/>";}
    if(!empty($passwordMessage)){ echo  $passwordMessage; echo "<br/>";}
    if(!empty($emptField)){ echo  $emptField; echo "<br/>";}



$conn=null;

echo <<<_END
    <html>
        <head>
            <title>Lame Translator</title> 
            <style>
                .signup { border: 1px solid #999999;
                font: normal 14px helvetica; color:#444444; }
            </style>
        </head>
        <body>

            <table class="signup" border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
                <th colspan="2" align="center">Login Form</th> 
                <form method="post" action="login.php"> 
                    <td> <p>Don't have an account? <a href='signup.php'>Signup</a></p></td>
                    </tr><tr>
                    <td>Username</td>
                    <td><input type="text" maxlength="32" name="username" /></td> </tr>

                    <tr><td>Password</td>
                    <td><input type="password" maxlength="32" name="password" /></td></tr>
                    
                    <tr><td><input type="checkbox" name="rememberme"/>Remember me</td></tr>
                    <tr><td colspan="2" align="center"><input type="submit" name="login" value="Login" /></td> </tr>
                </form>
            </table>
            <script src="validate.js"></script>  
        </body>
    </html>
_END;
?>
