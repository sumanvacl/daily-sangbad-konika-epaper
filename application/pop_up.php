<?php
	if(isset($_GET['img_name']))
	{
		$cur_url = 'http://www.esangbadkonika.com'.$_SERVER['REQUEST_URI'];
		$img_list=explode('/',$img_name);

		list($width,$height)=@getimagesize($img_name);

		$img_list[0] = filter_var($img_list[0],FILTER_SANITIZE_NUMBER_INT);
		$img_list[1] = filter_var($img_list[1],FILTER_SANITIZE_NUMBER_INT);
		$img_list[2] = filter_var($img_list[2],FILTER_SANITIZE_NUMBER_INT);
		if($img_list[1]<10) $img_list[1]='0'.(int)$img_list[1];
		if($img_list[2]<10) $img_list[2]='0'.(int)$img_list[2];

		$img_list = explode('/',str_replace('data/','',$_GET['img_name']));

		$imgPath = 'data/'.$img_list[0]."/".$img_list[1]."/".$img_list[2]."/".$img_list[3];
		$img_name = $imgPath."/".$img_list[4];

		$getImg=$img_list[4];
		$imgId=explode('.',$getImg);

		$path = 'data/'.$img_list[0]."/".$img_list[1]."/".$img_list[2];

		$xml_path='data/'.$img_list[0]."/".$img_list[1]."/".$img_list[2]."/XML.xml";
		$img_one='';$img_two='';
		if(file_exists($xml_path))
		{
			$xml = simplexml_load_file($xml_path);
			$imgTag = 'news_'.$imgId[0];
			$pub_no=$xml->$imgTag->pub_id;
			$cat_id=$xml->$imgTag->cat_id;
			$menu_id=$xml->$imgTag->menu_id;
			$news_id=$xml->$imgTag->news_id;
			$img_one=$xml->$imgTag->image01;
			$img_two=$xml->$imgTag->image02;
		}
	}
	function socialShare($hl,$url)
	{
		echo '<ul>';
			echo '<li><a class="fshare" url="'.urlencode($url).'"><span class="facebook"></span></a></li>';
			echo '<li><a class="tshare" url="'.urlencode($hl).'<::>'.urlencode($url).'"><span class="twitter"></span></a></li>';
			//echo '<li><a href="#"><span class="comment"></span></a></li>';
		echo '</ul>';
	}
?>
<link rel="image_src" href="<?php echo @$img_name ?>" />
<link media="screen" rel="stylesheet" href="css/round.css" type="text/css"  />
<style type="text/css">
	body{
		font-family:SolaimanLipi;
		font-size:13px
	}
	.menu_opt span{
		display:inline-block;
		padding:5px;
		border:1px solid #eee;

		-moz-box-shadow: 0px 0px 5px #666;
		-webkit-box-shadow: 0px 0px 5px #666;
		box-shadow: 0px 0px 5px #666;

		/* For IE 8*/
		-ms-filter: "progid:DXImageTransform.Microsoft.Shadow(Strength=5, Direction=0, Color='#666')";

		/*For IE 5.5 - 7 */
		filter: progid:DXImageTransform.Microsoft.Shadow(Strength=5, Direction=0, Color='#666');
	}
	div.eSocialShare span{
		display:inline-block;
		border:1px solid #ccc;
		background:#f5f5f5 url(images/small-social-i.png) no-repeat;
		width:10px;
		height:10px;
		padding:3px;
		cursor:pointer
	}
	div.eSocialShare span.fshare{
		background-color:#f5f5f5;
		background-position:-1px -3px;
	}
	div.eSocialShare span.tshare{
		background-color:#f5f5f5;
		background-position:-25px -3px;
		width:13px;
	}
	div.eSocialShare span.gshare{
		background-color:#f5f5f5;
		background-position:-51px -3px;
		width:13px;
	}

	div.eSocialShare span.fshare:hover{
		background-color:#464646;
		background-position:-1px -26px;
	}
	div.eSocialShare span.tshare:hover{
		background-color:#464646;
		background-position:-25px -26px;
	}
	div.eSocialShare span.gshare:hover{
		background-color:#464646;
		background-position:-51px -26px;
	}
	div#shareCaption{
		font-family:Verdana, Geneva, sans-serif;
		font-size:12px;
		font-weight:bold;
		padding:5px;
	}
</style>
<script type="text/javascript" src="js/jquery.1.5.1.min.js"></script>
<script type="text/javascript" src="js/jquery.colorbox-00.js"></script>
<link media="screen" rel="stylesheet" href="css/colorbox.css" type="text/css"  />
<script type="text/javascript">
	<!--
	function print_preview(image_name)
	{
	  var w=850;
	  var h=500;
	  w += 32;
	  h += 96;
	  wleft = (screen.width - w) / 2;
	  wtop = (screen.height - h) / 2;
	  if (wleft < 0) {
		w = screen.width;
		wleft = 0;
	  }
	  if (wtop < 0) {
		h = screen.height;
		wtop = 0;
	  }
	  var url="print_news.php?img_name=" + image_name;
	  //alert(url);
	  var win = window.open(url,'_blank',
		'width=' + w + ', height=' + h + ', ' +
		'left=' + wleft + ', top=' + wtop + ', ' +
		'location=0, menubar=0, ' +
		'status=0, toolbar=0, scrollbars=1, resizable=no');

	  win.resizeTo(w, h);
	  win.moveTo(wleft, wtop);
	  win.focus();
	}
	// -->
	$(function(){
		$('div.eSocialShare span.fshare').click(function(){
			var $url = $(this).attr('url');
			var facebook = 'http://www.facebook.com/sharer.php?u='+$url
			window.open(facebook,'_blank','width=500,height=300');
		});

		$('div.eSocialShare span.tshare').click(function(){
			var $url = $(this).attr('url').split('<::>');
			var twit = 'http://twitter.com/home?status='+$url[0]+'%20'+$url[1];
			window.open(twit,'_blank','width=700,height=300');
		});

		$('div.eSocialShare span.gshare').click(function(){
			var $url = $(this).attr('url');
			var gplus = 'https://plus.google.com/share?url='+$url;
			window.open(gplus,'_blank','width=700,height=300');
		});
	});
</script>
<body style="margin:0 auto; background:#fff">
	<div align="center">
		<div style="clear:both; padding-top:10px">
		<?php
            if(isset($_GET['img_name']))
            {
                echo "<div class='news'>";
                echo "<img style='border:1px solid #ccc; padding:5px' src='$img_name' width=$width height=$height border='0' />";
                echo "</div>";
                echo "<div class='menu_opt alink' style='width:300px; margin-top:10px; padding-top:10px' align='center'><table width='100%' cellpadding='5' cellspacing='1' border='0'><tr>";

                if($img_one<>'')
                {
					if($img_one==$getImg) echo "<td><span class='all_round'><img src='images/dis_sprevious.png' border='0' /></span></td>";
                    else echo "<td><span class='all_round' style='cursor:pointer' id='".$imgPath."/".$img_one."' onclick='window.parent.mouse_onclick(this)' title='Click here to display previous part'><img src='images/sprevious.png' border='0' /></span></td>";
                }
                else echo "<td><span class='all_round'><img src='images/dis_sprevious.png' border='0' /></span></td>";

                echo "<td><span class='all_round' style='cursor:pointer' onclick='javascript:print_preview(\"$img_name\");' title='Click here to print the news image'><img src='images/printer.png' border='0' /></span></td>";

                if($news_id<>'' && $pub_no<>'')echo "<span class='all_side'><a href='$news_link' target='_blank'>সম্পূর্ণ খবর</a></span>";

                if($img_two<>'')
                {
                    if($img_two==$getImg) echo "<td><span class='all_round'><img src='images/dis_snext.png' border='0' /></span></td>";
                    else echo "<td><span class='all_round' style='cursor:pointer' id='".$imgPath."/".$img_two."' onclick='window.parent.mouse_onclick(this)' title='Click here to display next part'><img src='images/snext.png' border='0' /></span></td>";
                }
                else echo "<td><span class='all_round'><img src='images/dis_snext.png' border='0' /></span></td>";

					echo '<td><div id="shareCaption">Share on </div></td>';

			echo '<td><div class="eSocialShare"><span class="fshare all_round" url="'.urlencode($cur_url).'" title="Share on facebook"></span></div></td>';
				echo '<td><div class="eSocialShare"><span class="tshare all_round" url="'.urlencode(strip_tags('sangbadkonika')).'<::>'.urlencode($cur_url).'" title="Share on twitter"></span></div></td>';
				echo '<td><div class="eSocialShare"><span class="gshare all_round" url="'.urlencode($cur_url).'" title="Share on google+"></span></div></td>';

                echo "</tr></table></div>";

            }
        ?>
		</div>
	</div>
</body>