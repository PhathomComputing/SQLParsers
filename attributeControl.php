<?php
$post = [];
    foreach($_POST as $key => $val){
		//echo $key."->".$val." ";
        $post[$key] = $val;

    }


	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/init.php';


//==========================================================================
	if($post['type'] == "save"){
		$count=0;
		foreach($post['attr_id'] as $id){
			$query =<<<EOF
UPDATE product_attributes SET attr_name = '{$post["attr_name"]}', attr_child = '{$post["attr_child"]}', child_number = {$count}, attr_value = '{$post["attr_value"][$count]}', attr_notes = '{$post["attr_notes"][$count]}', attr_visible = {$post["attr_visible"]}, use_for_variations = {$post["attr_use_var"]} WHERE attr_id = {$id}
EOF;

		if($db->query($query)){
			echo "Saved Variations Successfully!";
		} else {
			echo "An error occured with query: </br>";
			echo json_encode(['ok' => false, 'error' => $db->error_list, 'query' => $query]);
		}
	$count++;
		}
	}//==========================================================================
	elseif($post['type'] == "add-child"){

	$query =<<<EOF
	INSERT INTO product_attributes (`product_id`,`attr_name`,`attr_child`,`child_number`,`attr_visible`,`use_for_variations`) VALUES ({$post["product_id"]},'{$post["attr_name"]}','{$post["attr_child"]}',{$post["child_number"]},{$post["attr_visible"]},{$post["attr_use_var"]})
EOF;
	if($db->query($query)){
		$data["id"] = $db->insert_id;

			echo json_encode($data);
	} else {
			echo "An error occured with query: </br>";
			echo json_encode(['ok' => false, 'error' => $db->error_list, 'query' => $query]);
	}

	}//==========================================================================
	elseif($post['type'] == "add-parent"){

	$query =<<<EOF
	INSERT INTO product_attributes (`product_id`) VALUES ({$post["product_id"]})
EOF;
	if($db->query($query)){
		$data["id"] = $db->insert_id;

			echo json_encode($data);
	} else {
			echo "An error occured with query: </br>";
			echo json_encode(['ok' => false, 'error' => $db->error_list, 'query' => $query]);
	}

	}//==========================================================================
	elseif($post['type']=="remove-child"){
		$query=<<<EOF
		DELETE FROM `product_attributes` WHERE `product_attributes`.`attr_id` = {$post['id']}
EOF;
		if($db->query($query)){
				echo "Removed Attributes Successfully!";
		} else {
				echo "An error occured with query: </br>";
				echo json_encode(['ok' => false, 'error' => $db->error_list, 'query' => $query]);
		}
	}//==========================================================================
	elseif($post['type']=="remove"){
		$prodID=$post['prodID'];
		$db->query("DELETE FROM product_attributes WHERE attr_id = {$prodID}");
	}//==========================================================================
	elseif($post['type']=="refresh"){
				$htmlContent = '';
        $variations = $db->query("SELECT * FROM product_variations WHERE product_id = ".$post['prodID']);
        $i = 0;
        $parseVariations = $db->query("SELECT * FROM product_variations WHERE product_id = ".$post['prodID']);
        while($vars = mysqli_fetch_assoc($variations))
        {
            $variation_details = json_decode($variation['variation_name']);

            if($variations->num_rows > 0)
						{
  						$parsedVars =[];
  						$varCount = 0;
  						while($curVar = mysqli_fetch_assoc($parseVariations))
  						{



								$var_details = json_decode($curVar['variation_name']);
								//print_r($var_details);
								//array_push($parsedVars,$curVar['variation_name']);
								//echo $curVar['variation_name']."</br>";
								$attrToParse = $db->query("SELECT * FROM product_attributes WHERE product_id = ".$post['prodID']." AND use_for_variations = 1");
								$attrQueue = array();

								$htmlContent .=' &nbsp;
                <div class="variation-control list-group-item variation-config-group variation-config-group-<?=$varCount?>" ><div class="col-md-10 variation-container"><input type="hidden" value="'.$curVar["variation_id"].'" id="variation-style-id-'.$varCount.'">
                ';


								while($parseAttr = mysqli_fetch_assoc($attrToParse))
  							{
									  $attrName = $parseAttr['attr_name'];
									  $queueSize = sizeof($queueSize);
									  $index = sizeof($attrQueue['attr_name']);
									  if(!isset($attrQueue[$attrName])) {
						          $attrQueue[$attrName] =  [$index => $parseAttr['attr_value']];
									  }else{
					            array_push($attrQueue[$attrName], $parseAttr['attr_value']);
									  }
								}

				      $selectCounter=0;
							foreach($attrQueue as $header => $value)
							{
								//print_r($value);
								//print($var_details[$selectCounter]);
								$htmlContent.='<div class="col-md-2 select-container"><select class="variation-select form-control input-sm var-config-select var-config-'.$varCount.'-'.$selectCounter.'"><option value="'.$header.'">'.$header.'</option>
								';


								$vals = sizeof($value);
								for($v=0;$v<$vals;$v++)
								{

									//echo compareVariations($value[$v],$var_details[$selectCounter]);
										$option = '<option ';
										if(compareVariations($value[$v],$var_details[$selectCounter]))
									  {
											$option .= 'selected';
										}
										$option .=' class="var-opt-'.$selectCounter.'-'.$v.'" value="'.$value[$v].'">'.$value[$v].'</option>';
										$htmlContent.= $option;
								}
								$htmlContent.='
								</select>
								</div>';


				        $selectCounter++;
				      }



							$htmlContent.='
							<a style="margin-left:20px;margin-top:20px;" onclick="toggleMenu('.$varCount.')" role="button"   aria-expanded="true" aria-controls="product-variations-detail-'.$varCount.'">Variation Details</a>
							';



							$htmlContent.='</div><div class="col-md-2"><a href="javascript:;" class="btn btn-info btn-sm edit-variation" data-variation-id="'.$variation['variation_id'].'"><span class="glyphicon glyphicon-edit"> </span></a>
							<a href="javascript:;" class="btn btn-danger btn-sm remove-variation" data-variation-id="'.$variation['variation_id'].'"><span class="glyphicon glyphicon-remove"></span></a></div><div class="clearfix"></div></div>';



              $htmlContent.='



							<div id="#product-variations-detail-container" class="col-md-12">
							<div class="panel-group-var" id="vars-container" role="tablistvars" aria-multiselectable="false">
						  <div class="panel panel-default">
							<div id="product-variations-detail-'.$varCount.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="product-variations-detail-'.$varCount.'">

              ';

							$vid = $curVar['variation_id'];
							$variationQuery = mysqli_fetch_object($db->query("SELECT * FROM product_variations WHERE variation_id = {$vid}"));
        			$htmlContent.='
              <div class="modal-dialog modal-lg">
									<form id="variation-detail-edit-'.$varCount.'" enctype="multipart/form-data">
										<div class="modal-content">
											<div class="modal-header">

												<h4 class="modal-title"><span class="glyphicon glyphicon-file"></span> Edit variation:'.ucwords(implode(" ", unserialize($variationQuery->variation_name))).'</h4>
											</div>
											<div class="modal-body">
												<div id="variation-message" class="alert hidden"></div>
												<input type="hidden" name="variation_id" value="'.(   $variationQuery->description ? $variationQuery->description : "").'">
												<input type="hidden" name="product_id" value="'.$variationQuery->product_id.'">
												<div class="row">
													<div class="col-md-6 col-md-offset-6">
														<div class="form-group">
															<label for="product-sku">SKU</label>
															<input type="text" class="form-control product-sku" id="product-sku-'.$varCount.'" name="sku" value="'. ($variationQuery->sku ? $variationQuery->sku : '') .'">
														</div>
													</div>
												</div>
												<hr class="margin-bottom-10">
												<div class="row">
													<div class="col-md-12">
														<label for="product-enable">
															<input type="checkbox" id="product-enable-'.$varCount.'" name="enabled" '.($variationQuery->enabled ? "checked" : "").'>
															Enable
														</label>
														&nbsp;
														<label for="product-downloadable">
															<input type="checkbox" id="product-downloadable-'.$varCount.'" name="downloadable" '.( $variationQuery->downloadable ? "checked" : "").'>
															Downloadable
														</label>
														&nbsp;
														<label for="product-virtual">
															<input type="checkbox" id="product-virtual-'.$varCount.'" name="virtual" '.( $variationQuery->virtual ? "checked" : "").'>
															Virtual
														</label>
														&nbsp;
														<label for="product-manage-stock">
															<input type="checkbox" id="product-manage-stock-'.$varCount.'" name="manage_stock" '.($variationQuery->manage_stock ? "checked" : "").'>
															Manage Stock
														</label>
														&nbsp;
														<label for="product-default-variation">
															<input type="checkbox" id="product-default-variation-'.$varCount.'" name="default" '.($variationQuery->default_variation ? "checked" : "").'>
															Default Variation <small>(If theres another default it will be replaced as default by this variation)</small>
														</label>
													</div>
												</div>
												<hr class="margin-bottom-10">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label for="regular-price">Regular Price ($) </label>
															<input type="number" class="form-control" id="regular-price-'.$varCount.'" name="regular_price" placeholder="Variation price" value="'.( $variationQuery->regular_price).'" step="0.01">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label for="sale-price"><span class="text-danger">*</span> Sale Price ($)</label>
															<input type="number" class="form-control" id="sale-price-'.$varCount.'" name="sale_price" value="'.( $variationQuery->sale_price).'" step="0.01" required>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label for="stock-status">Stock status</label>
															<select class="form-control" id="stock-status-'.$varCount.'" name="in_stock">
																<option value="1" '.( $variationQuery->in_stock ? "selected" : "").'>In stock</option>
																<option value="0" '.(  !$variationQuery->in_stock ? "selected" : "").'>Out of stock</option>
															</select>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-6">
														<label for="weight">Weight (oz)</label>
														<input type="text" class="form-control" id="weight-'.$varCount.'" name="weight" value="'.(  $variationQuery->weight ? $variationQuery->weight : "").'">
													</div>
													<div class="col-md-6">
														<div class="row">
															<label class="col-md-12">Dimensions (L x W x H) (in)</label>
														</div>
														<div class="row">
															<div class="col-md-4">
																<input type="text" class="form-control" id="dimensions-length-'.$varCount.'" name="length" placeholder="Length" value="'.(   $variationQuery->length ? $variationQuery->length : "").'">
															</div>
															<div class="col-md-4">
																<input type="text" class="form-control" id="dimensions-width-'.$varCount.'" name="width" placeholder="Width" value="'.(   $variationQuery->width ? $variationQuery->width : "").'">
															</div>
															<div class="col-md-4">
																<input type="text" class="form-control" id="dimensions-height-'.$varCount.'" name="height" placeholder="Height" value="'.(   $variationQuery->height ? $variationQuery->height : "").'">
															</div>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label for="description">Description</label>
															<textarea name="description" id="description-'.$varCount.'" cols="30" rows="10" class="form-control">'.(   $variationQuery->description ? $variationQuery->description : "").'</textarea>
															(<span class="text-danger">*</span> Required)
														</div>
													</div>
												</div>
											</div>
											<div class="modal-footer">
											<span id="save-message-'.$varCount.'"></span>
												<input class="btn btn-primary" type="button" onclick="saveVars('.$varCount.','.$vid.')" value="Submit" id="save-variation-details">

											</div>
										</div>
									</form>
								</div>





								</div></div></div>
								</div>



                ';







                $htmlContent.='

                ';

                $varCount++;
              }//second while
		        }
           }
   echo json_encode($htmlContent);



	}
?>
