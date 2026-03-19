<?php
/**
 * Plugin Name: 囧丁乙 - url2wp
 * Plugin URI:  https://jiongdingyi.com/
 * Description: 高性能远程图片外链工具。由 EEZZ 为“囧丁乙”团队重构，直接平移旧版稳定逻辑，支持绕过防盗链，像手术刀一样精准地将其整合进媒体库，无需物理下载。
 * Version:     2.0.0
 * Author:      囧丁乙
 * Author URI:  https://jiongdingyi.com/
 * License:     GPLv3
 * Text Domain: eezz-url2wp
 */

namespace EEZZ\Url2Wp;

// 禁止直接访问
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 定义插件常量
 */
define( 'EEZZ_URL2WP_VERSION', '2.0.0' );
define( 'EEZZ_URL2WP_PATH', plugin_dir_path( __FILE__ ) );
define( 'EEZZ_URL2WP_URL', plugin_dir_url( __FILE__ ) );
define( 'EEZZ_URL2WP_BASENAME', plugin_basename( __FILE__ ) );

/**
 * 核心自动加载器 (强制小写匹配)
 * 解决 Linux 服务器对大小写敏感导致的 500 错误
 */
spl_autoload_register( function ( $class ) {
	$prefix   = __NAMESPACE__ . '\\';
	$base_dir = EEZZ_URL2WP_PATH . 'src/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	
	// 精确修复：将类名路径转换为全小写，以匹配文件系统中的小写文件名
	$file = $base_dir . strtolower( str_replace( '\\', '/', $relative_class ) ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

/**
 * 初始化插件
 * 启动核心 Hook 挂载流程
 */
function init() {
	if ( is_admin() ) {
		Init::instance();
	}
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );