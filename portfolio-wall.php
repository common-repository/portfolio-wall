<?php
/*
 * Plugin Name: Portfolio Wall
 * Plugin URI: http://rojait.com/plugins/portfolio-wall/
 * Description: This WordPress plugin, gives you the opportunity to display your portfolio details. The plugin is as easy to use just use the shortcode. It's best responsive portfolio plugin for your Wordpress site.
 * Version: 1.0
 * Author: Mahmudul Islam
 * Author URI: http://rojait.com
 * Text Domain: myplugin-page
 * License: MIT
 */

/*  Copyright 2015  Mahmudul Islam  (email : info.rojait@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Adding Latest jQurey from Wordpress */
function portfolio_wall_wp_jquery() {
		wp_enqueue_script('jquery');
}
add_action('init','portfolio_wall_wp_jquery');

/* Some Set-up*/
define('portfolio_wall_wp_enqueue', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/');

wp_enqueue_style('portfolio-wall-main', portfolio_wall_wp_enqueue.'css/style.css');

// Color Picker

add_action( 'admin_enqueue_scripts', 'portfolio_color_pickr_function' );
function portfolio_color_pickr_function( $hook_suffix ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'my-script-handle', plugins_url('js/portfolio-color-pickr.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}
//Demo images from behance.net
// Image Support
add_theme_support('post-thumbnails', array ('post', 'filterable_portfolio') );
add_image_size('filterable-thumb', 300, 250); // For Thumbnail Image

/* Custom Post */

function filterable_portfolio_wall_post_types() {
		
		$labels = array(
        'name'               => 'Portfolio Walls',
        'singular_name'      => 'Portfolio Wall',
        'menu_name'          => 'Portfolio Walls',
        'name_admin_bar'     => 'Portfolio Wall',
        'add_new'            => 'Add New Portfolio',
        'add_new_item'       => 'Add New Portfolio',
        'new_item'           => 'New Portfolio',
        'edit_item'          => 'Edit Portfolio',
        'view_item'          => 'View Portfolio',
        'all_items'          => 'All Portfolio',
        'search_items'       => 'Search Portfolios',
        'parent_item_colon'  => 'Parent Portfolios:',
        'not_found'          => 'No portfolios found.',
        'not_found_in_trash' => 'No portfolios found in Trash.',
    );
    
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-screenoptions',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'portfolios' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 3,
        'supports'           => array( 'title', 'editor', 'thumbnail' , 'excerpt'),
		'taxonomies'	 	 => array('category', 'post_tag')
		
  );
	register_post_type ('filterable_portfolio', $args);
}
add_action ('init', 'filterable_portfolio_wall_post_types'); 


function filterable_portfolio_wall_rewrite_flush() {
    filterable_portfolio_wall_post_types();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'filterable_portfolio_wall_rewrite_flush' );


/*excerpt length*/
function portfolio_excerpt_length( $length ) {
	return 10;
}
add_filter( 'excerpt_length', 'portfolio_excerpt_length', 999 );





/*shortcode*/

 function filterable_get_portfolio() {
	$portfolio= '<div id="posts">';
	$efs_query="post_type=filterable_portfolio&posts_per_page=-1";
	query_posts($efs_query);
	if (have_posts()) : while (have_posts()) : the_post();
		$protfolio_image = get_the_post_thumbnail ($post->ID, 'filterable-thumb');
		$portfolio.='  <div class="portfolio_post">
		'.$protfolio_image.'
    <div class="post-content">
      <h2>'.get_the_title().'</h2>
      <p>'.get_the_excerpt().'</p>
     
      <a href="'.get_permalink().'">'.get_the_title().'</a> 
	  </div>
  </div>		
		';
		endwhile; endif; wp_reset_query();
		$portfolio.='</div><div class="hidden"></div>';
		return $portfolio;
	}	
	/*Add the shortcode for the slider for use in editor ***/
	function get_portfolio ($atts, $content=null){
		$portfolio = filterable_get_portfolio();
		return $portfolio;
	}
add_shortcode ('portfoliowall','get_portfolio');

 
/*===============================================
    Protfolio Options page start 
=================================================*/
function protfolio_wall_options_page()  
{  
	add_menu_page('Protfolio Options', 'Protfolio Options', 'manage_options', 'protfolio-settings','protfolio_options_framework', plugins_url( '/images/icon.png',  __FILE__ ), 4 );  
}  
add_action('admin_menu', 'protfolio_wall_options_page');

// Default options values
$protfolio_options_framework = array(
	'title_color' => '#000000',    /* Default title color*/
	'title_align' => 'right',    /* Default title alignment*/
	'title_font_size' => '20px',    /* Default title font size*/
	'hover_color' => '#23282D',    /* Protfolio hover background default color*/
	'content_color' => '#ffffff',    /* Protfolio hover default text color*/
	'content_area' => '50%',    /* Protfolio hover default text area*/
	'border_top' => 'none',    /* Protfolio hover top border*/
	'border_bottom' => 'none',    /* Protfolio hover bottom border*/
	'border_left' => 'none',    /* Protfolio hover left border*/
	'border_right' => '1px solid #ffffff'   /* Protfolio hover right border*/

);

if ( is_admin() ) : // Load only if we are viewing an admin page

function protfolio_register_settings() {
	// Register settings and call sanitation functions
	register_setting( 'protfolio_p_options', 'protfolio_options_framework', 'protfolio_validate_options' );
}

add_action( 'admin_init', 'protfolio_register_settings' );


// Function to generate options page
function protfolio_options_framework() {
	global $protfolio_options_framework;

	if ( ! isset( $_REQUEST['updated'] ) )
		$_REQUEST['updated'] = false; // This checks whether the form has just been submitted. ?>

	<div class="wrap">

	
	<h2> Protfolio Options Page </h2>

	<?php if ( false !== $_REQUEST['updated'] ) : ?>
	<div class="updated fade"><p><strong><?php _e('Options saved' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>

	<form method="post" action="options.php">

	<?php $settings = get_option( 'protfolio_options_framework', $protfolio_options_framework ); ?>
	
	<?php settings_fields( 'protfolio_p_options' );
	/* This function outputs some hidden fields required by the form,
	including a nonce, a unique number used to ensure the form has been submitted from the admin page
	and not somewhere else, very important for security */ ?>

	
	<table class="form-table"><!-- Grab a hot cup of coffee, yes we're using tables! -->

		<tr valign="top">
			<th scope="row"><label for="title_color">Title Color</label></th>
			<td>
				<input id="color_picker" class="protfolio_color_picker" type="text" name="protfolio_options_framework[title_color]" value="<?php echo stripslashes($settings['title_color']); ?>" />
				<p class="description">Change the title color from color picker or HEX code. Example: #ffffff  </p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="title_align">Title Alignment</label></th>
			<td>
<input id="title_align" type="text" name="protfolio_options_framework[title_align]" value="<?php echo stripslashes($settings['title_align']); ?>" placeholder="Default or Blank will Right Aligmet"/>
				<p class="description">Change the title alignment. Example: left / right / center.   </p>
			</td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="title_font_size">Title Font Size</label></th>
			<td>
<input id="title_font_size" type="text" name="protfolio_options_framework[title_font_size]" value="<?php echo stripslashes($settings['title_font_size']); ?>" placeholder="Default or Blank will 20px"/>
				<p class="description">Change the title font size. Example: 20px.   </p>
			</td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="hover_color">Hover Background Color</label></th>
			<td>
<input id="hover_color"  class="protfolio_color_picker"  type="text" name="protfolio_options_framework[hover_color]" value="<?php echo stripslashes($settings['hover_color']); ?>" />
				<p class="description">Change the portfolio background color from color picker or HEX code. Example: #23282D  </p>
			</td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="content_color">Hover text Color</label></th>
			<td>
<input id="content_color" class="protfolio_color_picker" type="text" name="protfolio_options_framework[content_color]" value="<?php echo stripslashes($settings['content_color']); ?>" />
				<p class="description">Change the portfolio hover text color from color picker or HEX code. Example: #3E0B4C  </p>
			</td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="content_area">Hover text area</label></th>
			<td>
<input id="content_area" type="text" name="protfolio_options_framework[content_area]" value="<?php echo stripslashes($settings['content_area']); ?>" placeholder="Default or Blank will 50% width" />
				<p class="description"> Change the portfolio hover text area. Example: 50% </p>
			</td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="border_top">Hover text area border top</label></th>
			<td>
<input id="border_top" type="text" name="protfolio_options_framework[border_top]" value="<?php echo stripslashes($settings['border_top']); ?>" placeholder="Default or Blank will none"/>
				<p class="description">Change the portfolio hover text area top border. Example: 1px solid #ffffff   </p>
			</td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="border_bottom">Hover text area border bottom</label></th>
			<td>
<input id="border_bottom" type="text" name="protfolio_options_framework[border_bottom]" value="<?php echo stripslashes($settings['border_bottom']); ?>" placeholder="Default or Blank will none"/>
				<p class="description">Change the portfolio hover text area bottom border. Example: 1px solid #ffffff   </p>
			</td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="border_left">Hover text area border left</label></th>
			<td>
<input id="border_left" type="text" name="protfolio_options_framework[border_left]" value="<?php echo stripslashes($settings['border_left']); ?>" placeholder="Default or Blank will none"/>
				<p class="description">Change the portfolio hover text area left border. Example: 1px solid #ffffff   </p>
			</td>
		</tr>
	
		<tr valign="top">
			<th scope="row"><label for="border_right">Hover text area border right</label></th>
			<td>
<input id="border_right" type="text" name="protfolio_options_framework[border_right]" value="<?php echo stripslashes($settings['border_right']); ?>" placeholder="Default or Blank will 1px solid #ffffff"/>
				<p class="description">Change the portfolio hover text area border. Example: 1px solid #ffffff   </p>
			</td>
		</tr>
	
	</table>

	<p class="submit"><input type="submit" class="button-primary" value="Save Options" /></p>

	</form>

	</div>

	<?php
}
// Function to generate options page end

function protfolio_validate_options( $input ) {
	global $protfolio_options_framework;

	$settings = get_option( 'protfolio_options_framework', $protfolio_options_framework );
	
	// We strip all tags from the text field, to avoid vulnerablilties like XSS

	$input['title_color'] = wp_filter_post_kses( $input['title_color'] );
	$input['title_align'] = wp_filter_post_kses( $input['title_align'] );
	$input['title_font_size'] = wp_filter_post_kses( $input['title_font_size'] );
	$input['hover_color'] = wp_filter_post_kses( $input['hover_color'] );
	$input['content_color'] = wp_filter_post_kses( $input['content_color'] );
	$input['content_area'] = wp_filter_post_kses( $input['content_area'] );
	$input['border_top'] = wp_filter_post_kses( $input['border_top'] );
	$input['border_bottom'] = wp_filter_post_kses( $input['border_bottom'] );
	$input['border_left'] = wp_filter_post_kses( $input['border_left'] );
	$input['border_right'] = wp_filter_post_kses( $input['border_right'] );
		
	return $input;
}

endif;  // EndIf is_admin

/*===============================================
    Protfolio jQuery Settings 
=================================================*/
function protfolio_script_activate() { ?>

<?php global $protfolio_options_framework; $protfolio_settings = get_option( 'protfolio_options_framework', $protfolio_options_framework ); ?>

	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('.portfolio_post h2').css({"color":"<?php echo $protfolio_settings['title_color']; ?>"});			 
			jQuery('.portfolio_post h2').css({"text-align":"<?php echo $protfolio_settings['title_align']; ?>"});			 
			jQuery('.portfolio_post h2').css({"font-size":"<?php echo $protfolio_settings['title_font_size']; ?>"});			 
			jQuery('.portfolio_post').css({"background":"<?php echo $protfolio_settings['hover_color']; ?>"});			 
			jQuery('.post-content').css({"color":"<?php echo $protfolio_settings['content_color']; ?>"});			 
			jQuery('.portfolio_post p').css({"width":"<?php echo $protfolio_settings['content_area']; ?>"});			 
			jQuery('.portfolio_post p').css({"border-top":"<?php echo $protfolio_settings['border_top']; ?>"});			 
			jQuery('.portfolio_post p').css({"border-bottom":"<?php echo $protfolio_settings['border_bottom']; ?>"});			 
			jQuery('.portfolio_post p').css({"border-left":"<?php echo $protfolio_settings['border_left']; ?>"});			 
			jQuery('.portfolio_post p').css({"border-right":"<?php echo $protfolio_settings['border_right']; ?>"});			 
		});	
	</script>
<?php
}
add_action('wp_head', 'protfolio_script_activate');

