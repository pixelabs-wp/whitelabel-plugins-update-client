<?php

/**
 * Plugin Name: WPThumbs Updater
 * Description: A WordPress plugin for plugin updates
 * Version: 1.0.0
 * Author: Syed Ali Haider Hamdani
 * Author URI: https://www.fiverr.com/syedali157/
 * License: MIT
 */

// Define plugin constants
define('WP_THUMBS_URL', plugin_dir_url(__FILE__));
define('WP_THUMBS_DIR', plugin_dir_path(__FILE__));

// Include necessary files
require_once WP_THUMBS_DIR . 'includes/wp-thumbs-updater-admin.php';
require_once WP_THUMBS_DIR . 'includes/wp-thumbs-updater-functions.php';
require_once WP_THUMBS_DIR . 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if (!function_exists('get_plugin_data')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
// Hook to add admin menu
add_action('admin_menu', 'wp_thumbs_updater_add_admin_menu');

function WP_THUMBSactivate_plugin()
{
    // Call the function to create the database table upon activation.
    create_update_info_table();

    // You can also perform other activation tasks here if needed.
}

register_activation_hook(__FILE__, 'WP_THUMBSactivate_plugin');


function wp_thumbs_updater_check_for_updates()
{

    $api_url = 'https://www.96down.com/wp-json/wpthumbs/plugin-updates';
	
 	$username = get_option('wp_thumbs_updater_username');
    $password = get_option('wp_thumbs_updater_password');
	
    $response = wp_safe_remote_get($api_url, array(
        'headers' => array(
            'custom' => 'Bearer '.base64_encode($username.":".$password),
        ),
    ));

    if (is_wp_error($response)) {
        // Handle error - unable to fetch updates
        return;
    }

    $data = wp_remote_retrieve_body($response);
    $updates = json_decode($data);
    if (!empty($updates)) {
		if(isset($updates->code) && $updates->code == "unauthorized"){
			return;
		}
        foreach ($updates as $update) {
            if (isset($update->new_version)) {
                $slugData = $update->plugin_slug;
                $slug  = trim(explode("/", $slugData)[0]);
                $slugFilename  = trim(explode("/", $slugData)[1]);



                $pluginData = wp_thumbs_get_plugin($slug . "/" . $slugFilename);


                if ($pluginData && $pluginData["Version"] != $update->new_version) {



                    $file_name = $slug . ".json";
                    $upload_dir = wp_upload_dir(); // Get the uploads directory.



                    $name = $update->plugin_name;
                    $download_url = $update->updated_plugin_url;
                    $version = $update->new_version;
                    $author = $pluginData["AuthorName"];
                    $sections = array(
                        'description' => $pluginData["Description"],
                    );

                    $json = generate_plugin_info_json($name, $slug, $download_url, $version, $author, $sections);


                    $file_url = write_json_to_file($json, $file_name);
                    if ($file_url) {

                        $pluginFile = WP_PLUGIN_DIR . '/' . $slug . "/" . $slugFilename;
                        $myUpdateChecker = PucFactory::buildUpdateChecker(
                            $file_url,
                            $pluginFile,
                            $slug
                        );
                    }
                }
            }
        }
    }
}



add_action('init', 'wp_thumbs_updater_check_for_updates');
