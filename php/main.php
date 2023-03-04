<?php
namespace tomk79\pickles2\pageListGenerator;

/**
 * PX Plugin "px2-page-list-generator"
 */
class main{
	private $px;

	/**
	 * コンストラクタ
	 * @param object $px PxFWコアオブジェクト
	 */
	public function __construct($px){
		$this->px = $px;
	}

	/**
	 * listMgrオブジェクトを生成する
	 * @param mixed $cond 条件式
	 * @param array $options オプション
	 */
	public function factory_listMgr( $cond, $options ){
		$obj = new listMgr( $this->px, $cond, $options );
		return $obj;
	}

	/**
	 * areas
	 * @param mixed $cond 条件式
	 * @param array $options オプション
	 */
	public function create( $cond, $options ){
		return $this->factory_listMgr($cond, $options);
	}

}
