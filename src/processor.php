<?php
namespace EEZZ\Url2Wp;

/**
 * 逻辑处理类
 * 负责远程图片的探测、数据构造及数据库插入
 */
class Processor {

	/**
	 * 执行主循环逻辑 (完全平移旧代码黄金逻辑)
	 */
	public function run() {
		global $wpdb;

		// 1. 接收并预处理数据
		$urls      = $this->get_posted_urls();
		$width     = isset( $_POST['width'] ) ? absint( $_POST['width'] ) : 0;
		$height    = isset( $_POST['height'] ) ? absint( $_POST['height'] ) : 0;
		$mime_type = isset( $_POST['mime-type'] ) ? sanitize_mime_type( $_POST['mime-type'] ) : '';

		$attachment_ids = [];
		$failed_urls    = [];

		foreach ( $urls as $url ) {
			// 【查重逻辑】防止重复插入相同 URL 导致媒体库冗余
			$existing_id = $wpdb->get_var( $wpdb->prepare( 
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_eezz_external_url' AND meta_value = %s LIMIT 1", 
				$url 
			) );
			
			if ( $existing_id ) {
				$attachment_ids[] = intval( $existing_id );
				continue;
			}

			$width_of_the_image = $width;
			$height_of_the_image = $height;
			$mime_type_of_the_image = $mime_type;

			// 2. 探测逻辑：1:1 绝对平移旧代码
			if ( empty( $width ) || empty( $height ) ) {
				// 【核心修正】删除所有伪装逻辑，回归 PHP 原生单参数调用，杜绝 500 致命错误
				$image_size = @getimagesize( $url );
				
				if ( empty( $image_size ) ) {
					$failed_urls[] = $url;
					continue;
				}
				
				$width_of_the_image     = empty( $width ) ? $image_size[0] : $width;
				$height_of_the_image    = empty( $height ) ? $image_size[1] : $height;
				$mime_type_of_the_image = empty( $mime_type ) ? $image_size['mime'] : $mime_type;

			} elseif ( empty( $mime_type ) ) {
				// 如果有尺寸但没 MIME，尝试 HEAD 请求获取
				$response = wp_remote_head( $url, [ 'timeout' => 5, 'sslverify' => false ] );
				if ( is_array( $response ) && isset( $response['headers']['content-type'] ) ) {
					$mime_type_of_the_image = $response['headers']['content-type'];
				} else {
					$failed_urls[] = $url;
					continue;
				}
			}

			// 3. 构造附件并插入
			$filename = wp_basename( $url );
			$attachment = [
				'guid'           => $url,
				'post_mime_type' => $mime_type_of_the_image,
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			];

			// 【核心修正】单参数调用，避免向不支持的 Linux 环境传入远程 URL
			$attachment_id = wp_insert_attachment( $attachment );

			if ( ! is_wp_error( $attachment_id ) && $attachment_id ) {
				// 注入外链标识及原始 URL
				update_post_meta( $attachment_id, '_eezz_is_external', '1' );
				update_post_meta( $attachment_id, '_eezz_external_url', $url );

				// 【核心修正】'file' 严格存入文件名，而非完整 URL，避免后续路径解析崩溃
				$metadata = [
					'width'  => $width_of_the_image,
					'height' => $height_of_the_image,
					'file'   => $filename, 
					'sizes'  => [
						'full' => [
							'file' => $filename,
							'width' => $width_of_the_image,
							'height' => $height_of_the_image,
							'mime-type' => $mime_type_of_the_image,
						]
					]
				];
				
				wp_update_attachment_metadata( $attachment_id, $metadata );
				$attachment_ids[] = $attachment_id;
			}
		}

		// 返回处理结果
		$failed_urls_string = implode( "\n", $failed_urls );
		return [
			'attachment_ids' => $attachment_ids,
			'urls'           => $failed_urls_string,
			'error'          => ! empty( $failed_urls_string ) ? __( '部分图片探测失败，请尝试手动输入尺寸。', 'eezz-url2wp' ) : '',
			'width'          => $width, 
			'height'         => $height, 
			'mime-type'      => $mime_type
		];
	}

	/**
	 * 清理并获取 URL 列表
	 */
	private function get_posted_urls() {
		$raw_urls = isset( $_POST['urls'] ) ? explode( "\n", str_replace( "\r", "", $_POST['urls'] ) ) : [];
		return array_filter( array_map( 'esc_url_raw', array_map( 'trim', $raw_urls ) ) );
	}
}