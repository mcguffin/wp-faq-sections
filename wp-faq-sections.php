<?php
/**
* @package wp-faq-sections
* @version 0.1
*/

/*
Plugin Name: WP FAQ-Sections
Plugin URI: https://github.com/mcguffin/wp-faq-sections
Description: Edit FAQs in Wordpress.
Author: Joern Lund
Version: 0.0.1
Author URI: https://github.com/mcguffin

Text Domain: wpfsec
Domain Path: /lang/
*/


function wpfsec_print_faq( $atts , $content ) {
	$atts = wp_parse_args($atts, array(
		'section'=>false,
		'intro'=>false,
	));
	extract($atts);
	
	$get_terms_args = array();
	$get_post_args = array(
		'post_type' => 'faq',
		'posts_per_page' => -1,
		'order_by'=> 'menu_order',
		'order'=>'asc',
		'faq-section' => '',
	);

	$faq_sections = array();
	if ( $section ) {
		$section = explode( ',' , $section );
		foreach ($section as $single_section_slug) {
			$get_terms_args['slug'] = $single_section_slug;
			$faq_sections = array_merge( $faq_sections , get_terms( 'faq-section' ,  $get_terms_args ) );
		}
	} else {
		$faq_sections += get_terms( 'faq-section' ,  $get_terms_args );
	}
	
	
	$return = '';
	foreach ($faq_sections as $fsection) {
		
		$return .= '<section class="faq-section">';
		if ( $intro ) {
			$return .= '<hgroup><h1>'.$fsection->name.'</h1></hgroup>';
			$return .= '<p>'.$fsection->description.'</p>';
		}
		$get_post_args['faq-section'] = $fsection->slug;

		$faqs = get_posts($get_post_args);
	
		foreach ( $faqs as $faq ) {
			$return .= '<article class="faq">';
			$return .= '<header><h1>' . $faq->post_title . '</h1></header>';
			$return .= apply_filters('the_content',$faq->post_content);
			$return .= '</article>';
		}
		$return .= '</section>';
	}
	return $return;
}

function wpfsec_init() {

	
	
	register_post_type( 'faq' , array(
		'label' 		=> __( 'FAQ' , 'wpfsec' ),
		'description' 	=> __( 'Add FAQs to Your site.' , 'wpfsec' ),
		'public'		=> true,
		'exclude_from_search' => false,
		'show_ui'		=> true,
		'show_in_nav_menus'
						=> false,
		'show_in_menu'	=> true,
		'show_in_admin_bar'
						=> false,
		'menu_position'	=> 20,
//		'menu_icon'		=> '', // later
		'capability_type' => 'page',
		'supports'		=> array(
			'title',
			'editor',
			'author',
			//'revisions', // ... maybe
			'page-attributes'
		),
		'taxonomies'	=> array( ),
		'has_archive'	=> true,
		'can_export'	=> true,
		
	));

	register_taxonomy( 'faq-section' , 'faq' , array(
		'label'			=> __( 'FAQ Sections' , 'wpfsec' ),
		'public'		=> true,
		'show_ui'		=> true,
		'show_tagcloud' => false,
		'show_admin_column' => true,
		'hierarchical'	=> false,
		'show_in_nav_menus'	=> true,
	) );
	
	add_shortcode('faq','wpfsec_print_faq');
	
}

//
function wpfsec_flush_rewrite() {
	wpfsec_init();
	flush_rewrite_rules();
	
}
function dump(){
	$a = func_get_args();
	var_dump($a);
	return $a[0];
}

add_action('init' , 'wpfsec_init' );
add_action('plugins_loaded' , 'wpfsec_plugins_loaded' );
function wpfsec_plugins_loaded() {
	load_plugin_textdomain( 'wpfsec' , false , dirname(__FILE__) . '/lang/' );
}
if ( is_admin() ) {
	register_activation_hook( __FILE__ , 'wpfsec_flush_rewrite' );
	add_filter('wp_query','dump');
}

?>