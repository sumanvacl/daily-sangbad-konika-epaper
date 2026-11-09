<script type="text/javascript" src="js/jquery00.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		window.print();
		window.close();
	});
</script>
<div align="center">
<?php
	$img_name=$_GET['img_name'];
	$img_list=explode('/',$img_name);
	list($width,$height)=@getimagesize($img_name);
	echo "<div style='width:auto; margin-top:10px' align='center'>";
	echo "<img style='border:1px solid #ccc; padding:5px' src='$img_name' width=$width height=$height border='0' />";
	echo "</div>";
?>
</div>