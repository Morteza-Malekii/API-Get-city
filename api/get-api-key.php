<!-- Access for admin -->
<div>
    <h1>API Key</h1>
<form action="" method="post">
    <input type="text" name="email" placeholder="Email">
    <input type="password" name="password" placeholder="Password">
    <input type="submit" value="Get API Key">
    
</div>

<?php
    include_once ($_SERVER['DOCUMENT_ROOT'].'/My-project/Iran/loader.php');

    if($_SERVER['REQUEST_METHOD'] != 'POST')
        die();
    
    $email = $_POST['email'];
    $user = getUserByEmail($email);

    if(is_null($user))
        die('Email not found');
    $jwt = createJwtToken($user);
    echo "jwt token for $user->name is : <br> <textarea>$jwt</textarea>";

?>