<?php
namespace EEZZ\Url2Wp;

/**
 * 核心初始化类
 * 负责实例化所有功能模块并挂载核心路径补丁
 */
class Init {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->load_modules();
	}

	/**
	 * 按照依赖顺序加载子模块
	 */
	private function load_modules() {
		// 1. 资源加载 (js/css)
		Assets::instance();

		// 2. 后台 UI 界面
		Admin::instance();

		// 3. 异步交互处理
		Ajax::instance();

		// 4. 挂载核心路径过滤器
		$this->register_core_filters();
	}

	/**
	 * 注册核心路径拦截器
	 * 直接平移并优化旧代码的 get_attached_file 逻辑
	 */
	private function register_core_filters() {
		/**
		 * 路径重定向补丁
		 * 只有带有 '_eezz_is_external' 标识的附件，才会将其路径强制指向远程 URL (GUID)
		 */
		add_filter( 'get_attached_file', function( $file, $attachment_id ) {
			// 检查是否为本插件处理的外链附件 (精确修复：防止误伤)
			$is_external = get_post_meta( $attachment_id, '_eezz_is_external', true );

			if ( '1' === $is_external ) {
				$post = get_post( $attachment_id );
				if ( $post && ! empty( $post->guid ) ) {
					return $post->guid;
				}
			}

			// 如果不是外链图片，返回原始路径，确保系统稳定性
			return $file;
		}, 10, 2 );
	}
}