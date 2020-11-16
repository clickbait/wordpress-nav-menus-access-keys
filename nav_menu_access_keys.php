<?php
/*
Plugin Name: Access Keys for WordPress Menus
Plugin URI: http://aahacreative.com/our-projects/wordpress-nav-menu-access-keys/
Description: This plugin allows you to add Access Keys to navigation menus. Works with multiple menus. (Use version 1.0 if you need access keys on wp_list_pages).
Author: Aaron Harun
Version: 1.6
Author URI: http://aahacreative.com/
*/

add_action( 'wp_update_nav_menu_item', 'update_access_keys' ); // Hook in here because we can. We don't actually care about any of the data
add_filter( 'walker_nav_menu_start_el', 'access_keys_walker_nav_menu_start_el', 10, 4 );
$access_keys = get_option( 'nav_menu_access_keys' );

/**
* Update the access keys by grabbing the $_POST data
* It's safe because it only runs when WP has validated the
* User Data and the rest of the data.
**/
function update_access_keys ( $id ) {
	global $access_keys;
	static $done_already = false;

	if( $done_already ) //This is called multiple times, so we ignore the rest of the calls.
		return;

	$access_keys[ $id ] = $_POST['menu-item-attr-accesskey'];
	update_option( 'nav_menu_access_keys', $access_keys );
	$done_already = true;
}

/**
* Replacing all that dogmeat JS code from the previous version with
* the new wp_nav_menu_item_custom_fields filter
**/
add_filter( 'wp_nav_menu_item_custom_fields', 'add_access_keys_field_to_item_settings', 10, 5 );
function add_access_keys_field_to_item_settings ( $id, $item, $depth, $args, $menu_id ) {
	global $access_keys, $nav_menu_selected_id;

	if ( isset( $access_keys[ $nav_menu_selected_id ] ) && isset( $access_keys[ $nav_menu_selected_id ][ $id ] ) ) {
		$value = $access_keys[ $nav_menu_selected_id ][ $id ];
	}

	?>
	<div class="field-icon description-wide accesskey-wrap">
		<label for="edit-menu-item-attr-accesskey-<?= $id; ?>">
			Access Key<br/>
			<input type="text" value="<?= $value; ?>" name="menu-item-attr-accesskey[<?= $id; ?>]" class="widefat edit-menu-item-attr-accesskey" id="edit-menu-item-attr-accesskey-<?= $item->ID; ?>"/>
		</label>
	</div>
<?php }


/**
* Hooks into each menu li item and checks if there is an access key
* If there is, just add it. '<a' is Guaranteed to always be there.
* It's the only part that is.
**/
function access_keys_walker_nav_menu_start_el ( $output, $item, $depth, $args ) {
	global $access_keys;

	$locations = get_nav_menu_locations();

	if ( isset( $locations[ $args->theme_location ] ) ) {
		$menu_id = $locations[ $args->theme_location ];
	}

	if ( is_array( $access_keys[ $menu_id ] ) && count( $access_keys[ $menu_id ] ) ) {
		if ( !empty( $access_keys[ $menu_id ][ $item->ID ] ) ){
			$output = str_replace( '<a', '<a accesskey="' . $access_keys[ $menu_id ][ $item->ID ] . '"', $output );
		}

	}

	return $output;
}
