<?php 
    /*
    Plugin Name: multi-datepicker
    Plugin URI: 
    Description: 
    Author: Remi Shergold
    Version: 1.0
    Author URI: http://www.remi-shergold.com
    */

if( !class_exists('mdpick') ):
	
class mdpick {
	
	var $all_post_types,
		$table_name,
		$customize_next_query;
	
	public $force_customize_next_query;
		
	function __construct() {
		global $wpdb;
		
		$this->table_name = $wpdb->prefix . "mdp_multi_dates";
		$this->customize_next_query = true;
		$this->force_customize_next_query = false;
		
		if( is_admin() ) {
			register_activation_hook( __FILE__, array( $this, 'install' ) );
			add_action('admin_menu', array($this, 'add_settings_menu_item') );
			add_action('add_meta_boxes', array($this, 'add_meta_box') );
			add_action('save_post', array($this, 'save_meta_box_post') );
			add_action('registered_post_type', array($this, 'get_post_types') );
		} else {
			
			add_action('parse_query', array($this, 'query_custom_before') );
			add_action('posts_request', array($this, 'query_custom_after') );
			
			add_action('posts_fields', array($this, 'query_custom_feilds') );
			add_action('posts_join', array($this, 'query_custom_join') );
			add_action('posts_where', array($this, 'query_custom_where') );
			add_action('posts_groupby', array($this, 'query_custom_group') );
			add_action('posts_orderby', array($this, 'query_custom_order') );
			
		}
	}
	
	function install() {
		global $wpdb;
		$sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
			  post_id bigint(20) unsigned NOT NULL,
			  mdpicker_date datetime NOT NULL,
			  KEY post_id (post_id),
				UNIQUE(post_id,mdpicker_date)
			)";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
	
	/*
		change wp_query for mdp affected post types
	*/
	
	function query_custom_before(){
		if ($this->force_customize_next_query)
			$this->customize_next_query = true;
		elseif ($this->customize_next_query)
			$this->customize_next_query = $this->should_customize_next_query();
	}
	
	function query_custom_after($q){
		$this->customize_next_query = false;
		$this->force_customize_next_query = false;
		return $q;
	}
	
	function query_custom_feilds($feilds){
		if (!$this->customize_next_query) return $feilds;
		$feilds .= ",$this->table_name.mdpicker_date";
		return $feilds;
	}
	
	function query_custom_join($join){
		if (!$this->customize_next_query) return $join;
		global $wpdb;
		$join .= "INNER JOIN $this->table_name ON $this->table_name.post_id = $wpdb->posts.ID";
		return $join;
	}
	
	function query_custom_where($where){
		if (!$this->customize_next_query) return $where;
		$where .= "AND $this->table_name.mdpicker_date >= CURDATE() ";
		return $where;
	}
	
	function query_custom_order($order){
		if (!$this->customize_next_query) return $order;
		$order = "$this->table_name.mdpicker_date";
		return $order;
	}
	
	function query_custom_group($group){
		if (!$this->customize_next_query) return $group;
		global $wp_query;
		$suppress_category_grouping = (array_key_exists('category_name',$wp_query->query) && get_option('mdpick_custom_sort_categories'));
		$suppress_tag_grouping = (array_key_exists('tag',$wp_query->query) && get_option('mdpick_custom_sort_tags'));
		
		return ($suppress_category_grouping || $suppress_tag_grouping) ? '' : $group;
	}
	
	
		//helper
		function should_customize_next_query() {
			global $wp_query;
			
			if (!get_option('mdpick_custom_sort')) return false;
			if (array_key_exists('category_name',$wp_query->query) && !get_option('mdpick_custom_sort_categories')) return false;
			if (array_key_exists('tag',$wp_query->query) && !get_option('mdpick_custom_sort_tags')) return false;
			
			$querying_standard_posts = is_home() || (is_archive() && !is_post_type_archive());
			$should_moify_standard_posts = get_option('mdpick_pt_post');
			
			$querying_custom_posts = is_post_type_archive();
			$should_modify_custom_posts = get_option('mdpick_pt_'.$wp_query->query['post_type']);
			
			$modify_this_query = ($querying_standard_posts && $should_moify_standard_posts) 
															|| ($querying_custom_posts && $should_modify_custom_posts);
			
			return $modify_this_query;

		}

	
	/*
		create interface for admin section
	*/
	function add_settings_menu_item() {
		add_options_page('Multi DatePicker Settings', 'Multi DatePicker', 'manage_options', 'multi-datepicker', array($this, 'show_settings_page') );
	}
	
	function show_settings_page() {

		if($_POST['mdpick_hidden'] == 'Y') {
			$this->update_settings();
			$flash_message = "Settings updated";
		}

		include('html/mdp_settingsPage.php');
	}
	
	function update_settings() {
		foreach ( $this->all_post_types as $post_type ) {
				update_option('mdpick_pt_'.$post_type->name, array_key_exists($post_type->name, $_POST['mdpick_post_type']) );
		}
		
		update_option('mdpick_custom_sort', ($_POST['mdpick_custom_sort']=='Y') ? true : false); 
		update_option('mdpick_custom_sort_tags', ($_POST['mdpick_custom_sort_tags']=='Y') ? true : false);
		update_option('mdpick_custom_sort_categories', ($_POST['mdpick_custom_sort_categories']=='Y') ? true : false);
	}
	
	
	function add_meta_box() {

		foreach ($this->all_post_types as $post_type) {
			if (!get_option('mdpick_pt_'.$post_type->name)) continue;
			
			add_meta_box(
				'mdpick_sectionid',
				__( 'Multi Datepicker', 'mdpick_textdomain' ),
				array($this,'show_meta_box'),
				$post_type->name
			);
		}

	}
	
	function show_meta_box( $post ) {
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		wp_enqueue_script( 'mdpick', plugins_url('js/mdp_metaBox.js', __FILE__), array(), '1.0.1', true );
		include('html/mdp_metaBox.php');
		
	}
	
	function save_meta_box_post( $post_id ) {
		global $wpdb;
		
		// Checks save status
    $is_valid_nonce = ( isset( $_POST[ 'mdpicker_nonce' ] ) && wp_verify_nonce( $_POST[ 'mdpicker_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
		if ( 
			wp_is_post_autosave( $post_id ) ||
			wp_is_post_revision( $post_id ) ||
			!$is_valid_nonce ||
			!isset( $_POST['mdpicker_dates'])
		) return;
		
		//delete old
		$wpdb->delete($this->table_name,array('post_id' => $post_id));
		
		//replace with new
		foreach (explode(',',$_POST['mdpicker_dates']) as $timestamp) {
			$wpdb->insert($this->table_name, array(
				'post_id' => $post_id,
				'mdpicker_date' => date( 'Y-m-d H:i:s', $timestamp/1000 )
			));
		}
		
	}


		// show_meta_box helper
		function get_post_dates($post_id){
			global $wpdb;
			$dates = $wpdb->get_results("SELECT mdpicker_date FROM $this->table_name WHERE post_id = $post_id", OBJECT_K);
			$timestamps = [];
			foreach($dates as $date) 
				$timestamps[] = strtotime($date->mdpicker_date)*1000;
			return implode(',',$timestamps);
		}

		//general helper, gets the names of all post types, used by other functions later
		function get_post_types() {
			$this->all_post_types = get_post_types(array('public'=>true,'show_ui'=>true,'publicly_queryable'=>true),'objects');
			unset($this->all_post_types['attachment']);
		}


		/*
			template helpers
		*/
		
		function the_date() {
			
		}
}
	
$mdpick = new mdpick();


function mdp_next_query() {
	global $mdpick;
	$mdpick->force_customize_next_query = true;
}

function the_mdp_date() {
	global $mdpick;
	$mdpick->the_date();
}

endif; // class_exists check


?>