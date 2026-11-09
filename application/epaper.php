<script type="text/javascript" src="js/jquery.1.5.1.min.js"></script>
<script type="text/javascript" src="js/jquery.colorbox-00.js"></script>
<link media="screen" rel="stylesheet" href="css/colorbox.css" type="text/css"  />
<style>
.img_epaper{
	position:absolute;
	background-color:#E99900;
	visibility: visible;
	padding:0px;
	opacity:0;
	cursor:pointer;
	filter:alpha(opacity=0)
}

img{border:none;}

ul,li{
	margin:0;
	padding:0;
}
li{
	list-style:none;
	float:left;
	display:inline;
	margin-right:10px;
}

/*** Tooltip ***/
#tooltip{
	position:absolute;
	border:1px solid #006BE4;
	background:#095BB6/*#f7f5d1*/;
	padding:2px 5px;
	color:#fff;
	display:none;
	font-size:14px;
	font-family:SolaimanLipi;
	-moz-border-radius:4px;
	-webkit-border-radius:4px;
	border-radius:5px;
	-webkit-box-shadow:1px 1px 6px #ddd;
	-moz-box-shadow:1px 1px 6px #ddd;
	box-shadow:1px 1px 6px #ddd;
	}
/*** Tooltip ***/
</style>
<script type="text/javascript">
	function mouse_over_opacity(elem){
		elem.style.opacity= 0.2;
		elem.style.filter="alpha(opacity=20)";
	}

	function mouse_out_opacity(elem){
		elem.style.opacity= 0;
		elem.style.filter="alpha(opacity=0)";
	}

	function mouse_onclick(elem){
		//alert(elem.id);
		window.scrollTo(0, 0);
		//var tmp = elem.id;
		//var content = tmp.substr(2);
		var content = elem.id;
		//alert(content);
		//var mypage = "displaypage.php?id=" + content;
		var imageURL = "pop_up.php?img_name=" + content;
		$.colorbox({iframe:true,href:imageURL});
		//$.colorbox({ innerWidth: "850px", innerHeight: "550px", iframe: true, transition: "elastic", opacity: .6, open: true, href: imageURL });
		//document.getElementById('wc_timer').style.display = 'block';
	}
</script>
<?php

	$pub ='data/'.str_replace("_","/",$_GET['date']);
	$path = ''.$pub.'/';

	$page_num = $_GET['page_num'];

	if (!file_exists($path."pages/page".$page_num.".xml"))
		die();
?>
<div style="position:relative; border:1px solid #E2E2E2; width:680px; height:1050px; overflow:hidden">
	<div id="gallery_" class="clearfix">

		<?php
			if (!file_exists($path."title.php"))
				include("headline.php");

			include($path."title.php");

			@$xml = simplexml_load_file($path."pages/page".$page_num.".xml");

			if (file_exists($path.'pages/page'.$page_num.'.jpg'))
			{
				echo '<span class="img_epaper_"';
				echo ' style="';
				echo ' left:0px;';
				echo ' top:0px">';
				echo '<a>';
				echo '<img src="'.$path.'pages/page'.$page_num.'.jpg" border="0"/>';//style="visibility:hidden"
				echo '</a>';
				echo '</span>';
			}

			$j = 0;

			foreach($xml->children() as $child_root)
			{
				if (count($child_root) > 0)
				{
					$i = 0;
					foreach($child_root->children() as $child_value)
					{
						if ($i != 0)
							$child_child[$i] = (int)($child_value * 2);
						else
							$child_child[$i] = $child_value;
						$i = $i + 1;
					}
					$img_name = str_replace(".jpg", "", $child_child[0]);

					if (file_exists($path.'images/'.$img_name.'.jpg'))
					{
						echo '<div  class="img_epaper" id="'.$path.'images/'.$img_name.'.jpg"';
						echo 'style="
							  left:'.$child_child[1].'px;
							  top:'.$child_child[2].'px;
							  width:'.$child_child[3].'px;
							  height:'.$child_child[4].'px;
							  z-index:'.($j++).'"';
						echo 'onmouseover="javascript:mouse_over_opacity(this)"
							  onmouseout="javascript:mouse_out_opacity(this)"
							  onclick="javascript:mouse_onclick(this)">';
						echo '</div>';
					}
				}
			}
		?>

	</div>

<!-- Third-party AdSense integration omitted from this portfolio copy. -->

</div>
<div style="clear:both; height:5px"></div>