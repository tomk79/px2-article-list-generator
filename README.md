tomk79/px2-page-list-generator
========

__tomk79/px2-page-list-generator__ は、[Pickles 2](http://pickles2.pxt.jp/) に、ページ一覧画面を生成する機能を追加します。


## Usage - 使い方

### 1. Pickles2 をセットアップ

[Pickles2 のセットアップ手順](http://pickles2.pxt.jp/overview/setup/) を参照してください。

### 2. composer.json に追記

```json
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

```bash
$ composer update
```

### 4. コンテンツに実装

`tomk79/px2-page-list-generator` は、コンテンツに実装します。 このドキュメントの Usage(使い方) を参照してください。



## 使い方 - Usage

`tomk79/px2-page-list-generator` は、コンテンツに実装し、ページャー付きの複数の一覧ページを自動生成します。

### サイトマップの設定

はじめに、`sitemap.csv` に一覧ページを追加します。

- `path` の最後を、`{*}` で終わるようにします。
- `content` に、実在するコンテンツファイルを指定します。(省略時、`path` から `{*}` を削除した値をもとにコンテンツを探します)

次に、記事ページを追加します。 記事ページには、次の項目を追加してください。

- `article_flg` に固定値 `1` を立てます。
- `release_date` に、記事の公開日を記入します。PHP の `strtotime()` が解析できる形式で指定します。
- `update_date` に、記事の更新日を記入します。PHP の `strtotime()` が解析できる形式で指定します。
- `article_summary` に、記事のサマリーを記入します。

次の表は、`sitemap.csv` 記入例です。

<table>
    <thead>
        <tr>
            <th style="white-space:nowrap;">\* path</th>
            <th style="white-space:nowrap;">\* content</th>
            <th style="white-space:nowrap;">\* id</th>
            <th style="white-space:nowrap;">\* title</th>
            <th style="white-space:nowrap;">\* logical_path</th>
            <th style="white-space:nowrap;">\* article_flg</th>
            <th style="white-space:nowrap;">\* list_flg</th>
            <th style="white-space:nowrap;">\* release_date</th>
            <th style="white-space:nowrap;">\* update_date</th>
            <th style="white-space:nowrap;">\* article_summary</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="white-space:nowrap;">/listsample1/{\*}</td>
            <td style="white-space:nowrap;">/listsample1/index.html</td>
            <td></td>
            <td style="white-space:nowrap;">LIST PAGE</td>
            <td style="white-space:nowrap;"></td>
            <td></td>
            <td>1</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style="white-space:nowrap;">/listsample1/article/002.html</td>
            <td style="white-space:nowrap;"></td>
            <td></td>
            <td style="white-space:nowrap;">Article 2</td>
            <td style="white-space:nowrap;">listsample1/{\*}</td>
            <td>1</td>
            <td></td>
            <td>2015-09-09</td>
            <td>2015-09-09</td>
            <td>サマリー表示用のテキストを記入します。</td>
        </tr>
        <tr>
            <td style="white-space:nowrap;">/listsample1/article/001.html</td>
            <td style="white-space:nowrap;"></td>
            <td></td>
            <td style="white-space:nowrap;">Article 1</td>
            <td style="white-space:nowrap;">listsample1/{\*}</td>
            <td>1</td>
            <td></td>
            <td>2015-08-28</td>
            <td>2015-08-28</td>
            <td>サマリー表示用のテキストを記入します。</td>
        </tr>
    </tbody>
</table>


### コンテンツを実装

サイトマップの設定ができたら、一覧ページの `content` に設定したコンテンツに次のように実装します。

```php
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
	// ページやファイルが存在しないパスへのリクエストだった場合、
	// Not Found ページを表示します。
	$px->bowl()->send('<p>404 - File not found.</p>');
	return;
}

$list = $listMgr->get_list(); // <- ページの一覧を配列で取得します。
$pager = $listMgr->mk_pager(); // <- ページャーのHTMLコードを取得します。
?>

<!-- 得られた情報($list と $pager) をもとに描画する -->
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

## for Developer

### Test

```bash
$ cd {$documentRoot}
$ ./vendor/phpunit/phpunit/phpunit
```
