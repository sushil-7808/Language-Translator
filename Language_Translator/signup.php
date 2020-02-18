<?php
    session_start();
    //loggedin ussers should not see this page if loggedin
    if(isset($_SESSION['loged_in_user'])):
        header('location:index.php');
    endif;


    //get the function.php file from resources
    require_once "functions.php";
    //to get user database
    require_once "database.php";

    //check if form is submitted
    if($_SERVER['REQUEST_METHOD']=='POST'):

        function createSalt(){
            $text = md5(uniqid(rand(), TRUE));
            return substr($text, 0, 6);
        }


        //check if inputes are empty
        if(!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['password_confirm'])){
            // variables to hold the form input, test them and harsh the password
            $username=testTextInput($_POST['username']);
            $password=testPasswordInput($_POST['password']);
            $conPassword=testPasswordInput($_POST['password_confirm']);
            $salt1 = createSalt();
            $salt2 = createSalt();
            $password_hashed=password_hash ($salt1.$password.$salt2, PASSWORD_BCRYPT);
    

            //make sure space value does not enter the data base
            if($username!=""){
                //the next blocks of code is written to check if user exist
                //the summation of below variable has to be zero before the form is submitted
                $userExist=NULL;
                //this variable is +1 if passwords do not match, passwors length less than 6,...
                //...username length less than 4
                $submitProblem=NULL;
                //this adds all the problems together
                $allProblems=NULL;

                //this variables hold all error messages for reistration problems
                $usernameTakenMessage=NULL;
                $passwordMatchMessage=NULL;
                $usernameErrorMessage=NULL;
                $passwordErrorMessage=NULL;
                //these messasage would probably never come up
                $insertErrorMessage=NULL;
                $fillFormMessage=NULL;

                //check if username is taken
                $checkForUsername="SELECT username FROM finalexam1 WHERE username=:username";
                $checkForUsernameConn=$conn->prepare($checkForUsername);
                $checkForUsernameConn->bindParam(':username', $username);
                if(!$checkForUsernameConn->execute()){
                    echo "failed to check username existence";
                    $userExist++;
                }
                $usernameResult=$checkForUsernameConn-> fetch(PDO::FETCH_ASSOC);

                //check if username is taken
                if(count($usernameResult)>0  && $usernameResult['username']==$username):
                    $userExist++;
                    $usernameTakenMessage='username is already taken';
                endif;

                //check if password matches
                if($password != $conPassword):
                    $submitProblem++;
                    $passwordMatchMessage="Passwords do not match";
                endif;

                //check if username if at least 4 characters
                if(strlen($username)<4):
                    $submitProblem++;
                    $usernameErrorMessage='Username must be at least 4 characters';
                endif;

                //check if username has space
                if(preg_match("/ /",$username)):
                    $submitProblem++;
                    if(isset($usernameErrorMessage)){$usernameErrorMessage=$usernameErrorMessage.'<br/>space is not allowed within username';}else{$usernameErrorMessage='space is not allowed within username';}
                endif;

                //check if password is atleast 6 characters
                if(strlen($password)<6):
                    $submitProblem++;
                    $passwordErrorMessage='Password must be at least 6 characters';
                endif;

                //chech if password contains uppercase
                if (!preg_match("/[A-Z]/", $password)):
                    $submitProblem++;
                    if(isset($passwordErrorMessage)){$passwordErrorMessage.='<br/>Password must contain uppercase';}else{$passwordErrorMessage='Password must contain uppercase';}
                endif;

                //chech if password contains lowercase
                if (!preg_match("/[a-z]/", $password)):
                    $submitProblem++;
                    if(isset($passwordErrorMessage)){$passwordErrorMessage.='<br/>Password must contain lowercase';}else{$passwordErrorMessage='Password must contain lowercase';}
                endif;

                //chech if password contains lowercase
                if (!preg_match("/[0-9]/", $password)):
                    $submitProblem++;
                    if(isset($passwordErrorMessage)){$passwordErrorMessage.='<br/>Password must contain number';}else{$passwordErrorMessage='Password must contain number';}
                endif;

                //adds all the problem together
                $allProblems=$userExist + $submitProblem;

                //check if problem is zero, and adds user if true
                if($allProblems<=0):
                    //create quarry
                    $insertData="INSERT INTO finalexam1 (username, password, salt1, salt2) VALUES (:username, :password, :salt1, :salt2)";
                    //connect quarry to user database
                    $insertDataConn=$conn->prepare($insertData);
                    //bind parameter to $insertDataConn // need to run the input through so test b4 i cotinue
                    $insertDataConn->bindParam(':username', $username);
                    $insertDataConn->bindParam(':password', $password_hashed);
                    $insertDataConn->bindParam(':salt1', $salt1);
                    $insertDataConn->bindParam(':salt2', $salt2);
                    //execute $insertDataConn in an if statement
                    if($insertDataConn->execute()){
                        $sql="CREATE TABLE `$username` (
                            `English` varchar(119) NOT NULL,
                            `Spanish(Default)` varchar(199) NOT NULL
                        )";                        
                        $sql=$conn->prepare($sql);
                        if(!$sql->execute()){
                            $_SESSION['signuperr']="failed to create default model table";
                        }
                        $_SESSION['regSuccessMessage']=$username.' has been successfully registered';
                        echo "If you are not redirected automatically, please click <a href='login.php'>here</a>";
                        header('location:login.php');
                    }else{
                        //i dont think this block of code would ever run
                        $insertErrorMessage='failed to register user due to an unknow problem <br/> contact us if problem persist';
                    }
                endif;
            }else{
                $fillFormMessage='Please input valid credentials';
            }
            
        }else{
            if(isset($fillFormMessage)){$fillFormMessage=$fillFormMessage.'<br/>Please completly fill out the form';}else{$fillFormMessage='Please completly fill out the form';};
        }
    endif;

    if(!empty($fillFormMessage)){ 
        echo  $fillFormMessage; 
        echo "<br/>";
    }
    if(!empty($insertErrorMessage)){ echo  $insertErrorMessage; echo "<br/>";}
    if(!empty($usernameTakenMessage)){ echo  $usernameTakenMessage; echo "<br/>";}
    if(!empty($usernameErrorMessage)){ echo  $usernameErrorMessage; echo "<br/>";}
    if(!empty($passwordErrorMessage)){ echo  $passwordErrorMessage; echo "<br/>";}
    if(!empty($passwordMatchMessage) && empty($passwordErrorMessage)){ echo $passwordMatchMessage; echo "<br/>";}

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
                <th colspan="2" align="center">Signup Form</th> 
                <form method="post" action="signup.php" onsubmit="return validate(this)"> 
                    <td> <p>Already a member? <a href='login.php'>Login</a></p></td>
                    </tr><tr>
                    <td>Username</td>
                    <td><input type="text" maxlength="32" name="username" /></td> </tr>

                    <tr><td>Password</td>
                    <td><input type="password" maxlength="32" name="password" /></td>

                    <tr><td>Re-enter Password</td>
                    <td><input type="password" maxlength="32" name="password_confirm" /></td>

                    </tr><tr><td colspan="2" align="center"><input type="submit" name="signup" value="Signup" /></td> </tr>
                </form>
            </table>
            <script src="validate.js"></script>  
        </body>
    </html>
_END;
?>