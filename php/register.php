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

	}
}
