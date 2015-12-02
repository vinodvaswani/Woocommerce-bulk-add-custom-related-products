<?php
/*
* Plugin Name: WC Bulk Add Custom custom Related Products
* Version: 1.0
* Author: vinod vaswani
* License: GPLv2 or later
*/
// function create_plugin_database_table() {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'bulk_add_related_products';
//     $sql = "CREATE TABLE $table_name (
//         id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
//         product_ids longtext NOT NULL,
//         related_product_ids longtext NOT NULL,
//         PRIMARY KEY  (id)
//         );";
 
//     require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//     dbDelta( $sql );
// } 
// register_activation_hook( __FILE__, 'create_plugin_database_table' );

// filter the arguments of the custom related products query to match those selected, if any
// Remove category based custom related products

/**
 * Check if WooCommerce is active
 **/
if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    function my_admin_notice() {
    ?>
    <div class="update-nag">
        <p><?php _e( '<strong>wc bulk add custom related products</strong> requires the WooCommerce to be work with', 'wc-bulk-add-custom-related-products' ); ?></p>
    </div>
    <?php
}
add_action( 'admin_notices', 'my_admin_notice' );

}

function crp_filter_related_products($args) {
	global $post;
	$related = get_post_meta( $post->ID, '_related_ids', true );
	if($related) { // remove category based filtering
		$args['post__in'] = $related;
	}
	else{
		
		$args['post__in'] = array(0);
	}
	return $args;
}
add_filter( 'woocommerce_related_products_args', 'crp_filter_related_products' );

add_action( 'wp_ajax_load_products', 'add_bulk_load_products' );
add_action( 'wp_ajax_nopriv_load_products', 'add_bulk_load_products' );
function add_bulk_load_products()
{	
    /* Get products by ID */
	$args = array(
        'posts_per_page' => -1,
        'product_cat' => $_POST['p_slug'],
        'post_type' => 'product',
        'orderby' => 'title',
    );
	$the_query = new WP_Query( $args );
	// The Loop
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		if($_POST['section']==1)
		echo '<label for="product-'.get_the_ID().'"><input type="checkbox" class="bulk_product" data-name="'.get_the_title().'" data-id="'.get_the_ID().'" id="product-'.get_the_ID().'" value="'.get_the_ID().'">'.get_the_title().'</label><br>';
		elseif($_POST['section']==2)
		echo '<label for="product-2-'.get_the_ID().'"><input type="checkbox" class="bulk_product_2" data-name-2="'.get_the_title().'" data-id-2="'.get_the_ID().'" id="product-2-'.get_the_ID().'" value="'.get_the_ID().'">'.get_the_title().'</label><br>';
	}
    die();
}

/* Function for get all translated product id from orignal ID */
function get_all_translated_ids($post_id)
{
	global $sitepress;
	$translated_ids = Array();
	if(!isset($sitepress)) return;
	$trid = $sitepress->get_element_trid($post_id, 'post_product');
	$translations = $sitepress->get_element_translations($trid, 'product');
	foreach( $translations as $lang=>$translation)
	{
	    $translated_ids[] = $translation;
	}
	
	return $translated_ids;
}


add_action( 'wp_ajax_manage_load_products', 'manage_load_products' );
add_action( 'wp_ajax_nopriv_manage_load_products', 'manage_load_products' );
function manage_load_products()
{	
    /* Get products by ID */
	$args = array(
        'posts_per_page' => -1,
        'product_cat' => $_POST['p_slug'],
        'post_type' => 'product',
        'orderby' => 'title',
    );
	$the_query = new WP_Query( $args );
	// The Loop
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		echo '<label for="product-'.get_the_ID().'"><input type="radio" name="existing_related" class="existing_related_product" data-name="'.get_the_title().'" data-id="'.get_the_ID().'" id="product-'.get_the_ID().'" value="'.get_the_ID().'">'.get_the_title().'</label><br>';
	}
    die();
}


add_action( 'wp_ajax_get_related_products', 'get_related_products' );
add_action( 'wp_ajax_nopriv_get_related_products', 'get_related_products' );
function get_related_products()
{	

	if(!empty($_POST['product_id']))
	{
		$related_ids = get_post_meta($_POST['product_id'],'_related_ids',true);
	}

	

	if(is_array($related_ids) && !empty($related_ids))
	{

		/* Get products by ID */
		$args = array(
	        'posts_per_page'=> -1,
	        'post_type' 	=> 'product',
	        'orderby' 		=> 'title',
	        'post__in'		=>  $related_ids
	    );
		$the_query = new WP_Query( $args );
		// The Loop
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			echo '<li data-name="'.get_the_title().'" data-product-id="'.$_POST['product_id'].'" data-related-id="'.get_the_ID().'">'.get_the_title().'<span class="remove_related"><img src="'.plugins_url('images/remove.png', __FILE__).'"></span></li>';
		}

	}
	else
	{
		echo "No custom related products found";
	}

    
    die();
}



add_action( 'wp_ajax_remove_related_products', 'remove_related_products' );
add_action( 'wp_ajax_nopriv_remove_related_products', 'remove_related_products' );
function remove_related_products()
{
	if(!empty($_POST['data_product_id']) && !empty($_POST['data_related_id']))
	{	
		$remove_related = false;
		$data_product_id = $_POST['data_product_id'];
		$data_related_id = $_POST['data_related_id'];



		
		$prev_val = get_post_meta($data_product_id,'_related_ids',true);
		if(($key = array_search($data_related_id, $prev_val)) !== false) {
		    unset($prev_val[$key]);
		    $prev_val = array_values($prev_val);
		    if(update_post_meta($data_product_id, '_related_ids', $prev_val))
			$remove_related = true;
		}
		
		


		if($remove_related)
			die(json_encode(array('response'=>'TRUE')));
		else
			die(json_encode(array('response'=>'FALSE')));


	}
	die();
}


add_action( 'wp_ajax_add_related_products', 'add_bulk_related_products' );
add_action( 'wp_ajax_nopriv_add_related_products', 'add_bulk_related_products' );
function add_bulk_related_products()
{	

			$product_ids 			= array();
			$related_product_ids	= array();
			//$prev_val				= array();



			if(!empty($_POST['related_product_ids']))
			{	
				$temp = array();
				$temp = explode(',', $_POST['related_product_ids']);
				$product_ids = $temp;
			}


			if(!empty($_POST['product_ids']))
			{
				$temp = array();
				$temp = explode(',', $_POST['product_ids']);
				$related_product_ids = $temp;
			}




			$process_finished = false;

			
			foreach ($product_ids as $single_id)
			{
				$prev_val = get_post_meta($single_id,'_related_ids',true);

				if(is_array($prev_val))
				{	
					$final_val = array_merge($prev_val, $related_product_ids);
					$final_val = array_unique($final_val);
					$final_val = array_values($final_val);
					update_post_meta($single_id, '_related_ids', $final_val);
					$process_finished = true;
				}
				else
				{
					update_post_meta($single_id, '_related_ids', $related_product_ids);
					$process_finished = true;
				}
			}
			


			if($process_finished)
			{
				die(json_encode(array('response'=>'TRUE')));
			}
    
}

/* Add script and style */
function barp_style() {
	$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
	$params = array(
			// Get the url to the admin-ajax.php file using admin_url()
			'ajaxurl' => admin_url( 'admin-ajax.php', $protocol ),
			'remove_img' => plugins_url('images/remove.png', __FILE__)
		);
	wp_register_style( 'add-bulk-style', plugins_url('css/add-bulk-style.css', __FILE__) );
	wp_enqueue_style('add-bulk-style');

	//wp_register_style( 'bootstrap', plugins_url('css/bootstrap.min.css', __FILE__) );
	//wp_enqueue_style('bootstrap');

	wp_register_script( 'ajax-functions', plugins_url('js/ajax-functions.js', __FILE__) );
	wp_localize_script( 'ajax-functions', 'ajaxObject', $params );
	wp_enqueue_script('ajax-functions');

}
add_action( 'admin_enqueue_scripts', 'barp_style' );
/* Add script and style */


/* Add menu */
function register_my_custom_submenu_page() {
    add_submenu_page( 'woocommerce', 'Bulk add custom related products', 'Bulk add custom related products', 'manage_options', 'bulk-add-related-products', 'bulk_add_related_products_func' ); 
    add_submenu_page( 'woocommerce', 'Manage custom Related Products', 'Manage custom Related Products', 'manage_options', 'manage-related-products', 'manage_related_products_func' ); 
}
add_action('admin_menu', 'register_my_custom_submenu_page',99);
/* Add menu */


function manage_related_products_func()
{ ?>

<div class="manage_bulk_related_container">

<h2>Manage custom Related Products</h2>

<form class="form-horizontal" id="manage-related">
	
	 <div class="container-fluid">
	  <div class="row">
	  <div class="col-md-12">
	  <label class="col-md-12 control-label" for="selectbasic">Select Category</label>
	    <select id="manage_product_category" name="manage_product_category" class="form-control">
	      <option value="">Select Category</option>
	      <?php 
	      $args = array(
				'type'                     => 'post',
				'child_of'                 => 0,
				'parent'                   => '',
				'orderby'                  => 'name',
				'order'                    => 'ASC',
				'hide_empty'               => 1,
				'hierarchical'             => 1,
				'exclude'                  => '',
				'include'                  => '',
				'number'                   => '',
				'taxonomy'                 => 'product_cat',
				'pad_counts'               => false 

			); 
	      $categories = get_categories( $args );

	      foreach ($categories as $cat) {
	      	echo '<option value="'.$cat->slug.'">'.$cat->name.'</option>';
	      }
	      ?>
	    </select>
	    <img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>" style="display:none">
	  </div>
	  </div>
	  
	

  <div class="row" id="manage-select-products" style="display:none">
  	<div class="col-md-12">
  	<label class="col-md-12 control-label" for="selectbasic">Select Products</label>
    <p>Select the product to fetch there custom related products</p>
    <div id="manage_products" name="manage_products" class="form-control" style="width:600px;height:200px; overflow: scroll;">
    </div>
    </div>
  </div>
	
	
	<div class="row">
	  <div class="col-md-12">
	    <img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>" style="display:none" id="related_loader">
	  </div>
	</div>

	<div class="row" id="manage-select-products-fetch" style="display:none">
	  <div class="col-md-12">
  	  <label class="col-md-12 control-label" for="selectbasic">custom Related Products</label>
	    <ul id="fetch-related-products" class="form-control" style="width:600px;height:200px;overflow:scroll">
	    </ul>
	  </div>
	</div>

</div>

</form>

</div>

<?php }



function bulk_add_related_products_func() { ?>

<div class="add_bulk_related_container">


<h2>Bulk add custom related products</h2>
<form class="form-horizontal" id="add-bulk">

<div class="container-fluid">
	<div class="row">
		<div class="col-md-6 left_section">
		<div class="row">
	  <div class="col-md-12">
	  <label class="col-md-12 control-label" for="selectbasic">Select Category</label>
	    <select id="product_category" name="product_category" class="form-control">
	      <option value="">Select Category</option>
	      <?php 
	      $args = array(
				'type'                     => 'post',
				'child_of'                 => 0,
				'parent'                   => '',
				'orderby'                  => 'name',
				'order'                    => 'ASC',
				'hide_empty'               => 1,
				'hierarchical'             => 1,
				'exclude'                  => '',
				'include'                  => '',
				'number'                   => '',
				'taxonomy'                 => 'product_cat',
				'pad_counts'               => false 

			); 
	      $categories = get_categories( $args );

	      foreach ($categories as $cat) {
	      	echo '<option value="'.$cat->slug.'">'.$cat->name.'</option>';
	      }
	      ?>
	    </select>
	    <img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>" style="display:none">
	  </div>
	</div>

	<div class="row" id="select-related-products" style="display:none">
	  <div class="col-md-12">
	  <label class="col-md-12 control-label" for="selectbasic">Select Related Product</label>
	    <p>Select Products that will be related to products in right side. </p>
	    <div id="products" name="products" class="form-control" style="width:600px;height:200px; overflow: scroll;">
	    </div>
	  </div>
	</div>

	<div class="row" id="selected-related-products" style="display:none">
	  <div class="col-md-12">
	  <label class="col-md-12 control-label" for="selectbasic">Selected Related Products</label>
	    <ul id="selected-products" name="selected-products" class="form-control" style="width:600px;height:200px;overflow:scroll">
	    </ul>
	  </div>
	</div>
		</div>

		<div class="col-md-6 right_section">
		<div class="row">
	  <div class="col-md-12">
	  <label class="col-md-12 control-label" for="selectbasic">Select Category</label>
	    <select id="product_category_2" name="product_category_2" class="form-control">
	      <option value="">Select Category</option>
	      <?php 
	      $args = array(
				'type'                     => 'post',
				'child_of'                 => 0,
				'parent'                   => '',
				'orderby'                  => 'name',
				'order'                    => 'ASC',
				'hide_empty'               => 1,
				'hierarchical'             => 1,
				'exclude'                  => '',
				'include'                  => '',
				'number'                   => '',
				'taxonomy'                 => 'product_cat',
				'pad_counts'               => false 

			); 
	      $categories = get_categories( $args );

	      foreach ($categories as $cat) {
	      	echo '<option value="'.$cat->slug.'">'.$cat->name.'</option>';
	      }
	      ?>
	    </select>
	    <img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>" style="display:none">
	  </div>
	</div>


	<div class="row" id="select-related-products-2" style="display:none">
	  <div class="col-md-12">
	  <label class="col-md-12 control-label" for="selectbasic">Select Product</label>
	   	<p>Select those products for which you want to relate. </p>
	    <div id="products_2" name="products_2" class="form-control" style="width:600px;height:200px; overflow: scroll;">
	    </div>
	  </div>
	</div>

	<div class="row" id="selected-related-products-2" style="display:none">
	   <div class="col-md-12">
	   <label class="col-md-12 control-label" for="selectbasic">Selected Products</label>
	    <ul id="selected-products-2" name="selected-products-2" class="form-control" style="width:600px;height:200px;overflow:scroll">
	    </ul>
	  </div>
	</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<input type="submit" value="Add Related" id="add_related">
		<img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>" style="display:none" id="submit_loader">
	  
	</div>
</div>
</form>
</div>
		
<?php } ?>