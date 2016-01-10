<?php
namespace tomk79\pickles2\pageListGenerator;

/**
 * PX Plugin "listMgr"
 */
class pxplugin_listMgr_obj_rss{
	private $px;
	private $listMgr;
	private $error_list = array();
	private $pubDate = 0;

	/**
	 * コンストラクタ
	 */
	public function __construct( $px, $listMgr ){
		$this->px = $px;
		$this->listMgr = $listMgr;
	}


	/**
	 * RSSファイルを更新する。
	 */
	public function update_rss_file(){
		$options = $this->listMgr->get_options();
		if( !is_array($options['rss']) || !count($options['rss']) ){
			// RSS書き出しの指示がない場合
			return true;
		}
		$list = array();
		$tmp_list = $this->listMgr->get_list_all();
		$this->pubDate = 0;
		for( $i = 0; $i < 50 && $tmp_list[$i]; $i ++ ){
			array_push( $list, $tmp_list[$i] );
			if( strtotime($tmp_list[$i]['release_date']) > $this->pubDate ){
				$this->pubDate = strtotime($tmp_list[$i]['release_date']);
			}
		}
		unset( $tmp_list );

		foreach( $options['rss'] as $rss_version=>$realpath_rss ){
			if( !strlen( $realpath_rss ) ){
				$this->internal_error( 'RSSファイルの保存先が指定されていません。' , __FILE__ , __LINE__ );
				continue;
			}
			if( !is_dir( dirname($realpath_rss) ) ){
				$this->internal_error( 'RSSファイル保存先ディレクトリがありません。' , __FILE__ , __LINE__ );
				continue;
			}
			if( is_file($realpath_rss) && !is_writable($realpath_rss) ){
				$this->internal_error( 'RSSファイルが既に存在し、上書き許可がありません。' , __FILE__ , __LINE__ );
				continue;
			}elseif( !is_file($realpath_rss) && !is_writable( dirname($realpath_rss) ) ){
				$this->internal_error( 'RSSファイル保存先ディレクトリに書き込み許可がありません。' , __FILE__ , __LINE__ );
				continue;
			}

			#--------------------------------------
			#	RSSを生成して保存する。
			$SRC_RSS = '';
			switch( $rss_version ){
				case 'rss-1.0':
					$SRC_RSS = $this->generate_rss_0100( $list );//RSS 1.0
					break;
				case 'rss-2.0':
					$SRC_RSS = $this->generate_rss_0200( $list );//RSS 2.0
					break;
				case 'atom-1.0':
					$SRC_RSS = $this->generate_rss_atom( $list );//Atom 1.0
					break;
			}
			if( !$this->px->fs()->save_file( $realpath_rss , $SRC_RSS ) ){
				$this->internal_error( 'FAILD to save feed RSS '.$rss_version.' ['.$path_rss.']' , __FILE__ , __LINE__ );
			}
			unset( $path_rss , $SRC_RSS );

		}

#		$this->internal_error( '開発中です。' , __FILE__ , __LINE__ );
#		return	false;

		if( $this->get_error_count() ){
			return	false;
		}

		return	true;
	}

	/**
	 * RSS1.0ソースを生成する
	 */
	private function generate_rss_0100( $article_array ){
		$RTN = '';
		$RTN .= '<'.'?xml version="1.0" encoding="'.strtolower( mb_internal_encoding() ).'" ?'.'>'."\n";
		$RTN .= '<rdf:RDF';
		$RTN .= ' xmlns="http://purl.org/rss/1.0/"';
		$RTN .= ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"';
		$RTN .= ' xmlns:dc="http://purl.org/dc/elements/1.1/"';
		$RTN .= '>'."\n";
		$RTN .= '	<channel rdf:about="'.htmlspecialchars( $this->get_blog_info('blog_url') ).'">'."\n";
		$RTN .= '		<title>'.htmlspecialchars( $this->get_blog_info('blog_title') ).'</title>'."\n";
		$RTN .= '		<link>'.htmlspecialchars( $this->get_blog_info('blog_index_url') ).'</link>'."\n";
		$RTN .= '		<description>'.htmlspecialchars( $this->get_blog_info('blog_description') ).'</description>'."\n";
		$RTN .= '		<items>'."\n";
		$RTN .= '			<rdf:Seq>'."\n";
		foreach( $article_array as $Line ){
			$article_url = $this->mk_article_url( $Line['path'] );
			$RTN .= '				<rdf:li rdf:resource="'.htmlspecialchars( $article_url ).'" />'."\n";
		}
		$RTN .= '			</rdf:Seq>'."\n";
		$RTN .= '		</items>'."\n";
		$RTN .= '	</channel>'."\n";
		foreach( $article_array as $Line ){
			$article_url = $this->mk_article_url( $Line['path'] );
			$RTN .= '		<item rdf:about="'.htmlspecialchars( $article_url ).'">'."\n";
			$RTN .= '			<title>'.htmlspecialchars( $Line['title'] ).'</title>'."\n";
			$RTN .= '			<link>'.htmlspecialchars( $article_url ).'</link>'."\n";
			$RTN .= '			<description><![CDATA['.htmlspecialchars( $Line['article_summary'] ).']]></description>'."\n";//descriptionはHTMLとして解釈されるのか？
			$RTN .= '			<dc:date>'.$this->mk_releasedate_string( $Line['release_date'] ).'</dc:date>'."\n";
			$RTN .= '		</item>'."\n";
			unset($article_url);
		}
		$RTN .= '</rdf:RDF>'."\n";
		return	$RTN;
	}

	/**
	 * RSS2.0ソースを生成する
	 */
	private function generate_rss_0200( $article_array ){
		$RTN = '';
		$RTN .= '<'.'?xml version="1.0" encoding="'.strtolower( mb_internal_encoding() ).'" ?'.'>'."\n";
		$RTN .= '<rss version="2.0">'."\n";
		$RTN .= '	<channel>'."\n";
		$RTN .= '		<title>'.htmlspecialchars( $this->get_blog_info('blog_title') ).'</title>'."\n";
		$RTN .= '		<link>'.htmlspecialchars( $this->get_blog_info('blog_index_url') ).'</link>'."\n";
		$RTN .= '		<language>'.htmlspecialchars( $this->get_blog_info('language') ).'</language>'."\n";
		$RTN .= '		<description>'.htmlspecialchars( $this->get_blog_info('blog_description') ).'</description>'."\n";
		$RTN .= '		<pubDate>'.$this->mk_releasedate_string( $this->pubDate ).'</pubDate>'."\n";
#		$RTN .= '		<guid>'.htmlspecialchars( $this->get_blog_info('blog_index_url') ).'</guid>'."\n";
		foreach( $article_array as $Line ){
			$article_url = $this->mk_article_url( $Line['path'] );
			$RTN .= '		<item>'."\n";
			$RTN .= '			<title>'.htmlspecialchars( $Line['title'] ).'</title>'."\n";
			$RTN .= '			<link>'.htmlspecialchars( $article_url ).'</link>'."\n";
			$RTN .= '			<description><![CDATA['.htmlspecialchars( $Line['article_summary'] ).']]></description>'."\n";//descriptionはHTMLとして解釈されるのか？
			$RTN .= '			<pubDate>'.$this->mk_releasedate_string( $Line['release_date'] ).'</pubDate>'."\n";
			$RTN .= '			<guid isPermaLink="true">'.htmlspecialchars( $article_url ).'</guid>'."\n";
			$RTN .= '		</item>'."\n";
		}
		$RTN .= '	</channel>'."\n";
		$RTN .= '</rss>'."\n";
		return	$RTN;
	}

	/**
	 * ATOMソースを生成する
	 */
	private function generate_rss_atom( $article_array ){
		$RTN = '';
		$RTN .= '<'.'?xml version="1.0" encoding="'.strtolower( mb_internal_encoding() ).'" ?'.'>'."\n";
		$RTN .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n";
		$RTN .= '	<title>'.htmlspecialchars( $this->get_blog_info('blog_title') ).'</title>'."\n";
		$RTN .= '	<link rel="alternate" href="'.htmlspecialchars( $this->get_blog_info('blog_index_url') ).'" type="text/html" />'."\n";
		$RTN .= '	<updated>'.$this->mk_releasedate_string( $this->pubDate ).'</updated>'."\n";
		$RTN .= '	<author>'."\n";
		$RTN .= '		<name>'.htmlspecialchars( $this->get_blog_info('blog_author_name') ).'</name>'."\n";
		$RTN .= '	</author>'."\n";
		$RTN .= '	<id>'.htmlspecialchars( md5( $this->get_blog_info('blog_index_url') ) ).'</id>'."\n";
		foreach( $article_array as $Line ){
			$article_url = $this->mk_article_url( $Line['path'] );
			$RTN .= '	<entry>'."\n";
			$RTN .= '		<title>'.htmlspecialchars( $Line['title'] ).'</title>'."\n";
			$RTN .= '		<link rel="alternate" href="'.htmlspecialchars( $article_url ).'" type="text/html" />'."\n";
			$RTN .= '		<id>'.htmlspecialchars( md5( $article_url ) ).'</id>'."\n";
			$RTN .= '		<updated>'.$this->mk_releasedate_string( $Line['release_date'] ).'</updated>'."\n";
			$RTN .= '		<summary>'.htmlspecialchars( $Line['article_summary'] ).'</summary>'."\n";//summary要素はHTMLとして解釈されない。type="html"をつけるとHTMLになるのかも。
			$RTN .= '		<content>'.htmlspecialchars( $Line['article_summary'] ).'</content>'."\n";//content要素はHTMLとして解釈されない。type="html"をつけるとHTMLになるのかも。
			$RTN .= '	</entry>'."\n";
		}
		$RTN .= '</feed>'."\n";
		return	$RTN;
	}


	/**
	 * 記事のパスからURLを生成
	 */
	private function mk_article_url( $path ){
		$article_url = 'http://'.$this->get_blog_info('blog_domain').$this->px->href( $path ).'?rss=1';
		return $article_url;
	}


	/**
	 * リリース日を表す文字列を生成する
	 */
	private function mk_releasedate_string( $releaseDate ){
		if( !is_int($releaseDate) ){
			$releaseDate = strtotime($releaseDate);
		}
		$Ymd = date( 'Y-m-d' , $releaseDate );
		$His = date( 'H:i:s' , $releaseDate );
		$dit = date( 'O' , $releaseDate );
		$dit = preg_replace( '/^(.*)([0-9]{2})$/' , '$1:$2' , $dit );
		return	$Ymd.'T'.$His.$dit;
	}



	/**
	 * ブログに関する諸情報を得る
	 */
	private function get_blog_info( $name ){
		$options = $this->listMgr->get_options();
		switch( strtolower( $name ) ){
			case 'blog_domain':
				return	$options['domain']; break;
			case 'blog_title':
				return	$options['title']; break;
			case 'blog_url':
				return	$options['url_home']; break;
			case 'blog_index_url':
				return	$options['url_index']; break;
			case 'blog_description':
				return	$options['description']; break;
			case 'language':
				return	$options['lang']; break;
			case 'blog_author_name':
				return	$options['author']; break;
			default:
				return	false;
				break;
		}
		return	false;
	}


	/**
	 * オブジェクト内部エラーを記憶
	 */
	private function internal_error( $error_msg , $FILE = '' , $LINE = 0 ){
		if( !is_array( $this->error_list ) ){ $this->error_list = array(); }
		array_push( $this->error_list , array( 'message'=>$error_msg , 'file'=>$FILE , 'line'=>$LINE ) );
		return	true;
	}

	/**
	 * オブジェクト内部エラーを取得
	 */
	public function get_error_list(){
		return	$this->error_list;
	}

	/**
	 * エラー数を調べる
	 */
	public function get_error_count(){
		return	count( $this->error_list );
	}

}

?>
