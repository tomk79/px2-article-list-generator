これはリストページです。

<?php
$pageListGenerator = new \tomk79\pickles2\pageListGenerator\main($px);
$listMgr = $pageListGenerator->create(
	array(
	    "blog_id" => "sample_1",
	) ,
	array(
		"orderby" => "update_date",
		"scending" => "desc",
	)
);

echo $listMgr->draw();
?>
