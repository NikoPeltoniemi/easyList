<?php
/**
 *
 * easyList
 *
 * @package easyList
 * @author Niko Peltoniemi
 * @copyright 2017 Niko Peltoniemi
 *
 * Plugin Name: easyList
 * Plugin URI: http://pfn.ovh/easylist
 * Description: Plugin used to create FAQ sections with hideable answers.
 * Author: Niko Peltoniemi
 * Author URI: http://pgn.ovh
 * Version: 0.1.4a
 * Text Domain: pfn-ukk
 *
 */

function pfn_ukk_setup_post_types(){

	$sst_ukk_labels = array(
		'name' => __('FAQ', 'pfn-ukk'),
		'singular_name' => __('FAQ', 'pfn-ukk'),
		'add_new_item' => __('Add New FAQ', 'pfn-ukk'),
		'edit_item' => __('Edit FAQ', 'pfn-ukk'),
		'new_item' => __('New FAQ', 'pfn-ukk'),
		'view_item' => __('View FAQ', 'pfn-ukk'),
		);

	register_post_type( 'pfn-ukk', array('labels' => $sst_ukk_labels, 'public' => true, 'menu_position' => 5, 'menu_icon' => 'dashicons-info') );

	$ukk_heading_labels = array(
		'name' => __('Groups', 'pfn-ukk'),
		'singular_name' => __('Group', 'pfn-ukk'),
		'all_items' => __('All Groups', 'pfn-ukk'),
		'edit_item' => __('Edit Group', 'pfn-ukk'),
		'view_item' => __('View Group', 'pfn-ukk'),
		'update_item' => __('Update Group', 'pfn-ukk'),
		'add_new_item' => __('Add New Group', 'pfn-ukk'),
		'new_item_name' => __('New Group', 'pfn-ukk'),
		'popular_items' => __('Popular Groups', 'pfn-ukk'),		
		);

	register_taxonomy( 'ukk-heading', 'pfn-ukk', array( 'labels' => $ukk_heading_labels, 'public' => true, 'show_admin_column' => true ) );
}
add_action( 'init', 'sst_ukk_setup_post_types');

function pfn_taxonomy_add_new_meta_field(){
	?>
	<div class="form-field">
		<label for="term_meta[ukk_heading_color]"><?php _e('Color override (Hex)', 'pfn-ukk'); ?></label>
		<input type="text" name="term_meta[ukk_heading_color]" value="">
		<p class="description"><?php _e('Enter custom color for question bars', 'pfn-ukk'); ?></p>
	</div>
<?php
}
add_action('ukk-heading_add_form_fields', 'pfn_taxonomy_add_new_meta_field');

function pfn_taxonomy_edit_meta_field($term){
	$t_id = $term->term_id;

	$term_meta = get_option("taxonomy_$t_id");?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[ukk_heading_color]"><?php _e('Color override (Hex', 'pfn-ukk'); ?></label></th>
		<td>
			<input type="text" name="term_meta[ukk_heading_color]" id="term_meta[ukk_heading_color]" value="<?php echo esc_attr($term_meta['ukk_heading_color']) ? esc_attr($term_meta['ukk_heading_color']) : ''; ?>">
			<p class="description"><?php _e('Enter custom color for question bars', 'pfn-ukk'); ?></p>
		</td>
	</tr>
<?php
}
add_action('ukk-heading_edit_form_fields', 'pfn_taxonomy_edit_meta_field');

function save_taxonomy_custom_meta($term_id){
	if(isset($_POST['term_meta'])){
		$t_id = $term_id;
		$term_meta = get_option("taxonomy_$t_id");
		$cat_keys = array_keys($_POST['term_meta']);
		foreach($cat_keys as $key){
			if(isset($_POST['term_meta'][$key])){
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}

		update_option( "taxonomy_$t_id", $term_meta );
	}
}
add_action('edited_ukk-heading', 'save_taxonomy_custom_meta');
add_action('create_ukk-heading', 'save_taxonomy_custom_meta');

function pfn_ukk_install(){

	pfn_ukk_setup_post_types();

	flush_rewrite_rules();

}
register_activation_hook( __FILE__, 'pfn_ukk_install' );

function pfn_ukk_deactivation(){
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pfn_ukk_deactivation' );


function custom_enter_title( $input ) {
    global $post_type;

    if ( 'pfn-ukk' === $post_type ) {
        return __( 'Enter question here', 'pfn-ukk' );
    }

    return $input;
}
add_filter('enter_title_here','custom_enter_title');

function pfn_ukk_shortcode($atts){
	ob_start();

	$atts = shortcode_atts( array('group' => ''), $atts);

	if(isset($atts['group'])){

		$query = new WP_Query(array(
			'post_type' => 'pfn-ukk',
			'posts_per_page' => -1,
			'tax_query' => array(
				'taxonomy' => 'ukk-heading',
				'field' => 'name',
				'terms' => $atts['group']
				),
			'orderby' => 'title',
			'order' => 'ASC'
			)
		);
		//return print_r($query);
		$html = '';
		if($query->have_posts()){
			while($query->have_posts()){
				$query->the_post();
				if(has_term($atts['group'], 'ukk-heading')){
					if(get_term_meta(intval(get_term_by('name','ukk-heading')), 'ukk_heading_color')){
						$color = get_term_meta(intval(get_term_by('name','ukk-heading')), 'ukk_heading_color');
						$html .= '<div class="ukk" style="background-color: '.$color.';">';
					}else{
						$html .= '<div class="ukk">';
					}
					$html .= '<a class="ukk-head" href="#'. get_the_title().'">'. get_the_title().'</a>';
					$html .= '<div class="ukk-answer">';
					$html .= get_the_content();
					$html .= '</div>';
					$html .= '</div>';
				}
			}
		}
	}

	return $html;
}

add_shortcode( 'faq' , 'pfn_ukk_shortcode' );

function pfn_ukk_scripts(){
	wp_register_script( 'wp-easyList-js', plugin_dir_url(__FILE__) . 'public/js/wp_easyList.js', array( 'jquery' ), '1.0.1', true );
	wp_enqueue_style( 'wp-easyList-css', plugin_dir_url( __FILE__ ) . 'public/css/ep-Easylist.css', false, '1.0.2', 'all' );
	wp_enqueue_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js', array( '' ), '2.2.4', true );
	wp_enqueue_script( 'wp-easyList-js' );
}

add_action('init', 'pfn_ukk_scripts');