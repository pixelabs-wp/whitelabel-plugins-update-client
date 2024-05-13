<?php
function create_update_info_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'update_info';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        plugin_name varchar(255) NOT NULL,
        new_version varchar(20) NOT NULL,
        update_url varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function display_update_notification($plugin_name, $new_version, $update_url)
{
    echo '<div class="notice notice-info is-dismissible">';
    echo '<p>A new version of ' . $plugin_name . ' is available. <a href="' . $update_url . '">Update to ' . $new_version . '</a></p>';
    echo '</div>';
}

function insert_update_info($plugin_name, $new_version, $update_url)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'update_info';

    $wpdb->insert(
        $table_name,
        array(
            'plugin_name' => $plugin_name,
            'new_version' => $new_version,
            'update_url' => $update_url,
        )
    );
}

function get_update_info()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'update_info';

    $results = $wpdb->get_results("SELECT * FROM $table_name", OBJECT);

    return $results;
}

function update_update_info($id, $new_version, $update_url)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'update_info';

    $wpdb->update(
        $table_name,
        array(
            'new_version' => $new_version,
            'update_url' => $update_url,
        ),
        array('id' => $id)
    );
}

function delete_update_info($id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'update_info';

    $wpdb->delete(
        $table_name,
        array('id' => $id)
    );
}

// Define the plugin slug for the target plugin (replace 'plugin-slug' with the actual slug).

function wp_thumbs_get_plugin($plugin_slug)
{

    if (is_admin()) {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        if (file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug)) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_slug);
            return $plugin_data;
        } else {
            return false;
        }
    }
}

function wp_thumbs_convert_to_plugin_slug($string)
{
    // Convert to lowercase and replace spaces with hyphens.
    $slug = str_replace(' ', '-', strtolower($string));

    // Remove non-alphanumeric characters (except hyphens).
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

    return $slug;
}

function generate_plugin_info_json($name, $slug, $download_url, $version, $author, $sections)
{
    $plugin_info = array(
        'name' => $name,
        'slug' => $slug,
        'download_url' => $download_url,
        'version' => $version,
        'author' => $author,
        'sections' => $sections,
    );

    return json_encode($plugin_info, JSON_PRETTY_PRINT);
}

function write_json_to_file($json_data, $file_name)
{
    $upload_dir = wp_upload_dir(); // Get the uploads directory.

    // Define the file path based on the provided file name.
    $file_path = $upload_dir['path'] . '/' . $file_name;

    // Write the JSON data to the file.
    $result = file_put_contents($file_path, $json_data);

    if ($result !== false) {
        // Generate the URL of the file.
        $file_url = $upload_dir['url'] . '/' . $file_name;
        return $file_url;
    } else {
        return false; // Failed to write to the file.
    }
}
