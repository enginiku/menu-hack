<?php
/**
* Plugin Name: Tabbed Menu
* Description: A plugin for a tabbed menu in the dashboard
* Version: 0.1
* Author: Navin Nagpal
* License: GPLv2
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
* @package   TabbedMenu
* @version   0.1.0
* @since 	 0.1.0
* @author    
* @copyright Copyright (c) 2013
* @link      
* @license   
*/


class Tabbed_Menu {

	/**
	* PHP5 constructor method
	* 
	* @since 0.1.0
	* @access public
	* @return void
	*/
	public function __construct(){

		/* Register the activation hook. */
		register_activation_hook( __FILE__, array( $this, 'nn_myplugin_activate' ) );
		
		/* Internationalize the text strings. */
		add_action( 'init', array( &$this, 'nn_load_localisation' ), 0 );
		
		/* Add the custom menu. */
		add_action( 'admin_menu', array( &$this, 'nn_myplugin_add_tabbed' ) );
		
		/* Add action for all the settings fields. */
		add_action( 'admin_init', array( &$this, 'nn_myplugin_admin_menuinit' ) );
		//register_deactivation_hook( __FILE__, array( $this, 'nn_myplugin_deactivate' ));
		
		/* Add filter to provide an extra link. */
		add_filter( 'plugin_action_links_'.plugin_basename(__FILE__).'', array( &$this, 'nn_extra_link' ) );
		
		/* Add action to deactivate along with data. */
		add_action( 'admin_footer', array( &$this, 'nn_init_deactivate' ) );
		
		/* Add action to initiate callback using ajax. */
		add_action( 'wp_ajax_nn_deactivate_delete_options', array( &$this, 'nn_action_callback' ) );
		
		/* Add Action to include scripts for image upload. */
		add_action('admin_enqueue_scripts', array(&$this, 'nn_enqueue_image_upload') );
		
		/* Add Action to include scripts for color picker. */
		add_action( 'admin_enqueue_scripts', array( &$this, 'nn_enqueue_color_picker' ) );
	}


	/**  
	* Incorporate ajax for causing deletion of data with deactivation
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_init_deactivate(){
		?>
		<script>
		jQuery( '#nn_delete_data' ).hover(function(){
			jQuery.ajax({
				type: "post",
				url: "admin-ajax.php",
				data: {
					action: 'nn_deactivate_delete_options'
				}
			});
		});
		</script>
		<?php
	}

	/**
	* Callback on ajax action.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_action_callback(){

		/* Delete the options. */
		delete_option('nn_sub_general_options');
		delete_option('nn_sub_advanced_options');
		
		/* Call the deactivate function. */
		deactivate_plugins( plugin_basename(__FILE__), $silent=false );
	}


	function nn_extra_link ( $links ){

		$delete_data = '<a href="admin.php?page=nn_deact_delete">'.'Deactivate and delete'.'</a>';
		$links[] = $delete_data;
		return $links;
	}

	/**
	* Performs plugin activation tasks.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	public function nn_myplugin_activate(){
		
		/* Call function on uninstall. */
		register_uninstall_hook(__FILE__, 'nn_myplugin_uninstall');


		/**
		* Performs plugin uninstall tasks.
		* 
		* @since  0.1.0
		* @access public
		* @return void
		*/
		function nn_myplugin_uninstall(){

		}
	}

	/**
	* Adds the menu and it's submenus.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	public function nn_myplugin_add_tabbed(){
		
		add_menu_page( 'Tabbed Submenu Hack', 'Mera Plugin','manage_options', 'menu_hack_general', array( &$this, 'nn_myplugin_tabbed_menupg') );
		add_submenu_page( 'menu_hack_general', 'General Settings', 'General', 'manage_options', 'menu_hack_general', array( &$this, 'nn_myplugin_tabbed_menupg') );
		add_submenu_page( 'menu_hack_general', 'Advanced Settings', 'Advanced', 'manage_options', 'menu_hack_advanced', array( &$this, 'nn_myplugin_tabbed_menupg') );
		add_submenu_page( NULL,'Delete Data','Delete Data','manage_options','nn_deact_delete', array( &$this, 'nn_deact_delete_page') );
	}

	/**
	* Adds the various settings fields along with registering the settings.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_myplugin_admin_menuinit(){

		/* Register general settings */
		register_setting( 'nn_sub_general_options', 'nn_sub_general_options', array( &$this, 'nn_sub_validate_options') );

		/* Register advanced settings */
		register_setting( 'nn_sub_advanced_options', 'nn_sub_advanced_options', array( &$this, 'nn_sub_validate_options') );

		/* The section for general settings */
		add_settings_section( 'nn_sub_gen', __( 'My Plugin Settings', 'nnp' ), array( &$this, 'nn_sub_section_text' ), 'menu_hack_general' );
		
		/* The section for advanced settings */
		add_settings_section( 'nn_sub_ad', __( 'My Plugin Settings', 'nnp' ), array(&$this, 'nn_sub_section_text' ), 'menu_hack_advanced' );

		/* The section for advanced settings */
		add_settings_section( 'nn_dd', __( 'My Plugin Settings', 'nnp' ), array( &$this, 'nn_dd_text' ), 'nn_deact_delete' );

		/* The section for general settings */
		add_settings_field( 'nn_tfield_med', __( 'Text field medium', 'nnp' ), array( &$this,'nn_tf_med' ), 'menu_hack_advanced', 'nn_sub_ad' );

		/* Register the small sized text field for the section on advanced settings page */
		add_settings_field( 'nn_tfield_sm', __( 'Text field small', 'nnp' ), array( &$this,'nn_tf_small' ), 'menu_hack_advanced', 'nn_sub_ad' );

		/* Register the small large text field for the section on advanced settings page */
		add_settings_field( 'nn_tfield_la', __( 'Text field large', 'nnp' ), array( &$this,'nn_tf_large' ), 'menu_hack_advanced', 'nn_sub_ad' );

		/* Register the large sized text area for the section on advanced settings page */
		add_settings_field( 'nn_tarea_la', __( 'Textarea large', 'nnp' ), array( &$this,'nn_ta_large' ), 'menu_hack_advanced', 'nn_sub_ad' );

		/* Register the medium sized text area for the section on advanced settings page */
		add_settings_field( 'nn_tarea_med', __( 'Textarea medium', 'nnp' ), array( &$this,'nn_ta_med' ), 'menu_hack_advanced', 'nn_sub_ad' );

		/* Register the small sized text area for the section on advanced settings page */
		add_settings_field( 'nn_tarea_sm', __( 'Textarea small', 'nnp' ), array( &$this,'nn_ta_small' ), 'menu_hack_advanced', 'nn_sub_ad' );

		/* Register the number field for the section on advanced settings page */
		add_settings_field( 'nn_nfield', __('Number field', 'nnp' ), array( &$this,'nn_nf' ), 'menu_hack_advanced', 'nn_sub_ad' );

		/* Register the single check box for the section on advanced settings page */
		add_settings_field( 'nn_single_cb', __( 'Single checkbox', 'nnp' ), array( &$this,'nn_scb' ), 'menu_hack_advanced', 'nn_sub_ad' );

		/* Register multiple check boxes for the section on advanced settings page */
		add_settings_field( 'nn_multi_cb', __( 'Single checkbox', 'nnp' ), array( &$this,'nn_mcb' ), 'menu_hack_advanced', 'nn_sub_ad' );

		/* Register the inline radio buttons for the section on general settings page */
		add_settings_field( 'nn_inline_rb', __( 'Inline radio', 'nnp' ), array( &$this,'nn_irb' ), 'menu_hack_general', 'nn_sub_gen' );

		/* Register the standard radio buttons for the section on general settings page */
		add_settings_field( 'nn_std_rb', __( 'Standard radio', 'nnp' ), array( &$this,'nn_srb' ), 'menu_hack_general', 'nn_sub_gen' );

		/* Register the dropdown select for the section on general settings page */
		add_settings_field( 'nn_select', __( 'Select option', 'nnp' ), array( &$this,'nn_sel' ), 'menu_hack_general', 'nn_sub_gen' );

		/* Register the image upload field for the section on general settings page */
		add_settings_field( 'nn_image_uploader', __( 'Upload an image', 'nnp' ), array( &$this,'nn_image_up' ), 'menu_hack_general', 'nn_sub_gen' );

		/* Register the file upload field for the section on general settings page */
		add_settings_field( 'nn_file_uploader', 'Upload a file', array( &$this,'nn_file_up' ), 'menu_hack_general', 'nn_sub_gen' );

		/* Register wysiwyg editor for the section on general settings page */
		add_settings_field( 'nn_wp_edit', __( 'WYSIWYG', 'nnp' ), array( &$this,'nn_wysiwyg' ), 'menu_hack_general', 'nn_sub_gen' );

		/* Register the date picker for the section on general settings page */
		add_settings_field( 'nn_date_picker', __( 'Pick a date', 'nnp' ), array( &$this,'nn_dpicker' ), 'menu_hack_general', 'nn_sub_gen' );

		/* Register the post picker for the section on general settings page */
		add_settings_field( 'nn_post_picker', __( 'Pick a post type', 'nnp' ), array( &$this,'nn_ppicker' ), 'menu_hack_general', 'nn_sub_gen' );

		/* Register the taxonomy picker for the section on general settings page */
		add_settings_field( 'nn_tax_picker', __( 'Pick a taxonomy term', 'nnp' ), array( &$this,'nn_tpicker' ), 'menu_hack_general', 'nn_sub_gen' );

		/* Register the color picker for the section on advanced settings page */
		add_settings_field( 'nn_color_picker', __( 'Pick a color', 'nnp' ), array( &$this,'nn_cpicker' ), 'menu_hack_advanced', 'nn_sub_ad' );
	}

	/**
	* Selects the appropriate tab and adds the correct the settings fields.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_myplugin_tabbed_menupg($active_tab=''){
		$arg = 'Advanced';
		$page_title = get_admin_page_title();
		?>
		
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br></div>

			<h2>My Plugin</h2>
		
			<?php
			settings_errors();
		
			if( isset( $_GET[ 'tab' ] ) ) {  
        	    $active_tab = $_GET[ 'tab' ];  
        	} else if( $active_tab == 'nn_sub_advanced' ) {  
        	    $active_tab = 'nn_sub_advanced';  
        	} else if(strstr($page_title,$arg)){
        		 $active_tab = 'nn_sub_advanced';
        	}
			else 
			{  
            	$active_tab = 'nn_sub_general';
        	}

			?>  
		
			<h2 class="nav-tab-wrapper">  
    			<a href="?page=menu_hack_general&amp;tab=nn_sub_general" class="nav-tab <?php echo $active_tab == 'nn_sub_general' ? 'nav-tab-active' : ''; ?>">General</a>  
    			<a href="?page=menu_hack_advanced&amp;tab=nn_sub_advanced" class="nav-tab <?php echo $active_tab == 'nn_sub_advanced' ? 'nav-tab-active' : ''; ?>">Advanced</a>  
			</h2> 

			<form action="options.php" method="post" enctype="multipart/form-data">
			
				<?php 
				$gen = 'nn_sub_general';
				$ad = 'nn_sub_advanced';
				if( $active_tab == 'nn_sub_general' )
				{
					settings_fields('nn_sub_general_options');
					do_settings_sections('menu_hack_general');
				}
				else
				{
					settings_fields( 'nn_sub_advanced_options' );
					do_settings_sections( 'menu_hack_advanced' );
				}

				submit_button(__( 'Save Settings', 'nnp' ) );
				?>
			</form>
		</div>
	
		<?php
	}

	/**
	* Displays the page asking for confirmation about deleting data.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_deact_delete_page(){
		
		?>
		
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br></div>
			<h2>My Plugin</h2>
			
			<?php
			do_settings_sections( 'nn_deact_delete' );
			?>
		
			<form method='POST' action='plugins.php?deactivate=true&plugin_status=all&paged=1&s=' style='display:inline;'>
				<input type='submit' id='nn_delete_data' class='button' value="<?php _e('Yes, go ahead' ,'nnp' ) ?>" />
			</form>
			<form method='POST' action='plugins.php' style='display:inline;'>
				<input type='submit' class='button' value="<?php _e( 'No, return to plugins page', 'nnp' ) ?>" />
			</form>
		</div>
		
		<?php
	}

	/**
	* Loads the POT files.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_load_localisation(){
		
		load_plugin_textdomain( 'nnp', false, dirname( plugin_basename(__FILE__)) . '/languages/' );
	}

	/**
	* Draws the section header.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_sub_section_text() {
		
		echo "<p>".__( 'Enter your settings here' ,'nnp' )."</p>";
	}

	/**
	* Displays the text for deactivation plus deletion page.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_dd_text(){
		
		echo "<p>".__( 'Do you really want to select the option of deleting all data along with deactivation ?', 'nnp' )."</p>";
	}

	/**
	* Include all js files necessary for image upload.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_enqueue_image_upload(){

		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_register_script( 'nn_my-upload', WP_PLUGIN_URL.'/menu-hack-oop/nn-image-script.js', array( 'jquery', 'media-upload', 'thickbox' ) );
		wp_enqueue_script( 'nn_my-upload' );
		wp_enqueue_style( 'thickbox' );
	}

	/**
	* Include all js files necessary for color picker.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_enqueue_color_picker(){

		wp_enqueue_style( 'wp-color-picker' );
    	wp_enqueue_script( 'wp-color-picker-script', WP_PLUGIN_URL.'/menu-hack-oop/nn-color-script.js', array( 'wp-color-picker' ), false, true );
	}

	/**
	* Displays and sanitizes medium sized text field.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_tf_med(){
		
		$options = get_option( 'nn_sub_advanced_options' );
		if(!isset($options['nn_tfield_med'])) $options['nn_tfield_med'] = '';
		$text_string = $options['nn_tfield_med'];
		echo "<input id='nn_tfield_med' name='nn_sub_advanced_options[nn_tfield_med]' type='text' value='".sanitize_text_field( $text_string )."' />";
	}

	/**
	* Displays and sanitizes small sized text field.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_tf_small(){
		
		$options = get_option( 'nn_sub_advanced_options' );
		if(!isset($options['nn_tfield_sm'])) $options['nn_tfield_sm'] = '';
		$text_string = $options['nn_tfield_sm'];
		echo "<input id='nn_tfield_sm' name='nn_sub_advanced_options[nn_tfield_sm]' type='text' value='".sanitize_text_field($text_string)."' style='width:80px'/>";
	}

	/**
	* Displays and sanitizes large sized text field.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_tf_large(){
		
		$options = get_option( 'nn_sub_advanced_options' );
		if(!isset($options['nn_tfield_la'])) $options['nn_tfield_la'] = '';
		$text_string = $options['nn_tfield_la'];
		echo "<input id='nn_tfield_la' name='nn_sub_advanced_options[nn_tfield_la]' type='text' value='".sanitize_text_field($text_string)."' style='width:200px'/>";
	}

	/**
	* Displays and sanitizes large sized text area.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_ta_large(){
		
		$options = get_option( 'nn_sub_advanced_options' );
		if(!isset($options['nn_tarea_la'])) $options['nn_tarea_la'] = '';
		$text_string = wp_strip_all_tags($options['nn_tarea_la']);
		echo "<textarea id='nn_tarea_la' name='nn_sub_advanced_options[nn_tarea_la]' style='width:300px;height:150px'>".$text_string."</textarea>";
	}

	/**
	* Displays and sanitizes medium sized text field.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_ta_med(){
		
		$options = get_option( 'nn_sub_advanced_options' );
		if(!isset($options['nn_tarea_med'])) $options['nn_tarea_med'] = '';
		$text_string = wp_strip_all_tags($options['nn_tarea_med']);
		echo "<textarea id='nn_tarea_med' name='nn_sub_advanced_options[nn_tarea_med]' style='width:200px;height:100px'>".$text_string."</textarea>";
	}

	/**
	* Displays and sanitizes small sized text area.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_ta_small(){
		
		$options = get_option( 'nn_sub_advanced_options' );
		if(!isset($options['nn_tarea_sm'])) $options['nn_tarea_sm'] = '';
		$text_string = wp_strip_all_tags($options['nn_tarea_sm']);
		echo "<textarea id='nn_tarea_sm' name='nn_sub_advanced_options[nn_tarea_sm]' style='width:100px;height:50px'>".$text_string."</textarea>";
	}

	/**
	* Displays number field.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_nf(){
		
		$options = get_option( 'nn_sub_advanced_options' );
		if(!isset($options['nn_nfield'])) $options['nn_nfield'] = '';
		$text_string = $options['nn_nfield'];
		echo "<input id='nn_nfield' name='nn_sub_advanced_options[nn_nfield]' type='number' value='$text_string' min='1' max='20'/>";
	}

	/**
	* Displays single check box.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_scb(){
		
		$options = get_option( 'nn_sub_advanced_options' );
		if(!isset($options['nn_single_cb'])) $options['nn_single_cb'] = '';
		$text_string = $options['nn_single_cb'];
		echo "<input id='nn_single_cb' name='nn_sub_advanced_options[nn_single_cb]' type='hidden' value='0' />
		<input id='nn_single_cb' name='nn_sub_advanced_options[nn_single_cb]' type='checkbox' value='1'".(($options['nn_single_cb']) ?" checked='checked'":"")."/> ".__( 'One', 'nnp' );
	}

	/**
	* Displays multiple check boxes.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_mcb(){
		
		$options = get_option( 'nn_sub_advanced_options' );
		if(!isset($options['nn_multi_cb1'])) $options['nn_multi_cb1'] = '';
		if(!isset($options['nn_multi_cb2'])) $options['nn_multi_cb2'] = '';		
		//$text_string = $options['nn_single_cb'];
		echo "<input id='nn_multi_cb1' name='nn_sub_advanced_options[nn_multi_cb1]' type='hidden' value='0' />
		<input id='nn_multi_cb1' name='nn_sub_advanced_options[nn_multi_cb1]' type='checkbox' value='1'".(($options['nn_multi_cb1']) ?" checked='checked'":"")."/>".__( ' One', 'nnp' )."<br/>";
		echo "<input id='nn_multi_cb2' name='nn_sub_advanced_options[nn_multi_cb2]' type='hidden' value='0' />
		<input id='nn_multi_cb2' name='nn_sub_advanced_options[nn_multi_cb2]' type='checkbox' value='1'".(($options['nn_multi_cb2']) ?" checked='checked'":"")."/>".__( ' Two', 'nnp' );
	}

	/**
	* Displays inline radio buttons.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_irb(){
		
		$options = get_option( 'nn_sub_general_options' );
		if(!isset($options['nn_inline_rb'])) $options['nn_inline_rb'] = '';
		echo "<input id='nn_inline_rb1' name='nn_sub_general_options[nn_inline_rb]' type='radio' value='1'".(($options['nn_inline_rb'] == '1') ?" checked='checked'":"")."/>".__( ' One', 'nnp' )."&nbsp;&nbsp;&nbsp;";
		echo "<input id='nn_inline_rb2' name='nn_sub_general_options[nn_inline_rb]' type='radio' value='2'".(($options['nn_inline_rb'] == '2') ?" checked='checked'":"")."/>".__( ' Two', 'nnp' );
	}

	/**
	* Displays single radio button.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_srb(){
		
		$options = get_option( 'nn_sub_general_options' );
		if(!isset($options['nn_std_rb'])) $options['nn_std_rb'] = '';
		echo "<input id='nn_std_rb1' name='nn_sub_general_options[nn_std_rb]' type='radio' value='1'".(($options['nn_std_rb'] == '1') ?" checked='checked'":"")."/>".__( ' One', 'nnp' )."<br/>";
		echo "<input id='nn_std_rb2' name='nn_sub_general_options[nn_std_rb]' type='radio' value='2'".(($options['nn_std_rb'] == '2') ?" checked='checked'":"")."/>".__( ' Two', 'nnp' );
	}

	/**
	* Displays dropdown for selection.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_sel(){
		
		$options = get_option( 'nn_sub_general_options' );
		if(!isset($options['nn_select'])) $options['nn_select'] = '';
		echo "<select id='nn_select' name='nn_sub_general_options[nn_select]'>
				<option></option>
				<option value='sel1'".(($options['nn_select'] == 'sel1') ?" selected='selected'":"").">".__( ' One', 'nnp' )."</option>
				<option value='sel2'".(($options['nn_select'] == 'sel2') ?" selected='selected'":"").">".__( ' Two', 'nnp' )."</option>
				<option value='sel3'".(($options['nn_select'] == 'sel3') ?" selected='selected'":"").">".__( ' Three', 'nnp' )."</option>
			</select>";
	}

	/**
	* Displays everything related to image upload.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_image_up(){
		
		$options = get_option( 'nn_sub_general_options' );
		if(!isset($options['nn_image_uploader'])) $options['nn_image_uploader'] = '';
		?>
		<span class='upload'>
		    <input type='text' id='nn_image_uploader' class='regular-text text-upload' name='nn_sub_general_options[nn_image_uploader]' style='width: 300px' value='<?php echo esc_url( $options["nn_image_uploader"] ); ?>'/>
		    <input type='button' id='nn_image_uploader_button' class='button button-upload' value="<?php _e( 'Upload an image', 'nnp' ) ?>"/></br>
		    <img style='max-width: 300px; display: block;' src='<?php echo esc_url( $options["nn_image_uploader"] ); ?>' class='preview-upload' />
		</span>
		<?php		
	}

	/**
	* Displays file upload field and handles the file.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_file_up(){
		
		$options = get_option('nn_sub_general_options');
		if(!isset($options['nn_file_uploader'])) $options['nn_file_uploader'] = '';
		echo "<input type='file' name='nn_sub_general_options[nn_file_uploader]' id='nn_sub_general_options[nn_file_uploader]' /></form>";
		//add_action( 'admin_footer', array( &$this, 'do_the_upload' ) );
		/*require_once( ABSPATH . 'wp-admin/includes/file.php' );
		$uploadedfile = $_FILES['nn_file_uploader'];
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );*/
		
	}

	/**
	* Displays the WordPress Editor.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_wysiwyg(){
		
		$options = get_option('nn_sub_general_options');
		if(!isset($options['nn_wp_edit'])) $options['nn_wp_edit'] = '';
		$wysiwyg_settings = array('textarea_name' => 'nn_sub_general_options[nn_wp_edit]', 'media_buttons' => false);
		$wysiwyg_content = $options['nn_wp_edit'];
		$wysiwyg_id = 'nn_wp_edit';
		wp_editor( $wysiwyg_content, $wysiwyg_id, $wysiwyg_settings );
	}

	/**
	* Displays the date picker and does the necessary jquery.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_dpicker(){
		
		$options = get_option('nn_sub_general_options');
		if(!isset($options['nn_date_picker'])) $options['nn_date_picker'] = '';
		?>
		<script>
			jQuery(document).ready(function(){
				jQuery('#nn_date_picker').datepicker();	
			});
		</script>
		<?php
		$text_string = $options['nn_date_picker'];
		echo '<input type="date" id="nn_date_picker" name="nn_sub_general_options[nn_date_picker]" />';
	}

	/**
	* Displays the date picker and does the necessary jquery.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_ppicker(){
		
		$options = get_option( 'nn_sub_general_options' );
		$nn_post_types = get_post_types( '','names' );
		$number_of_types = array(count( $nn_post_types ) );
		$i = 0;
		foreach($nn_post_types as $post_type)
		{
			$number_of_types[$i] = $post_type;
			if( !isset( $options['nn_post_picker'.$i] ) ) $options['nn_post_picker'.$i] = '';
			if( ( $number_of_types[$i] != 'revision' ) && ( $number_of_types[$i] != 'attachment' ) )
			{
				echo "<input name='nn_sub_general_options[nn_post_picker".$i."]' type='hidden' value='0' />
				<input name='nn_sub_general_options[nn_post_picker".$i."]' type='checkbox' value='$number_of_types[$i]'".( ( $options['nn_post_picker'.$i] == $number_of_types[$i]) ?" checked='checked'":"")."/>&nbsp;". __( $number_of_types[$i], 'nnp' ) ."<br/>";
				$i++;
			}
		}
	}

	/**
	* Displays the taxonomy picker along with all the available options.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_tpicker(){
		
		$options = get_option( 'nn_sub_general_options' );
		$nn_taxes = get_taxonomies( '', 'names' );
		$number_of_taxes = array(count($nn_taxes));
		$i = 0;
		foreach($nn_taxes as $nn_tax_name){
			$number_of_taxes[$i] = $nn_tax_name;
			$i++;
		}
		$i = 0;
		$number_of_tax_terms = array( count( get_terms( $number_of_taxes ) ) );
		foreach( $number_of_taxes as $nn_tax_name )
		{
			$nn_tax_terms = get_terms($nn_tax_name);
			if( !empty( $nn_tax_terms ) )
			{
				echo $nn_tax_name.'<br/>';
				foreach ( $nn_tax_terms as $nn_term_name ) 
				{
					$number_of_tax_terms[$i] = $nn_term_name;
					$nn_term_name = $number_of_tax_terms[$i]->name;
					if( !isset( $options['nn_tax_picker'.$i] ) ) $options['nn_tax_picker'.$i] = '';
					echo "<input name='nn_sub_general_options[nn_tax_picker".$i."]' type='hidden' value='0' />
					<input name='nn_sub_general_options[nn_tax_picker".$i."]' type='checkbox' value='$nn_term_name'".( ( $options['nn_tax_picker'.$i] == $nn_term_name ) ?" checked='checked'":"")."/>&nbsp;".__( $nn_term_name, 'nnp' )."&nbsp;&nbsp;&nbsp;";
					$i++;
				}
				echo '<br/></br>';
			}
		}
	}

	/**
	* Displays color picker.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_cpicker(){

		$options = get_option('nn_sub_advanced_options');
		if(!isset($options['nn_color_picker'])) $options['nn_color_picker'] = '';
		$text_string = $options['nn_color_picker'];
		echo "<input type='text' value='$text_string' class='wp-color-picker-field' data-default-color='#ffffff' name='nn_sub_advanced_options[nn_color_picker]' />";
	}

	/**
	* Validation if any can be added here.
	* 
	* @since  0.1.0
	* @access public
	* @return void
	*/
	function nn_sub_validate_options( $input ) {
		return $input;
	}
}

/* An object of the class */
new Tabbed_Menu();
?>
