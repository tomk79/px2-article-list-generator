<!-- autoindex -->

## これは、動的に追加された記事です

サイトマップキャッシュ生成処理に含まれません。


<?php
$array_csv = array();
array_push($array_csv, array(
    "* title",
    "* path",
    "* release_date",
    "* update_date",
    "* article_summary",
    "* article_keywords",
));
for($i = 0; $i < 10000; $i ++){
    array_push($array_csv, array(
        "サンプルブログページ".($i+1)."のタイトル",
        "/define_sample_".($i+1)."/2023/03/16/samplepage_".($i+1)."/",
        "2023-03-16",
        "2023-03-16",
        "",
        "",
    ));
}
$str_array_csv = $px->fs()->mk_csv($array_csv);
// $px->fs()->save_file(__DIR__.'/../../../../../px-files/blogs/sample_2.csv', $str_array_csv);
?>
