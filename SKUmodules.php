<?php


//------------------------------------------------------------------------------------------------generate sku parts
function genSkuPart($type){
	$skuMatrix = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","2","3","4","5","6","7","8","9");
	if($type == "parent") {
		do{
			$skuRand = array_rand($skuMatrix,1);
			$catSku= $skuMatrix[$skuRand];
		}while(checkSkuPart("cat",$catSku));
			return $skuMatrix[$skuRand];
	}
	elseif($type =="child") {
		do{
			$skuRand = array_rand($skuMatrix,2);
			$skuChildPart;
			foreach($skuRand as $part){
				$skuChildPart.=$skuMatrix[$part];
			}
		}while(checkSkuPart("child",$skuChildPart));
		return $skuChildPart;
	}
	elseif($type =="brand"){
		do{
			$skuRand = array_rand($skuMatrix,2);
			$skuBrandPart;
			foreach($skuRand as $part){
				$skuBrandPart.=$skuMatrix[$part];
			}
		}while(checkSkuPart("child",$skuBrandPart));
		return $skuBrandPart;
	}
	elseif($type == "attribute"){
		do{
			$skuRand = array_rand($skuMatrix,3);
			$skuAttrPart;
			foreach($skuRand as $part){
				$skuAttrPart.=$skuMatrix[$part];
			}
		}while(checkSkuPart("child",$skuAttrPart));
		return $skuAttrPart;
	}
}



//------------------------------------------------------------------------------------------------build Skus
function buildSkus(){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/init.php';


		$parentId=$post["parentId"];
		$parentSku=$post["parentSku"];
		$query=<<<EOF
		SELECT * FROM product_variations
EOF;
		$result = $db->query($query);
		while($prodVar = mysqli_fetch_assoc($result)){
			$varName = json_decode($prodVar['variation_name']);
			$skuContainer;

			$prodID = $prodVar['product_id'];
			$grandQuery=<<<QUERY
			SELECT * FROM products WHERE brand = 1
QUERY;
			$grandResult= $db->query($grandQuery);
			$grandResult = mysqli_fetch_assoc($grandResult);
			$grandCat = $grandResult['categories'];
			$grandSkuPart = $grandResult['skuPart'];
			$catQuery =<<<QUERY
			SELECT * FROM categories WHERE id = $grandCat
QUERY;

			$catResult = $db->query($catQuery);
			$catArray = mysqli_fetch_assoc($catResult);
			$parentID=$catArray['parent'];
			$parentQuery =<<<QUERY
			SELECT * FROM categories WHERE id = $parentID
QUERY;
			$parentResult = $db->query($parentQuery);
			$parentArray = mysqli_fetch_assoc($parentResult);
			$skuContainer=$parentArray['skuPart'];
			$skuContainer.= $catArray['skuPart'];
			$skuContainer.=$grandSkuPart;

			$attrSku;
			foreach($varName as $key => $value){
				$attrQuery=<<<QUERY
				SELECT skuPart FROM product_attributes WHERE attr_value = "{$value}"
QUERY;
				$attrResult = $db->query($attrQuery);

				if($attrResult->num_rows>0){
						$attrSkuPart = mysqli_fetch_assoc($attrResult);
						$attrSku.= $attrSkuPart["skuPart"];
				}
			}
			$skuContainer .= $attrSku;
			$varID=$prodVar['variation_id'];

			$updateQuery = <<<QUERY
			UPDATE product_variations SET sku = '$skuContainer' WHERE variation_id ='$varID'
QUERY;
			$db->query($updateQuery);
			unset($skuContainer);
			unset($attrSku);



		}
}

//------------------------------------------------------------------------------------------------Check Skus
function checkSkuPart($type,$skuPart)
{
		$dbcheck = mysqli_connect('localhost', 'dbconnect123', 'Number2017', 'web_ecommerce');
	if(mysqli_connect_errno()){
    echo 'Database connection fail with following errors: ' . mysqli_connect_error();
    die();
}


	if($type == "cat"){

		$parentSku=$skuPart;
		$query=<<<EOF
		SELECT id FROM categories WHERE skuPart = '{$parentSku}'
EOF;
		$result = $dbcheck->query($query);
		if($result->num_rows != 0)
		{
			echo "Found Parents Sku...";
			return true;
		}
		else
		{
			echo "Sku not found...";
			return false;
		}
	}//===============================================================
	elseif($type == "prod"){
		$childSku=$skuPart;
		$query=<<<EOF
		SELECT id FROM products WHERE skuPart = '{$childSku}'
EOF;
		$result = $dbcheck->query($query);
		if($result->num_rows != 0)
		{
			echo "Found Parents Sku";
			return true;
		}
		else
		{
			echo "Sku not found!";
			return false;
		}


	}elseif($type == "brand"){
		$brandSku=$skuPart;
		$query=<<<EOF
		SELECT id FROM products WHERE skuPart = '{$brandSku}'
EOF;
		$result = $dbcheck->query($query);
		if($result->num_rows != 0)
		{
			echo "Found Parents Sku";
			return true;
		}
		else
		{
			echo "Sku not found!";
			return false;
		}


	}//===============================================================
	elseif($type == "attr"){

		$attrSku=$skuPart;
		$query=<<<EOF
		SELECT attr_id FROM product_attributes WHERE skuPart = '{$attrSku}'
EOF;
		$result = $dbcheck->query($query);
		if($result->num_rows != 0)
		{
			echo "Found Parents Sku";
			return true;
		}
		else
		{
			echo "Sku not found!";
			return false;
		}
	}//===============================================================
}

//function checkSkus($parentSku){
//}


//------------------------------------------------------------------------------------------------Combine





//------------------------------------------------------------------------------------------------generate
function genSkus(){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/init.php';
	//==============================================================Category Skus
	$query=<<<EOF
	SELECT id FROM categories WHERE (parent = 0  AND skuPart = '')
EOF;
	$result = $db->query($query);
	if($result->num_rows == 0){
		echo "All have SKUs!";
	}
	else
	{
		echo "Sku not found for ".$result->num_rows;
		while ($row = $result->fetch_assoc()){
			$skuParentPart = genSkuPart("parent");
			$query=<<<EOF
			UPDATE categories SET skuPart = '{$skuParentPart}' WHERE id = {$row['id']}
EOF;
			$db->query($query);
		}
	}
	$query=<<<EOF
	SELECT id FROM categories WHERE (parent = 2  AND skuPart = '')
EOF;
	$result = $db->query($query);
	if($result->num_rows == 0){
		echo "All have SKUs!";
	}
	else
	{
		echo "Sku not found for ".$result->num_rows;
		while ($row = $result->fetch_assoc()){
			$skuChildPart = genSkuPart("child");
			$query=<<<EOF
			UPDATE categories SET skuPart = '{$skuChildPart}' WHERE id = {$row['id']}
EOF;
			echo $query;
			$db->query($query);
		}
	}
	//==============================================================Title Skus
	$query=<<<EOF
	SELECT id FROM products WHERE (skuPart = '')
EOF;
	$result = $db->query($query);
	if($result->num_rows == 0){
		echo "All have SKUs!";
	}
	else
	{
		echo "Sku not found for ".$result->num_rows;
				while ($row = $result->fetch_assoc()){
				$skuTitlePart = genSkuPart("brand");
				$query=<<<EOF
				UPDATE products SET skuPart = '{$skuTitlePart}' WHERE id = {$row['id']}
EOF;
				echo $query;
				$db->query($query);
			}
	}
	//==============================================================Attributes Skus
	$query=<<<EOF
	SELECT attr_id FROM product_attributes WHERE (skuPart = '')
EOF;
	$result = $db->query($query);
	if($result->num_rows == 0){
		echo "All have SKUs!";
	}
	else
	{
		echo "Sku not found for ".$result->num_rows;
		while ($row = $result->fetch_assoc()){
				$skuAttributesPart = genSkuPart("attribute");
				$query=<<<EOF
				UPDATE product_attributes SET skuPart = '{$skuAttributesPart}' WHERE attr_id = {$row['attr_id']}
EOF;
				echo $query;
				$db->query($query);
			}
	}



}

?>
