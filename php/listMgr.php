<?php
namespace tomk79\pickles2\pageListGenerator;

/**
 * PX Plugin "listMgr"
 */
class listMgr{
	private $px;
	private $cond;
	private $options;
	private $list;
	private $current_page_info;
	private $current_pager_num;
	private $path_default_thumb_image;

	/**
	 * コンストラクタ
	 * @param $px = PxFWコアオブジェクト
	 */
	public function __construct($px, $cond, $options){
		$this->px = $px;
		$this->cond = $cond;
		$this->options = (array) $options;
		$this->current_page_info = $this->px->site()->get_current_page_info();
		$this->path_default_thumb_image = 'data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/../resources/images/noimage.png'));


		// ------ options

		// Display Per Page
		if( !array_key_exists('dpp', $this->options) ){
			$this->options['dpp'] = 10;
		}
		$this->options['dpp'] = intval($this->options['dpp']);
		if( $this->options['dpp'] <= 1 ){
			$this->options['dpp'] = 1;
		}

		// ------
		$this->load_list();
		$this->parse_request();
	}

	/**
	 * オプション配列を取得する
	 */
	public function get_options(){
		return $this->options;
	}

	/**
	 * rssオブジェクトを生成する
	 */
	private function factory_rss(){
		$obj = new rss($this->px, $this);
		return $obj;
	}

	/**
	 * リクエストの内容を解析する
	 */
	private function parse_request(){
		$path_param = $this->px->site()->get_path_param('');
		$path_param = preg_replace( '/'.$this->px->get_directory_index_preg_pattern().'$/', '', $path_param );

		$paramlist = array();
		if( strlen($path_param) ){
			// $tmp_binded_path = $this->px->href( $this->px->site()->bind_dynamic_path_param( $this->current_page_info['path'], array(''=>$path_param) ) );
			// if( is_file($this->px->get_path_controot().$tmp_binded_path) ){
			// 	return include( $this->px->get_path_controot().$tmp_binded_path );
			// }
			if( !preg_match('/^[1-9][0-9]*\/$/si', $path_param) ){
				return $this->page_notfound();
			}
			$paramlist = explode( '/', $path_param );
		}
		if( @!$paramlist[0] ){
			$paramlist[0] = 1;
		}
		$paramlist[0] = intval($paramlist[0]);

		$this->current_pager_num = $paramlist[0];
		return true;
	}

	/**
	 * リスト配列をロードする
	 */
	private function load_list(){

		$sitemap = $this->px->site()->get_sitemap();
		$list = array();
		foreach( $sitemap as $page_info ){
			if( is_callable($this->cond) ){
				if( call_user_func_array( $this->cond, array($page_info) ) ){
					array_push($list, $page_info);
				}
			}elseif( gettype($this->cond) == 'string' ){
				if( isset($page_info[$this->cond]) && $page_info[$this->cond] ){
					array_push($list, $page_info);
				}
			}
		}

		$this->list = $list;
		unset($list);

		if( is_array( @$this->options['rss'] ) ){
			// RSSパスのオプションが有効な場合
			$obj_rss = $this->factory_rss();
			$obj_rss->update_rss_file();
		}

		return true;
	}

	/**
	 * リスト配列を取得する
	 */
	public function get_list(){
		$pager_info = $this->get_pager_info();
		$rtn = array();
		for( $i = $pager_info['dpp']*($pager_info['current']-1); $i < $pager_info['dpp']*($pager_info['current']) && @$this->list[$i]; $i++ ){
			array_push( $rtn, $this->list[$i] );
		}
		return $rtn;
	}

	/**
	 * リスト配列を全件取得する
	 */
	public function get_list_all(){
		return $this->list;
	}

	/**
	 * ページャーのソースを取得する
	 */
	public function mk_pager(){

		$pager_info = $this->get_pager_info();
		$pager = '';
		if( $pager_info['total_page_count'] > 1 ){
			$pager .= '<div class="unit pager">'."\n";
			$pager .= '	<ul>'."\n";
			$pager .= '		<li class="pager-first">'.(!is_null($pager_info['first'])?'<a href="'.htmlspecialchars( $this->href_pager( $pager_info['first'] ) ).'">&lt;&lt;first</a>':'<span>&lt;&lt;first</span>').'</li>'."\n";
			$pager .= '		<li class="pager-prev">'.(!is_null($pager_info['prev'])?'<a href="'.htmlspecialchars( $this->href_pager( $pager_info['prev'] ) ).'">&lt;prev</a>':'<span>&lt;prev</span>').'</li>'."\n";
			for( $i = $pager_info['index_start']; $i <= $pager_info['index_end']; $i ++ ){
				if( $i == $pager_info['current'] ){
					$pager .= '		<li><span class="current">'.htmlspecialchars($i).'</span></li>'."\n";
				}else{
					$href = $this->href_pager( $i );
					$pager .= '		<li><a href="'.htmlspecialchars( $href ).'">'.htmlspecialchars($i).'</a></li>'."\n";
					$this->px->add_relatedlink( $href );
				}
			}
			$pager .= '		<li class="pager-next">'.(!is_null($pager_info['next'])?'<a href="'.htmlspecialchars( $this->href_pager( $pager_info['next'] ) ).'">next&gt;</a>':'<span>next&gt;</span>').'</li>'."\n";
			$pager .= '		<li class="pager-last">'.(!is_null($pager_info['last'])?'<a href="'.htmlspecialchars( $this->href_pager( $pager_info['last'] ) ).'">last&gt;&gt;</a>':'<span>last&gt;&gt;</span>').'</li>'."\n";
			$pager .= '	</ul>'."\n";
			$pager .= '</div><!-- /.pager -->'."\n";

		}

		return $pager;
	}

	/**
	 * ページャーごとのURLを生成
	 */
	private function href_pager( $page_num ){
		$bind_param = $page_num.'/';
		if( $page_num == 1 ){
			$bind_param = '';
		}
		$rtn = $this->px->href( $this->px->site()->bind_dynamic_path_param( $this->current_page_info['path'], array(''=>$bind_param) ) );
		return $rtn;
	}

	/**
	 * ページャー情報を計算して答える。
	 * 
	 * `$options' に次の設定を渡すことができます。
	 * 
	 * <dl>
	 *   <dt>int $options['index_size']</dt>
	 *     <dd>インデックスの範囲</dd>
	 * </dl>
	 * 
	 * @param int $total_count 総件数
	 * @param int $current_page_num カレントページのページ番号
	 * @param int $display_per_page 1ページ当りの表示件数
	 * @param array $options オプション
	 * 
	 * @return array ページャー情報を格納した連想配列
	 */
	public function get_pager_info( $total_count = null , $current_page_num = null , $display_per_page = null , $options = array() ){
		$total_count = count($this->list);
		$current_page_num = $this->current_pager_num;


		// 総件数
		$total_count = intval( $total_count );
		if( $total_count <= 0 ){ return false; }

		// 現在のページ番号
		$current_page_num = intval( $current_page_num );
		if( $current_page_num <= 0 ){ $current_page_num = 1; }

		// ページ当たりの表示件数
		if( is_null($display_per_page) ){
			$display_per_page = intval( $this->options['dpp'] );
		}
		if( $display_per_page <= 1 ){ $display_per_page = 1; }

		// インデックスの範囲
		$index_size = 0;
		if( !@is_null( $options['index_size'] ) ){
			$index_size = intval( $options['index_size'] );
		}
		if( $index_size < 1 ){
			$index_size = 5;
		}

		$RTN = array(
			'tc'=>$total_count,
			'dpp'=>$display_per_page,
			'current'=>$current_page_num,
			'total_page_count'=>null,
			'first'=>null,
			'prev'=>null,
			'next'=>null,
			'last'=>null,
			'limit'=>$display_per_page,
			'offset'=>0,
			'index_start'=>0,
			'index_end'=>0,
			'errors'=>array(),
		);

		if( $total_count%$display_per_page ){
			$RTN['total_page_count'] = intval($total_count/$display_per_page) + 1;
		}else{
			$RTN['total_page_count'] = intval($total_count/$display_per_page);
		}

		if( $RTN['total_page_count'] != $current_page_num ){
			$RTN['last'] = $RTN['total_page_count'];
		}
		if( 1 != $current_page_num ){
			$RTN['first'] = 1;
		}

		if( $RTN['total_page_count'] > $current_page_num ){
			$RTN['next'] = intval($current_page_num) + 1;
		}
		if( 1 < $current_page_num ){
			$RTN['prev'] = intval($current_page_num) - 1;
		}

		$RTN['offset'] = ($RTN['current']-1)*$RTN['dpp'];

		if( $current_page_num > $RTN['total_page_count'] ){
			array_push( $RTN['errors'] , 'Current page num ['.$current_page_num.'] is over the Total page count ['.$RTN['total_page_count'].'].' );
		}

		// インデックスの範囲
		// 	23:50 2007/08/29 Pickles Framework 0.1.8 追加
		$RTN['index_start'] = 1;
		$RTN['index_end'] = $RTN['total_page_count'];
		if( ( $index_size*2+1 ) >= $RTN['total_page_count'] ){
			// 範囲のふり幅全開にしたときに、
			// 総ページ数よりも多かったら、常に全部出す。
			$RTN['index_start'] = 1;
			$RTN['index_end'] = $RTN['total_page_count'];
		}elseif( ( $index_size < $RTN['current'] ) && ( $index_size < ( $RTN['total_page_count']-$RTN['current'] ) ) ){
			// 範囲のふり幅全開にしたときに、
			// すっぽり収まるようなら、前後に $index_size 分だけ出す。
			$RTN['index_start'] = $RTN['current']-$index_size;
			$RTN['index_end'] = $RTN['current']+$index_size;
		}elseif( $index_size >= $RTN['current'] ){
			// 前方が収まらない場合は、
			// あまった分を後方に回す
			$surplus = ( $index_size - $RTN['current'] + 1 );
			$RTN['index_start'] = 1;
			$RTN['index_end'] = $RTN['current']+$index_size+$surplus;
		}elseif( $index_size >= ( $RTN['total_page_count']-$RTN['current'] ) ){
			// 後方が収まらない場合は、
			// あまった分を前方に回す
			$surplus = ( $index_size - ($RTN['total_page_count']-$RTN['current']) );
			$RTN['index_start'] = $RTN['current']-$index_size-$surplus;
			$RTN['index_end'] = $RTN['total_page_count'];
		}

		return	$RTN;
	} // get_pager_info()

	/**
	 * NotFound画面
	 */
	private function page_notfound(){
		$this->px->set_status(404);// 404 NotFound
		// $this->px->bowl()->send('<p>404 - File not found.</p>');
		return;
	}

	/**
	 * 記事本文から、サムネイルに使う画像を抽出する
	 *
	 * @param object $path ページのパスまたはページID
	 */
	public function get_article_thumb( $path ){
		$path_thumb = $this->path_default_thumb_image;

		$path_content = $path;
		$current_page_info = $this->px->site()->get_page_info( $path );
		$path_content = null;
		if( is_array($current_page_info) && array_key_exists('content', $current_page_info) ){
			$path_content = $current_page_info['content'];
		}
		if( is_null( $path_content ) ){
			$path_content = $path;
		}

		foreach( array_keys( get_object_vars( $this->px->conf()->funcs->processor ) ) as $tmp_ext ){
			if( $this->px->fs()->is_file( './'.$path_content.'.'.$tmp_ext ) ){
				$path_content = $path_content.'.'.$tmp_ext;
				break;
			}
		}

		if( !is_file('./'.$path_content) ){
			return $path_thumb;
		}

		$src_content = file_get_contents('./'.$path_content);


		// HTML属性を削除
		$tmp_path_thumb = null;
		require_once(__DIR__.'/simple_html_dom.php');
		$html = str_get_html(
			$src_content ,
			true, // $lowercase
			true, // $forceTagsClosed
			DEFAULT_TARGET_CHARSET, // $target_charset
			false, // $stripRN
			DEFAULT_BR_TEXT, // $defaultBRText
			DEFAULT_SPAN_TEXT // $defaultSpanText
		);
		if( $html ){
			$ret = $html->find('img');
			foreach( $ret as $retRow ){
				// var_dump($retRow->src);
				$tmp_path_thumb = $retRow->src;
				break;
			}
		}

		if( strlen($tmp_path_thumb) ){
			if( preg_match( '/^\//', $tmp_path_thumb ) ){
				$path_thumb = $this->px->conf()->path_controot.$tmp_path_thumb;
			}else{
				$path_thumb = dirname($this->px->conf()->path_controot.$path_content).'/'.$tmp_path_thumb;
			}
			$path_thumb = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $path_thumb ) );
		}

		return $path_thumb;
	}

	/**
	 * 一覧ページを描画する
	 */
	public function draw( $options = array() ){
		$options = (object) $options;
		$rtn = '';

		$template = '';
		if( isset($options->template) && is_string($options->template) ){
			$template = $options->template;
		}else{
			$template = file_get_contents( __DIR__.'/../resources/templates/list.twig' );
			$stylesheet = '';
			$stylesheet .= '<style> /* Page List Generator */ ';
			$stylesheet .= htmlspecialchars( file_get_contents( __DIR__.'/../resources/styles/pagelist.min.css' ) );
			$stylesheet .= '</style>'."\n";
			$this->px->bowl()->put($stylesheet, 'head');
		}

		$twigHelper = new helper_twig();
		$pager = $this->get_pager_info();
		$list = $this->get_list();

		if( $pager['total_page_count'] > 1 ){
			for( $idx = $pager['index_start']; $idx <= $pager['index_end']; $idx ++ ){
				if( $idx != $pager['current'] ){
					$this->px->add_relatedlink( $this->href_pager( $idx ) );
				}
			}
		}

		$rtn .= $twigHelper->bind(
			$template,
			array(
				'lang' => $this->px->lang(),
				'pager' => $pager,
				'list' => $list,
			),
			array(
				'href_pager' => function( $page_num ){
					return $this->href_pager( $page_num );
				},
				'href' => function( $path ){
					return $this->px->href( $path );
				},
				'thumb' => function( $path ){
					return $this->get_article_thumb($path);
				},
			)
		);

		return $rtn;
	}

}
