px2-page-list-generator
========

__px2-page-list-generator__ は、[Pickles 2](http://pickles2.pxt.jp/) に、ページ一覧画面を生成する機能を追加します。


## Usage - 使い方

### 1. Pickles2 をセットアップ

### 2. composer.json に追記

```
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/tomk79/px2-page-list-generator.git"
        }
    ],
    "require": {
        "tomk79/px2-page-list-generator": "dev-master"
    }
}
```

### 3. composer を更新

```
$ composer update
```

### 4. コンテンツに実装

```
<?php
$listMgr = (new \tomk79\pickles2\pageListGenerator\main($px))->factory_listMgr(
	function($page_info){
		if(@$page_info['article_flg']){
			return true;
		}
		return false;
	} ,
	array(
		'domain'=>'pickles2.pxt.jp',
		'title'=>'test list 1',
		'description'=>'TEST LIST',
		'lang'=>'ja',
		'url_home'=>'http://pickles2.pxt.jp/',
		'url_index'=>'http://pickles2.pxt.jp/listsample1/',
		'author'=>'Tomoya Koyanagi'
	)
);

if( $px->get_status() != 200 ){
	$px->bowl()->send('<p>404 - File not found.</p>');
	return;
}
$list = $listMgr->get_list();
$pager = $listMgr->mk_pager();
?>

<?php print $pager; ?>

<?php foreach( $list as $row ){ ?>


<h2><a href="<?= htmlspecialchars( $px->href( $row['path'] ) ); ?>"><?= htmlspecialchars( $row['title'] ); ?></a></h2>
<p><?= htmlspecialchars( $row['description'] ); ?></p>
<div>
	released: <?= htmlspecialchars( @date('Y-m-d (D)', strtotime($row['release_date'])) ); ?>
</div>

<?php } ?>
<?php print $pager; ?>

```

## Test

```
$ cd {$documentRoot}
$ ./vendor/phpunit/phpunit/phpunit
```
