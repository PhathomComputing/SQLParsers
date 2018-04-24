<?php


$post = [];
foreach($_POST as $key => $val){
    $post[$key] = empty($val) ? 0 :  htmlentities(trim($val));
}



require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/parsers/SKUmodules.php';




if ($_POST['mode']=="build") {
	buildSkus();
  echo "Combining SKU's Successful!";

}elseif($_POST['mode']=="generate"){
	genSkus();
  echo "Generate Successful!";
}


?>
=====>
<?php
?>
