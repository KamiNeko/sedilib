<?php

header("Pragma:no-cache");
header("Cache-Control:private,no-store,no-cache,must-revalidate");

session_start();

error_reporting(E_ALL);

  require_once('seqlib/sequence_parser.php');
  
  $text = '';
  
  if (isset($_POST['clear_cache'])) {
      if ($handle = opendir('.')) {
      
	  while (false !== ($file = readdir($handle))) {
	      if (is_dir($file)) continue;
	      
	      $pathinfo = pathinfo($file);
	      $extension = $pathinfo['extension'];
	      if ($extension == 'png') {
		  unlink($file);
	      }
	  }
	  
	  echo '<div>Cleared Cache !</div>';

	  closedir($handle);
      }
      
      $text = $_POST['seqdia_code'];
      $text = str_replace("\'", "'", $text);
  }  
  
?>

<html>
  <head>
  </head>
  <body>  
  <script>
  
      var myVar = setInterval(function(){myTimer()}, 1000);
      
      function myTimer()
      {
	  clearInterval(myVar);
	  var xmlHttp = null;
	  try {
	      // Mozilla, Opera, Safari sowie Internet Explorer (ab v7)
	      xmlHttp = new XMLHttpRequest();
	  } catch(e) {
	      try {
		  // MS Internet Explorer (ab v6)
		  xmlHttp  = new ActiveXObject("Microsoft.XMLHTTP");
	      } catch(e) {
		  try {
		      // MS Internet Explorer (ab v5)
		      xmlHttp  = new ActiveXObject("Msxml2.XMLHTTP");
		  } catch(e) {
		      xmlHttp  = null;
		  }
	      }
	  }
	  if (xmlHttp) {
	      xmlHttp.open('POST', 'generate.php', true);
	      var sendValue = "seqdia_code=" + document.getElementById("seqdia_code").value;
	      sendValue = sendValue.replace(/\+/g, "!create!");
	      //Send the proper header information along with the request
	      xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	      xmlHttp.setRequestHeader("Content-length", sendValue.length);
	      xmlHttp.setRequestHeader("Connection", "close");

	      //xmlhttp.setRequestHeader('Content-Type', 'application/json');
	      
	      xmlHttp.onreadystatechange = function () {
		  if (xmlHttp.readyState == 4) {
		      //alert(xmlHttp.responseText);
		      var json = eval('(' + xmlHttp.responseText + ')');
		      
		      
		      document.getElementById("img").src = json.img;
		      if (json.errors != undefined)
			  document.getElementById("error").innerHTML = json.errors;
		  }
	      };
	     
	      xmlHttp.send(sendValue);
	      
	  }
	  
      }
      
      function resetTimer(e)
      {
	  clearInterval(myVar);
	  myVar = setInterval(function(){myTimer()}, 1000);
      }
</script>

  <form action="index.php" method='POST'>
    <p>Enter code for sequence diagram here:<br>
      <textarea id="seqdia_code" name="seqdia_code" onkeyup="resetTimer(event)" cols="50" rows="10"><?php echo $text;?></textarea>
    </p>
    <input name="clear_cache" type="submit" value=" Clear cached img "> 
  </form>
  <div id="error"></div>
  <img id="img" src="">
  
  </body>
</html>