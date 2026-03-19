<?php
namespace EEZZ\Url2Wp;

/**
 * 后台 UI 渲染类
 * 负责菜单创建、上传界面钩子挂载以及 HTML 模板渲染
 */
class Admin {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// 注册子菜单
		add_action( 'admin_menu', [ $this, 'add_submenu' ] );
		
		// 挂载到标准上传界面
		add_action( 'post-plupload-upload-ui', [ $this, 'post_upload_ui' ] );
		add_action( 'post-html-upload-ui', [ $this, 'post_upload_ui' ] );
	}

	public function add_submenu() {
		add_submenu_page(
			'upload.php',
			__( 'Add External Media without Import', 'eezz-url2wp' ),
			__( 'Add External Media', 'eezz-url2wp' ),
			'upload_files',
			'eezz-url2wp-handler',
			[ $this, 'print_submenu_page' ]
		);
	}

	public function post_upload_ui() {
		$mode = get_user_option( 'media_library_mode', get_current_user_id() );
		?>
		<div id="emwi-in-upload-ui">
			<div class="row1"><?php _e( 'or', 'eezz-url2wp' ); ?></div>
			<div class="row2">
				<?php 
				if ( empty( $mode ) || 'grid' === $mode ) : ?>
					<button type="button" id="emwi-show" class="button button-large">
						<?php _e( 'Add External Media without Import', 'eezz-url2wp' ); ?>
					</button>
					<?php $this->print_media_new_panel( true ); ?>
				<?php else : ?>
					<a class="button button-large" href="<?php echo esc_url( admin_url( 'upload.php?page=eezz-url2wp-handler' ) ); ?>">
						<?php _e( 'Add External Media without Import', 'eezz-url2wp' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public function print_submenu_page() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'Add External Media without Import', 'eezz-url2wp' ); ?></h1>
			<hr class="wp-header-end">
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<?php $this->print_media_new_panel( false ); ?>
			</form>
		</div>
		<?php
	}

	private function print_media_new_panel( $is_modal ) {
		?>
		<div id="emwi-media-new-panel" <?php if ( $is_modal ) echo 'style="display: none"'; ?>>
			<label id="emwi-urls-label"><strong><?php _e( 'Add medias from URLs', 'eezz-url2wp' ); ?></strong></label>
			
			<textarea id="emwi-urls" rows="<?php echo $is_modal ? 3 : 10; ?>" name="urls" required 
				placeholder="<?php _e( "Please fill in the media URLs.\nMultiple URLs are supported with each URL specified in one line.", 'eezz-url2wp' ); ?>"></textarea>

			<div id="emwi-result-log"></div>

			<div id="emwi-hidden" style="display: none;">
				<div style="margin: 10px 0;">
					<span id="emwi-error" style="color: #d63638; font-weight: bold;"></span><br>
					<small class="description">
						<?php _e( 'Please fill in the following properties manually. If you leave the fields blank (or 0 for width/height), the plugin will try to resolve them automatically.', 'eezz-url2wp' ); ?>
					</small>
				</div>
				<div id="emwi-properties">
					<label><?php _e( 'Width', 'eezz-url2wp' ); ?></label>
					<input id="emwi-width" name="width" type="number" style="width: 80px;">
					
					<label><?php _e( 'Height', 'eezz-url2wp' ); ?></label>
					<input id="emwi-height" name="height" type="number" style="width: 80px;">
					
					<label><?php _e( 'MIME Type', 'eezz-url2wp' ); ?></label>
					<input id="emwi-mime-type" name="mime-type" type="text" placeholder="image/jpeg">
				</div>
			</div>

			<div id="emwi-buttons-row">
				<input type="hidden" name="action" value="eezz_url2wp_add_media">
				<?php wp_nonce_field( 'eezz_url2wp_nonce', 'nonce' ); ?>
				
				<span class="spinner"></span>
				<input type="button" id="emwi-clear" class="button" value="<?php _e( 'Clear', 'eezz-url2wp' ); ?>">
				<input type="submit" id="emwi-add" class="button button-primary" value="<?php _e( 'Add', 'eezz-url2wp' ); ?>">
				
				<?php if ( $is_modal ) : ?>
					<input type="button" id="emwi-cancel" class="button" value="<?php _e( 'Cancel', 'eezz-url2wp' ); ?>">
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}