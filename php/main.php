<?php
namespace tomk79\pickles2\pageListGenerator;

/**
 * PX Plugin "px2-page-list-generator"
 */
class main{
	private $px;

	/**
	 * コンストラクタ
	 * @param $px = PxFWコアオブジェクト
	 */
	public function __construct($px){
		$this->px = $px;
	}

	/**
	 * listMgrオブジェクトを生成する
	 */
	public function factory_listMgr( $path_list, $options ){
		require_once( __DIR__.'/obj/listMgr.php' );
		$obj = new pxplugin_listMgr_obj_listMgr( $this->px, $path_list, $options );
		return $obj;
	}

	/**
	 * areas
	 */
	public function create( $path_list, $options ){
		return $this->factory_listMgr($path_list, $options);
	}

}
