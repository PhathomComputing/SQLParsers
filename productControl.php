<?php $brandQuery = $db->query("SELECT * FROM brand ORDER BY brand");
$parentQuery = $db->query("SELECT * FROM categories WHERE parent = 0 ORDER BY category");
$title = ((isset($_POST['title']) && $_POST['title'] != '') ? sanitize($_POST['title']) : '');
$slug = ((isset($_POST['slug']) && $_POST['slug'] != '') ? sanitize($_POST['slug']) : urls_amigables(trim($title, '-')));
$brand = ((isset($_POST['brand']) && !empty($_POST['brand'])) ? sanitize($_POST['brand']) : '');
$parent = ((isset($_POST['parent']) && !empty($_POST['parent'])) ? sanitize($_POST['parent']) : '');
$category = ((isset($_POST['child']) && !empty($_POST['child'])) ? sanitize($_POST['child']) : '');
$saved_videos = ((isset($_POST['videos']) && !EMPTY($_POST['videos'])) ? sanitize($_POST['videos']) : $product['videos']);
$saved_videos = rtrim($saved_videos, ',');
$saved_videos = str_replace(' ', '', $saved_videos);
$saved_image = '';
$saved_archivos = '';
$attributes = $db->query("SELECT * FROM product_attributes WHERE product_id = {$_GET['edit']}");
$attributes_for_variations = $db->query("SELECT * FROM product_attributes WHERE product_id = {$_GET['edit']} AND use_for_variations = 1");
$variations = $db->query("SELECT * FROM product_variations WHERE product_id = {$_GET['edit']}");

if(isset($_GET['edit'])){
    $edit_id = (int)$_GET['edit'];
    $productresults = $db->query("SELECT * FROM products WHERE id = '$edit_id'");
    $product = mysqli_fetch_assoc($productresults);
    if(isset($_GET['delete_image'])){
        $image_id = sanitize($_GET['delete_image']);
        $image_query = $db->query(<<<EOF
SELECT * FROM product_images WHERE image_id = {$image_id};
EOF
        );
        $image = mysqli_fetch_assoc($image_query);
        $image_url = $_SERVER['DOCUMENT_ROOT'] . $image['image_url'];
        unlink($image_url);
        $db->query(<<<EOF
DELETE FROM product_images WHERE image_id = {$image_id};
EOF
        );
        header('Location: products.php?edit=' . $edit_id);
    }
    if(isset($_GET['delete_file'])){
        $file_id = sanitize($_GET['delete_file']);
        $file_query = $db->query(<<<EOF
SELECT * FROM product_files WHERE file_id = {$file_id};
EOF
        );
        $file = mysqli_fetch_assoc($file_query);
        $file_url = $_SERVER['DOCUMENT_ROOT'] . $file['file_url'];
        unlink($file_url);
        $db->query(<<<EOF
DELETE FROM product_files WHERE file_id = {$file_id};
EOF
        );
        header('Location: products.php?edit=' . $edit_id);
    }
    if(isset($_GET['delete_video'])){
        $video_id = sanitize($_GET['delete_video']);
        $video_query = $db->query(<<<EOF
SELECT * FROM product_videos WHERE video_id = {$video_id};
EOF
        );
        $video = mysqli_fetch_assoc($video_query);
        $video_url = $_SERVER['DOCUMENT_ROOT'] . $video['video_url'];
        unlink($video_url);
        $db->query(<<<EOF
DELETE FROM product_videos WHERE video_id = {$video_id};
EOF
        );
        header('Location: products.php?edit=' . $edit_id);
    }
    if(isset($_GET['tax'])){
        $tax = (int)$_GET['tax'];
        $taxsql = "UPDATE products SET tax = '$tax' WHERE id='$edit_id'";
        $db->query($taxsql);
        header('Location: products.php?edit=' . $edit_id);
    }
    $category = ((isset($_POST['child']) && !EMPTY($_POST['child'])) ? sanitize($_POST['child']) : $product['categories']);
    $title = ((isset($_POST['title']) && !EMPTY($_POST['title'])) ? sanitize($_POST['title']) : $product['title']);
    $slug = ((isset($_POST['slug']) && !EMPTY($_POST['slug'])) ? sanitize($_POST['slug']) : $product['slug']);
    $parentQ = $db->query("SELECT * FROM categories WHERE id ='$category'");
    $parentResult = mysqli_fetch_assoc($parentQ);
    $parent = ((isset($_POST['parent']) && !EMPTY($_POST['parent'])) ? sanitize($_POST['parent']) : $parentResult['parent']);
    $description = ((isset($_POST['description'])) ? sanitize($_POST['description']) : $product['description']);
	$notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : $product['notes'];
	$brand = isset($_POST['brand']) ? sanitize($_POST['brand']) : $product['brand'];
    $saved_videos = ((isset($_POST['videos']) && !EMPTY($_POST['videos'])) ? sanitize($_POST['videos']) : $product['videos']);
    $saved_videos = rtrim($saved_videos, ',');
    $saved_videos = str_replace(' ', '', $saved_videos);
    $saved_image = (($product['images'] != '') ? $product['images'] : '');
    $saved_archivos = (($product['archives'] != '') ? $product['archives'] : '');
    $dbpath = $saved_image;
    $dbpath2 = $saved_archivos;
    $editdbpath = $saved_image;
    $editdbpath2 = $saved_archivos;
}
//create producto
if($_POST){
    $errors = array();
    $required = array( 'title', 'parent', 'child' );
    $allowed = array( 'png', 'jpg', 'jpeg', 'gif' );
    $photoName = array();
    $tmpLoc = array();
    $uploadPath = array();
    foreach($required as $field){
        if($_POST[ $field ] == ''){
            $errors[] = 'All Fields With and Asterisk are required.';
            break;
        }
    }
    //images
    $product_images = [];
    $photoCount = count($_FILES['photo']['name']);
    if($photoCount > 0 && $_FILES['photo']['name'][0] != ""){
        for($i = 0; $i < $photoCount; $i++){
            $name = $_FILES['photo']['name'][ $i ];
            $nameArray = explode('.', $name);
            $fileName = $nameArray[0];
            $fileExt = $nameArray[1];
            $mime = explode('/', $_FILES['photo']['type'][ $i ]);
            $mimeType = $mime[0];
            $mimeExt = $mime[1];
            $tmpLoc[] = $_FILES['photo']['tmp_name'][ $i ];
            $fileSize = $_FILES['photo']['size'][ $i ];
            $uploadName = md5(microtime() . $i) . '.' . $fileExt;
            $uploadPath[] = BASEURL . 'images/products/' . $uploadName;
            if($mimeType != 'image'){
                $errors[] = 'The file must be an image.';
            }
            if(!in_array($fileExt, $allowed)){
                $errors[] = 'The photo must be a png, jpg, jpeg or gif.';
            }
            if($fileSize > 15000000){
                $errors[] = 'The file size must be under 15MB.';
            }
            if($fileExt != $mimeExt && ($mimeExt == 'jpeg' && $fileExt != 'jpg')){
                $errors[] = 'File extension do not match the file.';
            }

            $image = [
                'title' => $uploadName,
                'alt_text' => $uploadName,
                'image_url' => '/images/products/' . $uploadName
            ];
            array_push($product_images, $image);
        }
    }
    //files
    $fileCount = count($_FILES['archivo']['name']);
    $product_files = [];
    if($fileCount > 0 && $_FILES['archivo']['name'][0] != ""){
        for($i = 0; $i < $fileCount; $i++){
            $name = $_FILES['archivo']['name'][ $i ];
            $nameArray = explode('.', $name);
            $fileName = urls_amigables($nameArray[0]);
            $fileExt = $nameArray[1];
            $mime = explode('/', $_FILES['archivo']['type'][ $i ]);
            $mimeType = $mime[0];
            $mimeExt = $mime[1];
            $tmpLoc[] = $_FILES['archivo']['tmp_name'][ $i ];
            $fileSize = $_FILES['archivo']['size'][ $i ];
            $uploadName = "{$fileName}.{$fileExt}";
            $uploadPath[] = BASEURL . 'archives/' . $uploadName;
            $file = [
                'title' => $uploadName,
                'file_url' => '/archives/' . $uploadName
            ];
            array_push($product_files, $file);
        }
    }
    $saved_notes = isset($_POST['notes']) ? sanitize($_POST) : '';
    if(!empty($errors)){
        echo display_errors($errors);
    } else {
        //upload file and insert into database
        if(($photoCount + $fileCount) > 0){
            for($i = 0; $i < ($photoCount + $fileCount); $i++){
                move_uploaded_file($tmpLoc[ $i ], $uploadPath[ $i ]);
            }
        }
        $insertSql = <<<EOF
INSERT INTO products (`title`,`slug` ,`categories`,`brand`, `description`, `notes`)
          VALUES ('$title', '$slug', '$category', '$brand', '$description', '$notes')
EOF;
        if(isset($_GET['edit'])){
            $insertSql = <<<EOF
UPDATE products SET title = '$title', categories = '$category', brand = '$brand', description = '$description', notes = '$notes'
          WHERE id = '$edit_id'
EOF;
        }
        $db->query($insertSql);
        $edit_id = isset($_GET['edit']) ? $_GET['edit'] : mysqli_insert_id($db);
        if($photoCount > 0 && count($product_images) > 0){
            foreach($product_images as $product_image){
                $image_sql = <<<EOF
INSERT INTO product_images (`product_id`, `image_url`, `title`, `alt_text`) VALUES ($edit_id, '{$product_image['image_url']}', '{$product_image['title']}', '{$product_image['alt_text']}');
EOF;
                $db->query($image_sql);

            }
        }
        if($fileCount > 0 && count($product_files) > 0){
            foreach($product_files as $product_file){
                $file_sql = <<<EOF
INSERT INTO product_files (`product_id`, `file_url`, `title`) VALUES ($edit_id, '{$product_file['file_url']}', '{$product_file['title']}');
EOF;
                $db->query($file_sql);

            }
        }
        header('Location: products.php?edit=' . $edit_id);
    }
}