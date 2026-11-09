<?php
	$xml = simplexml_load_file($path."XML.xml");

	$myFile = $path."title.php";

	$fh = fopen($myFile, 'w');

	fwrite($fh, '<?php ');

	foreach($xml->children() as $child_root)
	{
		if (count($child_root) > 0)
		{
			$img_num = $child_root->getName();
			$img_num = substr($img_num, strpos($img_num, '_') + 1 , strlen($img_num)-strpos($img_num, '_'));
			if (strpos($img_num, '_'))
			{
				$i = 0;
				foreach($child_root->children() as $child_value)
				{
					//$img_info[$img_num][$i] = $child_value;
					fwrite($fh, '$img_info["'.$img_num.'"]['.$i.'] = "'.$child_value.'";');
					$i = $i + 1;
				}
			}
		}
	}

	fwrite($fh, ' ?>');

	fclose($fh);
?>