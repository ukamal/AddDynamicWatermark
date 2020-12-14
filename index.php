<?php

//index.php

$connect = new PDO("mysql:host=localhost;dbname=testing", "root", "");

$message = '';

if(isset($_POST["upload"]))
{
  if(!empty($_FILES["select_image"]["name"]))
  { 
    $extension = pathinfo($_FILES["select_image"]["name"],PATHINFO_EXTENSION);
    
    $allow_extension = array('jpg','png','jpeg');

    $file_name = uniqid() . '.' . $extension;

    $upload_location = 'upload/' . $file_name;

    if(in_array($extension, $allow_extension))
    {
      $image_size = $_FILES["select_image"]["size"];
      if($image_size < 2 * 1024 * 1024)
      {
        if(move_uploaded_file($_FILES["select_image"]["tmp_name"], $upload_location))
        { 
          
          $watermark_image = imagecreatefrompng('round-logo.png');
          if($extension == 'jpg' || $extension == 'jpeg')
          {
            $image = imagecreatefromjpeg($upload_location);
          }

          if($extension == 'png')
          {
            $image = imagecreatefrompng($upload_location);
          }

          $margin_right = 10; 
          $margin_bottom = 10;

          $watermark_image_width = imagesx($watermark_image); 
          $watermark_image_height = imagesy($watermark_image);  

          imagecopy($image, $watermark_image, imagesx($image) - $watermark_image_width - $margin_right, imagesy($image) - $watermark_image_height - $margin_bottom, 0, 0, $watermark_image_width, $watermark_image_height); 

          imagepng($image, $upload_location); 

          imagedestroy($image);
          if(file_exists($upload_location))
          { 
            $message = "Image Uploaded with Watermark";
            $data = array(
              ':image_name'   =>  $file_name
            );
            $query = "
            INSERT INTO images_table 
            (image_name, upload_datetime) 
            VALUES (:image_name, now())
            ";
            $statement = $connect->prepare($query);
            $statement->execute($data);
          }
          else
          { 
            $message = "There is some error, try again";
          }
        }
        else
        {
          $message = "There is some error, try again";
        }
      }
      else
      {
        $message = "Selected Image Size is very big";
      }      
    }
    else
    {
      $message = 'Only .jpg, .png and .jpeg image file allowed to upload';
    }
  }
  else
  { 
    $message = 'Please select Image';
  } 
}

$query = "
SELECT * FROM images_table 
ORDER BY image_id DESC
";

$statement = $connect->prepare($query);

$statement->execute();

$result = $statement->fetchAll();



?>

<!DOCTYPE html>
<html>
  <head>
    <title>Dynamically Add Watermark to Image using PHP for Appcilious Pvt Ltd</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
  </head>
  <body>
    <br />
    <div class="container">
      <h3 align="center">Dynamically Add Watermark for Appcilious Pvt Ltd</h3>
      <br />
      <?php
      if($message != '')
      {
        echo '
        <div class="alert alert-info">
        '.$message.'
        </div>
        ';
      }
      ?>
      <div class="panel panel-default">
        <div class="panel-heading">Add Wartermark to an Image</div>
        <div class="panel-body">
          <form method="post" enctype="multipart/form-data">
            <div class="row">
              <div class="form-group">
                <label class="col-md-6" align="right">Select Image</label>
                <div class="col-md-6">
                  <input type="file" name="select_image" />
                </div>
              </div>              
            </div>
            <br />
            <div class="form-group" align="center">
              <input type="submit" name="upload" class="btn btn-primary" value="Upload" />
            </div>
          </form>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">Uploaded Image with Watermark</div>
        <div class="panel-body" style="height: 700px;overflow-y: auto;">
          <div class="row">
          <?php
          foreach($result as $row)
          {
            echo '
            <div class="col-md-2" style="margin-bottom:16px;">
              <img src="upload/'.$row["image_name"].'" class="img-responsive img-thumbnail"  />
            </div>
            ';
          }
          ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
