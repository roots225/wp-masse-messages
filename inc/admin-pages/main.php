<?php
/**
 * Add all files for admin pages
 */

 require_once(dirname(__FILE__). '/mass-messages-table-view.php');

/** Add masse message to menu */
if (!function_exists('masse_messages_admin_pages')) {
  function masse_messages_admin_pages () {
    add_menu_page( esc_html__( 'Masse Message', 'masse-messages' ), esc_html__( 'Masse Messages', 'masse-messages' ), 'manage_options', 'masse_messages', 'masse_messages_table_render_view_pages', plugins_url( 'images/booking.png', dirname(__FILE__) ), 100);
		//$table_view_page = add_submenu_page( 'masse_messages', esc_html__( 'Table View', 'masse-messages' ), esc_html__( 'Table View', 'masse-messages' ), 'manage_options', 'masse_messages', 'lordcros_core_room_booking_table_view_render_pages' );
		//add_action( 'admin_print_scripts-' . $table_view_page, 'lordcros_core_room_booking_admin_enqueue_scripts' );
  }

  add_action('admin_menu', 'masse_messages_admin_pages');
}

