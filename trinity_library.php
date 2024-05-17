<?php
/*
 * Plugin Name:       Trinity Library
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       web-based book management system for Trinity library's librarians.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Gaurav Khandelwal
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       trinity-library
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Trinity_Library')):
    class Trinity_Library
    {
        function __construct()
        {
            $this->load_textdomain();
            $this->define_constants();
            require_once (TRINTY_LIBRARY_PATH . 'post-types/class.trinty-library-cpt.php');
            $Trinity_Library_Post_Type = new Trinity_Library_Post_Type();
        }
        public function define_constants()
        {
            define('TRINTY_LIBRARY_PATH', plugin_dir_path(__FILE__));
            define('TRINTY_LIBRARY_URL', plugin_dir_url(__FILE__));
            define('TRINTY_LIBRARY_VERSION', '0.1.0');
        }

        public function load_textdomain()
        {
            load_plugin_textdomain(
                'trinity-library',
                false,
                dirname(plugin_basename(__FILE__)) . '/languages/'
            );
        }
        public static function activation_hook()
        {
            update_option('rewrite_rules', '');

            global $wpdb;
            global $tl_db_version;

            $table_name = $wpdb->prefix . 'bookmanagementmeta';

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL DEFAULT '0',
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            name tinytext NOT NULL,
            text text NOT NULL,
            url varchar(55) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            add_option('tl_db_version', $tl_db_version);

        }
        public static function deactivation_hook()
        {

            flush_rewrite_rules();
            unregister_post_type('book-management');
            global $wpdb;
            $table_name = $wpdb->prefix . 'bookmanagementmeta';
            $sql = "DROP TABLE IF EXISTS $table_name";
            $wpdb->query($sql);
            delete_option("tl_db_version");
        }
        public static function uninstall_hook()
        {
            $posts = get_posts(
                array(
                    'post_type' => 'book-management',
                    'number_posts' => -1,
                    'post_status' => 'any'
                )
            );

            foreach ($posts as $post) {
                wp_delete_post($post->ID, true);
            }
        }
    }
endif;
if (class_exists('Trinity_Library')):
    register_activation_hook(__FILE__, array('Trinity_Library', 'activation_hook'));
    register_deactivation_hook(__FILE__, array('Trinity_Library', 'deactivation_hook'));
    register_uninstall_hook(__FILE__, array('Trinity_Library', 'uninstall_hook'));
    $Trinity_Library = new Trinity_Library();

endif;