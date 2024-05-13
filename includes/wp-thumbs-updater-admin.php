<?php
// Admin menu and settings page
function wp_thumbs_updater_add_admin_menu()
{
    add_menu_page(
        'WPThumbs Updater',
        'WPThumbs Updater',
        'manage_options',
        'wp-thumbs-updater-settings',
        'wp_thumbs_updater_settings_page'
    );
}
function wp_thumbs_updater_settings_page()
{
    if (isset($_POST['username']) && isset($_POST['password'])) {
        update_option('wp_thumbs_updater_username', sanitize_text_field($_POST['username']));
        update_option('wp_thumbs_updater_password', sanitize_text_field($_POST['password']));
    }

    $username = get_option('wp_thumbs_updater_username');
    $password = get_option('wp_thumbs_updater_password');

    $api_url = 'https://www.96down.com/wp-json/wpthumbs/plugin-updates';

    $response = wp_safe_remote_get($api_url, array(
        'headers' => array(
            'custom' => 'Bearer ' . base64_encode($username . ":" . $password),
        ),
    ));

    $status = "";
    $data = wp_remote_retrieve_body($response);
    $updates = json_decode($data);
    if (!empty($updates)) {
        if (isset($updates->code) && $updates->code == "unauthorized") {
            $status = "<span style='color:red;'>$updates->message</span>";
        } else {
            $status = "<span style='color:green;'>Active</span>";
        }
    }
    ?>
    <div class="wrap">
        <h2>WPThumbs Updater Settings - <?php echo $status; ?></h2>
        <form method="post" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo esc_attr($username); ?>" class="regular-text"><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value="<?php echo esc_attr($password); ?>" class="regular-text"><br>
            <input type="submit" class="button button-primary" value="Save">
        </form>
    </div>
    <?php
}
