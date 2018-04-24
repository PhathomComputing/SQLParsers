

<?php

 $post = [];
    foreach($_POST as $key => $val){
        $post[$key] = empty($val) ? 0 :  htmlentities(trim($val));
    }

// Fetching Values From URL
$vid = $post['vid'];
$sku = $post['sku'];
$enable =  $post['enable'];
$downloadable = $post['downloadable'];
$virtual = $post['virtual'];
$stock =  $post['stock'];
$variation = $post['variation'];
$regularPrice =  $post['regularPrice'];
$salePrice =  $post['salePrice'];
$stockStatus = $post['stockStatus'];
$weight = $post['weight'];
$length =  $post['length'];
$width = $post['width'];
$height = $post['height'];
$description =  nl2br($post['description']);
echo $stockStatus;

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/init.php';

$query = <<<EOF
UPDATE product_variations SET  enabled = {$enable}, downloadable = {$downloadable}, virtual = {$virtual}, manage_stock = {$stock}, regular_price = {$regularPrice}, sale_price = {$salePrice}, in_stock = {$stockStatus}, weight = {$weight}, height = {$height}, width = {$width}, length = {$length}, default_variation = {$variation}, description = '{$description}' WHERE variation_id = {$vid}
EOF;


    if($db->query($query)){
		echo "Saved Variations Successfully!";
    } else {
		echo "An error occured with query: </br>";
        echo json_encode(['ok' => false, 'error' => $db->error_list, 'query' => $query]);
    }


?>
