<?php


  function testTextInput($data){
    // this function is to check text input for html special charcters, slaches
    // extra speces for removal. and also conver the input to lower case
    
    //removes back slaches
    $data=stripslashes($data);
    //remove extra space
    $data=trim($data);
    //convert to lowercase
    $data=strtolower($data);
    //converts special html characters to html entities
    $data=htmlspecialchars($data);
    return $data;
  }//function testTextInput($data) ends here


  function testPasswordInput($data){
    // this function is to check text input for html special charcters
    //and slaches for removal
    $data=stripslashes($data);
    $data=htmlspecialchars($data);
    return $data;
  }//function testPasswordInput($data) ends here

  function mysql_fatal_error()
{
    echo <<< _END
    <html><body> <img src = "https://s3-us-west-2.amazonaws.com/s.cdpn.io/29841/dog.jpg" alt="A black, brown, and white dog wearing a kerchief"> </body></html>
_END;
}

$conn=null;
?>