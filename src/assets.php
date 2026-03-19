<?php
namespace EEZZ\Url2Wp;

/**
 * 资源加载类
 * 负责在后台加载所需的 CSS 和 JS 文件
 */
class Assets {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * 加载后台资源
	 */
	public function enqueue_assets() {
		// 加载插件 CSS 文件
		wp_enqueue_style(
			'eezz-url2wp-css',
			EEZZ_URL2WP_URL . 'eezz-url2wp.css',
			[],
			EEZZ_URL2WP_VERSION
		);

		// 加载插件 JS 文件
		// 依赖项增加了 wp-util 和媒体视图，确保 wp.media 相关逻辑稳定运行
		wp_enqueue_script(
			'eezz-url2wp-js',
			EEZZ_URL2WP_URL . 'eezz-url2wp.js',
			[ 'jquery', 'wp-util', 'media-models', 'media-views' ],
			EEZZ_URL2WP_VERSION,
			true
		);

		// 注入本地化变量 (用于 AJAX 请求和安全校验)
		// 精确修复：通过 wp_localize_script 统一管理 AJAX 入口和安全 Nonce
		wp_localize_script( 'eezz-url2wp-js', 'eezzUrl2Wp', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'eezz_url2wp_nonce' ),
			'strings'  => [
				'error' => __( '发生未知网络错误。', 'eezz-url2wp' ),
			]
		] );
	}
}