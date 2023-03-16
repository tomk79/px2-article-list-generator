<?php
namespace tomk79\pickles2\pageListGenerator;

/**
 * PX Plugin "px2-page-list-generator"
 */
class blogDefine{

	private $px;
	private $options;

	/**
	 * コンストラクタ
	 * @param object $px PxFWコアオブジェクト
	 * @param array $options オプション
	 */
	public function __construct($px, $options){
		$this->px = $px;
		$this->options = (object) $options;
	}

	/**
	 * ブログページを読み込む
	 */
	public function load_blog_page_list(){
        $realpath_homedir = $this->px->get_realpath_homedir();
        $realpath_blog_basedir = $realpath_homedir.'blogs/';
        $realpath_blog_csv = $realpath_blog_basedir.$this->options->blog_id.'.csv';
        if( !is_file($realpath_blog_csv) ){
            return;
        }
        $blog_page_list_csv = $this->px->fs()->read_csv($realpath_blog_csv);

	}
}
