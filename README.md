tomk79/px2-page-list-generator
========

__tomk79/px2-page-list-generator__ は、[Pickles 2](https://pickles2.pxt.jp/) に、ページ一覧画面を生成する機能を追加します。


## インストール - Installation

### 1. Pickles2 をセットアップ

[Pickles2 のセットアップ手順](https://pickles2.pxt.jp/overview/setup/) を参照してください。

### 2. composer.json に追記

```json
$ composer require tomk79/px2-page-list-generator
```

### 3. コンテンツに実装

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
            <th style="white-space:nowrap;">* path</th>
            <th style="white-space:nowrap;">* content</th>
            <th style="white-space:nowrap;">* id</th>
            <th style="white-space:nowrap;">* title</th>
            <th style="white-space:nowrap;">* logical_path</th>
            <th style="white-space:nowrap;">* article_flg</th>
            <th style="white-space:nowrap;">* list_flg</th>
            <th style="white-space:nowrap;">* release_date</th>
            <th style="white-space:nowrap;">* update_date</th>
            <th style="white-space:nowrap;">* article_summary</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="white-space:nowrap;">/listsample1/{*}</td>
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
            <td style="white-space:nowrap;">listsample1/{*}</td>
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
            <td style="white-space:nowrap;">listsample1/{*}</td>
            <td>1</td>
            <td></td>
            <td>2015-08-28</td>
            <td>2015-08-28</td>
            <td>サマリー表示用のテキストを記入します。</td>
        </tr>
    </tbody>
</table>


### 一覧ページのコンテンツを実装

サイトマップの設定ができたら、一覧ページの `content` に設定したコンテンツに次のように実装します。

```php
<?php
$listMgr = (new \tomk79\pickles2\pageListGenerator\main($px))->create(
	function($page_info){
		if ($page_info['article_flg']) {
			return true;
		}
		return false;
	} ,
	array(
		'scheme'=>'https',
		'domain'=>'yourdomain.com',
		'title'=>'test list 1',
		'description'=>'TEST LIST',
        'list_page_id' => '/blog/{*}', // ページネーションのリンク先をカレントページ以外のリストにしたい場合に指定する (省略可)
		'dpp'=>10,
		'lang'=>'ja',
		'url_home'=>'https://yourdomain.com/',
		'url_index'=>'https://yourdomain.com/listsample/',
		'author'=>'Tomoya Koyanagi',
		'rss'=>array(
			'atom-1.0'=>$px->get_path_docroot().'rss/atom0100.xml',
			'rss-1.0'=>$px->get_path_docroot().'rss/rss0100.rdf',
			'rss-2.0'=>$px->get_path_docroot().'rss/rss0200.xml',
		)
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

`$listMgr->draw()` メソッドを使って簡単に実装する方法もあります。

```php
<?php
$pageListGenerator = new \tomk79\pickles2\pageListGenerator\main($px);
$listMgr = $pageListGenerator->create(
	function($page_info){
		if( $page_info['article_flg'] ){
			return true;
		}
		return false;
	},
	array(
        /* Any Options */
    )
);

echo $listMgr->draw();
```


## 更新履歴 - Change log

### tomk79/px2-page-list-generator v2.2.0 (リリース日未定)

- クラス名を変更: `.cont-page-list` -> `.px2-page-list`
- 内部コードの細かい修正。

### tomk79/px2-page-list-generator v2.1.3 (2023年2月12日)

- サムネイル抽出ロジックを改善。`$px->path_files()` で呼び出された画像パスに対応できるようになった。

### tomk79/px2-page-list-generator v2.1.2 (2023年2月11日)

- 内部コードの細かい修正。

### tomk79/px2-page-list-generator v2.1.1 (2022年11月3日)

- `list_page_id` オプションを追加。

### tomk79/px2-page-list-generator v2.1.0 (2022年1月8日)

- サポートするPHPのバージョンを `>=7.3.0` に変更。
- PHP 8.1 に対応した。
- `$listMgr->draw()` を追加。
- `$listMgr->get_article_thumb()` を追加。
- 記事一覧ページを Twigテンプレートで表現できるようになった。

### tomk79/px2-page-list-generator v2.0.2 (2020年2月25日)

- オプション `dpp` を追加。リスト1ページあたりの表示件数を設定できる。

### tomk79/px2-page-list-generator v2.0.1 (2019年8月2日)

- ページャーの最初のページURLに番号を含まないようにした。これにより、一覧の最初のページが2つ生成される問題が解消された。
- オプション `scheme` を追加。
- オプションの一部を省略した場合のデフォルト値の挙動を追加。

### tomk79/px2-page-list-generator v2.0.0 (2019年1月15日)

- Initial Release


## for Developer

### Test

```bash
$ cd {$documentRoot}
$ ./vendor/phpunit/phpunit/phpunit
```



## ライセンス - License

MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
