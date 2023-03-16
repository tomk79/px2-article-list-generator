<?php
namespace tomk79\pickles2\pageListGenerator;

/**
 * PX Plugin "px2-page-list-generator"
 */
class register{

	/**
	 * ブログを定義する
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 */
	public static function define_blog( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

        $request_file_path = $px->req()->get_request_file_path();
        if( !preg_match('/\.html?$/i', $request_file_path) ){
            // HTML以外のコンテンツでは実行しない
            return;
        }
 
        $blogDefine = new blogDefine($px, $options);
        $blogDefine->load_blog_page_list();
        return;
	}
}
