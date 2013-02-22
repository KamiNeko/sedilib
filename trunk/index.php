<?php
  require_once('seqlib/sequence_parser.php');

  if (isset($_POST['submit'])) {
      $text = $_POST['seqdia_code'];
      $md5 = md5($text);
      $file_name = $md5.'.png';
      $errors = array();
      
      //if (!file_exists($file_name)) {
	  $parser = new SequenceParser($text, $errors);
	  $parser->generateImage($file_name);
      //}
      
      if (count($errors) > 0) {
	  foreach($errors as $error) {
	      echo 'ERROR: '.$error.'<br>';
	  }
      }
  }
?>

<html>
  <head>
  </head>
  <body>
  
  <form action="index.php" method='POST'>
    <p>Enter code for sequence diagram here:<br>
      <textarea name="seqdia_code" cols="50" rows="10"><?php echo $_POST['seqdia_code'];?></textarea>
    </p>
    <input name="submit" type="submit" value=" Send ">
  </form>
  
  <img src="<?php echo $file_name; ?>">
  </body>
</html>