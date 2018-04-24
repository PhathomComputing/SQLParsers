<?php
  require_once $_SERVER['DOCUMENT_ROOT'].'/core/init.php';
  $mode = sanitize($_POST['mode']);
  $user_id = sanitize($_POST['user_id']);
  if($mode == 'removeone'){
    $userQ = $db->query("UPDATE users SET permissions = (permissions-1) WHERE id= '{$user_id}'");
  }elseif($mode == 'addone'){
    $userQ = $db->query("UPDATE users SET permissions = (permissions+1) WHERE id= '{$user_id}'");
  }
  if($userQ){
    // $_SESSION['success_flash'] = 'User permissions updated! // '."UPDATE users SET permissions = (permissions-1) WHERE id= '{$user_id}'";
  }
?>
