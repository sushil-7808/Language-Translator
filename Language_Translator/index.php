<?php
    session_start();
    if(isset($_SESSION['loged_in_user'])){
        $userId=$_SESSION['loged_in_user'];
    }else{
        if(isset($_COOKIE["userlogin"])){
            header('location:login.php');
        }
    }
    

    //get the function.php file from resources
    require_once "functions.php";
    //to get user database
    require_once "database.php";  

    if($_SERVER['REQUEST_METHOD']=='POST'){
        
        //get username(tablename)
        function get_username($conn, $userId){
            $sql="SELECT username FROM finalexam1 WHERE id=:id";
            $sql=$conn->prepare($sql);
            $sql->bindParam(":id", $userId);
            if(!$sql->execute()){
                die("failed to get user username");
            }
            $username=$sql->fetch(PDO::FETCH_ASSOC);
            $username=$username['username'];    
            return $username;
        }
        function get_username_translation_model($conn,$userId){
            //get translation_model and username
            $sql="SELECT username,translation_model FROM finalexam1 WHERE id=:id";
            $sql=$conn->prepare($sql);
            $sql->bindParam(":id", $userId);
            if(!$sql->execute()){
                die("failed to get translation_model");
            }
            $translation_model=$sql->fetch(PDO::FETCH_ASSOC);
            $username=$translation_model['username'];
            $translation_model=$translation_model['translation_model'];
            if(!$translation_model){
                $translation_model="Spanish(Default)";
            }
            return array('username'=>$username,'translation_model'=>$translation_model);
        }
    
        if(isset($_POST['modelName'])){
            $username=get_username($conn, $userId);    
            $colName=testTextInput($_POST['modelName']);
            //edit table
            $sql="ALTER TABLE `$username` ADD `$colName` varchar(119) NOT NULL";                        
            $sql=$conn->prepare($sql);
            if(!$sql->execute()){
                echo "failed to create new model table";
            }
            else{
                $sql="ALTER TABLE `$username` DROP COLUMN `Spanish(Default)`";  
                $sql=$conn->prepare($sql);
                if(!$sql->execute()){
                 echo "failed to delete default model table";
                }
                $sql="DELETE FROM `$username`";  
                $sql=$conn->prepare($sql);
                if(!$sql->execute()){
                 echo "failed to create database";
                }
          
            }
            //update user record
            $sql="UPDATE finalexam1 SET translation_model=:tm WHERE id=:id";
            $sql=$conn->prepare($sql);
            $sql->bindParam(":tm",$colName);
            $sql->bindParam(":id",$userId);
            
            if(!$sql->execute()){
                echo "failed to update user record";
            }
        }

        if(isset($_POST['dictionarydata'])){
            $firstLevelArray=explode('..', $_POST['dictionarydata']);
            $dictionarydata=array();
            foreach($firstLevelArray as $i){
                $secondLevelArra=explode(',', $i);
                array_push($dictionarydata,$secondLevelArra);
            }

            //get translation_model and username
            $username=get_username_translation_model($conn,$userId)['username'];
            $translation_model=get_username_translation_model($conn,$userId)['translation_model'];
            
            foreach($dictionarydata as $data){
                if($data[0] && $data[1]){
                    $data1=testTextInput($data[0]);
                    $data2=testTextInput($data[1]);
                    $sql="INSERT INTO `$username` (english, `$translation_model`) VALUES (:eng, :trans)";
                    
                    $sql=$conn->prepare($sql);
                    $sql->bindParam(":eng", $data1);
                    $sql->bindParam(":trans", $data2);
                    if(!$sql->execute()){
                        die("1failed to update translation_model");
                    }
                }else{
                    echo "There was problem geting your information to the database <br/>Your textfile was probably not well structured";
                }
            }
            
        }

        if(isset($_POST['toTranslate'])){
            $toTranslate=explode(',', $_POST['toTranslate']);
            $translated=array();

            $username=get_username_translation_model($conn,$userId)['username'];
            $translation_model=get_username_translation_model($conn,$userId)['translation_model'];

            foreach($toTranslate as $data){
                $data=testTextInput($data);
                $sql="SELECT `$translation_model` FROM `$username` WHERE english=:data";
                $sql=$conn->prepare($sql);
                $sql->bindParam(":data", $data);
                if(!$sql->execute()){
                    die("failed to get translation");
                }
                $tranlateWord=$sql->fetch(PDO::FETCH_ASSOC);
                $tranlateWord=$tranlateWord[$translation_model];
                if($tranlateWord){
                    array_push($translated,$tranlateWord);
                }else{
                    $tranlateWord="Not in dictionary";
                    array_push($translated,$tranlateWord);
                }
            }


        }
        
    }

    $sql="SELECT username, translation_model FROM finalexam1 WHERE id=:id";
    $sql=$conn->prepare($sql);
    $sql->bindParam(":id", $userId);
    if(!$sql->execute()){
        die("failed to get user details");
    }
    $userDetails=$sql->fetch(PDO::FETCH_ASSOC);
    if($userDetails['translation_model']){
        $tranModel=$userDetails['translation_model'];
    }else{
        $tranModel="Spanish(Default)";
    }
  


$conn=null;
?>


<?php
    echo <<<_END
    <html>
        <head>
        <title>Lame Translator</title> 
        </head>
        <body>
_END;
?>




        <?php if(isset($userId)){ 
            echo <<<_END
            <form action="index.php" onsubmit="return choosefileform()"  method="post">
                    
                <p>Upload List:</p>
               <p> <input id="choosefile" type="file" accept=".txt" />
                <input type="hidden" name="dictionarydata" id="data" />
                <br/>
                <input type="submit" name="updateModel" value="Update model" id="updateModel"/> <br/>
            </form>

            <form action="index.php"  method="post">
                <p>input the word(s) to be translated to  $tranModel</p>
                <p>Multiple words should be separted by comma(,)</p>
                <input type="text" name="toTranslate"/>
                <input type="submit" value="Translate"/>
            </form>
_END;
        ?>

            <?php if(isset($toTranslate) && isset($translated)){ ?>
                <table>
                <tr><th>English word</th> <th>Translation(<?php echo $tranModel?>)</th></tr>
                    <?php
                        $index=0;
                        foreach($toTranslate as $data){
                            echo "<tr><td>".$data."</td><td>".$translated[$index]."</td></tr>";
                            $index++;
                        }
                    
                    ?>
                </table>

            <?php } ?>



            <?php  if(!$userDetails['translation_model']){ 
                echo <<<_END
                <p>You have not set your model yet, you can set it now</p>
                <form method="post" action="index.php">
                    English to <input type="text" name="modelName"/>
                    <input type="submit" value="Set">
                </form>                
_END;
            } 
            ?>

                <a href="logout.php">Logout?</a>

        <?php }else{//user is loged out 
            echo <<<_END
            <a href='login.php'>Login</a> or <a href='signup.php'>Signup</a>  
_END;
         } ?>

<?php
    echo <<<_END
            <script src="validate.js"></script>
        </body>
    </html>
_END;
?>




