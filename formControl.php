<?php
echo "In formControl";
echo $_POST['parent'];
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/init.php';
	if(isset($_POST['slug'])){
		$slug=$_POST['slug'];
		$prodID = $_POST['prodID'];
		$query = <<<QUERY
		UPDATE products SET slug = '$slug' WHERE id='$prodID'
QUERY;
		$db->query($query);
	}elseif(isset($_POST['select'])){
		if($_POST['select']=="brand"){
			echo "branding";
			$brand=$_POST['item'];
			$prodID = $_POST['prodID'];
			$query = <<<QUERY
			UPDATE products SET brand = '$brand' WHERE id='$prodID'
QUERY;
			$db->query($query);
		}elseif($_POST['select']=="child"){
			echo "InChild";
			$category=$_POST['item'];
			$prodID = $_POST['prodID'];
			$query = <<<QUERY
			UPDATE products SET categories = '$category' WHERE id='$prodID'
QUERY;
			$db->query($query);

		}


			//=====
	}


?>
