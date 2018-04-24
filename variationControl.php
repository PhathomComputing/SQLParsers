<?php

	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/init.php';





/*
if($_POST['type']=="modal")
{
// Fetching Values From URL
$vid = $_POST['vid'];
$sku = $_POST['sku'];
$enable =  $_POST['enable'];
$downloadable = $_POST['donwloadable'];
$virtual = $_POST['virtual'];
$stock =  $_POST['stock'];
$variation = $_POST['variation'];
$regularPrice =  $_POST['regularPrice'];
$salePrice =  $_POST['salePrice'];
$stockStatus = $_POST['stockStatus'];
$weight = $_POST['weight'];
$length =  $_POST['length'];
$width = $_POST['width'];
$height = $_POST['height'];
$description =  $_POST['description'];

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/init.php';

$query = <<<EOF
UPDATE product_variations SET sku = '{$sku}', enabled = {$enable}, downloadable = {$downloadable}, virtual = {$virtual}, manage_stock = {$stock}, regular_price = {$regularPrice}, sale_price = {$salePrice]}, in_stock = {$stockStatus}, weight = {$weight}, height = {$height}, width = {$width}, length = {$length}, default_variation = '{$variation}', description = '{$description}' WHERE variation_id = {$vid};
EOF;

    if($db->query($query)){
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => $db->error_list, 'query' => $query]);
    }
echo json_encode($vid);

echo "<h1>ajax: $sku</h1>";
}*/
if($_POST['type']=="save"){
	foreach($_POST['varPackage'] as $key => $value)
	{
		$varID = $value['varID'];
		$varName = json_encode($value['content']);
		echo $varName."-".$varID." ";
		if($varID=='add')
		{
			$prodID = $value['prodID'];
			$query= <<<QUERY
INSERT INTO product_variations (product_id, variation_name) VALUES ({$prodID}, '{$varName}')
QUERY;
		}else{
		$query =<<<QUERY
UPDATE product_variations SET variation_name = '{$varName}' WHERE variation_id = {$varID}
QUERY;
		}
		if($db->query($query)){
		//$data["id"] = $db->insert_id;

		//	echo json_encode($data);
		} else {
				echo "An error occured with query: </br>";
				echo json_encode(['ok' => false, 'error' => $db->error_list, 'query' => $query]);
		}
	}
}elseif($_POST['type']=='remove'){
	$query=<<<EOF
	DELETE FROM `product_variations` WHERE `product_variations`.`variation_id` = {$_POST['id']}
EOF;
	if($db->query($query)){
			echo "Removed Attributes Successfully!";
	} else {
			echo "An error occured with query: </br>";
			echo json_encode(['ok' => false, 'error' => $db->error_list, 'query' => $query]);
	}
}
?>
