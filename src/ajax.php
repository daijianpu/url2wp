<?php
namespace EEZZ\Url2Wp;

/**
 * 异步交互类
 * 负责接收 AJAX 请求、处理安全核验、并调用逻辑处理器
 */
class Ajax {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// 挂载 AJAX 钩子
		add_action( 'wp_ajax_eezz_url2wp_add_media', [ $this, 'handle_request' ] );
	}

	/**
	 * 处理异步添加媒体请求
	 */
	public function handle_request() {
		// 1. 精确修复：强制引入媒体库系统依赖，防止 500 致命错误
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		// 2. 安全核验
		check_ajax_referer( 'eezz_url2wp_nonce', 'nonce' );

		// 3. 权限检查 ( upload_files 比 manage_options 更精确 )
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( [ 'message' => __( '权限不足。', 'eezz-url2wp' ) ] );
		}

		// 4. 调用处理器执行旧代码平移逻辑
		$processor = new Processor();
		$info      = $processor->run();

		// 5. 数据转换：平移旧代码中将 ID 转换为 JS 附件对象的逻辑
		$attachment_ids = $info['attachment_ids'];
		$attachments    = [];
		
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment_data = wp_prepare_attachment_for_js( $attachment_id );
			if ( $attachment_data ) {
				$attachments[] = $attachment_data;
			} else {
				$retrieve_error = __( '附件已插入但无法从数据库检索显示。', 'eezz-url2wp' );
				$info['error']  = isset( $info['error'] ) ? $info['error'] . "\n" . $retrieve_error : $retrieve_error;
			}
		}

		// 合并附件列表到返回数据中 (与旧代码 JS 预期格式一致)
		$info['attachments'] = $attachments;

		// 返回成功响应
		wp_send_json_success( $info );
	}
}