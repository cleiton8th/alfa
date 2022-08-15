<?php
/*
* Plugin Name: WP Alfa Contacts
* Description: Plugin test php alfa
* Version:     1.0
* Author:      Cleiton
* Text Domain: wpalfa
*/

defined( 'ABSPATH' ) or die( 'error' );

require plugin_dir_path( __FILE__ ) . 'includes/metabox-p1.php';

function wpalfa_custom_admin_styles() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'wpalfa_custom_admin_styles');


global $wpalfa_db_version;
$wpalfa_db_version = '1.1.0'; 


function wpalfa_install()
{
    global $wpdb;
    global $wpalfa_db_version;

    $table_name     = $wpdb->prefix . 'alfapessoas'; 
    $table_contacts = $wpdb->prefix . 'alfacontatos'; 


    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      name VARCHAR (50) NOT NULL,
      email VARCHAR(100) NOT NULL,     
      PRIMARY KEY  (id)
    );";

    $sql2 = "CREATE TABLE " . $table_contacts . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        country_code VARCHAR (3) NOT NULL,
        phone VARCHAR(9) NOT NULL,     
        PRIMARY KEY  (id)
      );";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($sql2);

    add_option('wpalfa_db_version', $wpalfa_db_version);

    $installed_ver = get_option('wpalfa_db_version');
    if ($installed_ver != $wpalfa_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          name VARCHAR (50) NOT NULL,
          email VARCHAR(100) NOT NULL,
          PRIMARY KEY  (id)
        );";
        $sql2 = "CREATE TABLE " . $table_contacts . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            country_code VARCHAR (3) NOT NULL,
            phone VARCHAR(9) NOT NULL,     
            PRIMARY KEY  (id)
          );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql2);

        update_option('wpalfa_db_version', $wpalfa_db_version);
    }
}

register_activation_hook(__FILE__, 'wpalfa_install');


function wpalfa_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'alfapessoas'; 

}

register_activation_hook(__FILE__, 'wpalfa_install_data');


function wpalfa_update_db_check()
{
    global $wpalfa_db_version;
    if (get_site_option('wpalfa_db_version') != $wpalfa_db_version) {
        wpalfa_install();
    }
}

add_action('plugins_loaded', 'wpalfa_update_db_check');



if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Custom_Table_Alfa_List_Table extends WP_List_Table
 { 
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'person',
            'plural'   => 'people',
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function column_name($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="?page=people_form&id=%s">%s</a>', $item['id'], __('Edit', 'wpalfa')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'wpalfa')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'name'      => __('Name', 'wpalfa'),
            'email'     => __('E-Mail', 'wpalfa'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('name', true),
            'email'     => array('email', true),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alfapessoas'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alfapessoas'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

class Custom_Table_Alfa_Contact_List_Table extends WP_List_Table
 { 
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'contact',
            'plural'   => 'contacts',
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_phone($item)
    {
        return '<em>' . $item['phone'] . '</em>';
    }


    function column_name($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="?page=contacts_form&id=%s">%s</a>', $item['id'], __('Edit', 'wpalfa')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'wpalfa')),
        );

        return sprintf('%s %s',
            $item['country_code'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'country_code'      => __('countrycode', 'wpalfa'),
            'phone'     => __('phone', 'wpalfa'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'country_code'      => array('country_code', true),
            'phone'     => array('phone', true),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alfacontatos'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alfacontatos'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'country_code';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function wpalfa_admin_menu()
{
    add_menu_page(__('Contacts', 'wpalfa'), __('Contacts', 'wpalfa'), 'activate_plugins', 'contacts', 'wpalfa_people_page_handler');

    add_submenu_page('contacts', __('People', 'wpalfa'), __('People', 'wpalfa'), 'activate_plugins', 'people', 'wpalfa_people_page_handler');
    add_submenu_page('contacts', __('Add new Person', 'wpalfa'), __('Add new Person', 'wpalfa'), 'activate_plugins', 'people_form', 'wpalfa_people_form_page_handler');
    add_submenu_page('contacts', __('Contact', 'wpalfa'), __('Contact', 'wpalfa'), 'activate_plugins', 'contacts', 'wpalfa_contacts_page_handler');
    add_submenu_page('contacts', __('Add new Contact', 'wpalfa'), __('Add new Contact', 'wpalfa'), 'activate_plugins', 'contacts_form', 'wpalfa_contacts_form_page_handler');
}

add_action('admin_menu', 'wpalfa_admin_menu');


function wpalfa_validate_person($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Name is required', 'wpalfa');
    if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('E-mail is in wrong format', 'wpalfa');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function wpalfa_validate_contact($item)
{
    $messages = array();

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}
