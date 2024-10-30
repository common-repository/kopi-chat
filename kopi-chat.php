<?php
/**
 * Plugin Name:         Kopi Chat
 * Plugin URI:          https://app.kopi.chat/integration
 * Description:         A WordPress plugin to save and embed KopiChat chatbot in website.
 * Version:             1.1.2
 * Requires at least:   5.0
 * Requires PHP:        7.3.5
 * Author:              Kopi Chat
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 **/

// Add the admin menu
function kpctb_menu() {
    add_menu_page(
        'Kopi Chat Settings',
        'Kopi Chat',
        'manage_options',
        'kpctb_settings',
        'kpctb_settings_page',
        'dashicons-admin-generic',
        100
    );
}

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', 'kpctb_menu');

// Register and define the settings
function kpctb_register_settings() {
    register_setting('kpctb_settings_group', 'kpctb_embedded_code');
    register_setting('kpctb_settings_group', 'kpctb_security_key');
    register_setting('kpctb_settings_group', 'kpctb_channel_id');
}

add_action('admin_init', 'kpctb_register_settings');

// Create the settings page
function kpctb_settings_page() {
    // Generate MD5 token on the server side
    $timestamp = time();
    $token = md5(get_option("kpctb_security_key") . $timestamp . get_option("kpctb_channel_id"));

    $kpctb_sso_url = urldecode('https://app.kopi.chat/api/wp/chatroom/chat/?token='. $token.'&id='.get_option("kpctb_channel_id").'&timestamp='.$timestamp);
    ?>
    <div class="wrap">
        <h1>Kopi Chat Settings</h1>

        <form method="post" action="options.php">
            <?php settings_fields('kpctb_settings_group'); ?>
            <?php do_settings_sections('kpctb_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Plugin URL</th>
                    <td><textarea name="kpctb_embedded_code" rows="5" cols="50"><?php echo esc_attr(get_option('kpctb_embedded_code')); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Security Key</th>
                    <td><input type="text" name="kpctb_security_key" value="<?php echo esc_attr(get_option('kpctb_security_key')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Channel ID</th>
                    <td><input type="text" name="kpctb_channel_id" value="<?php echo esc_attr(get_option('kpctb_channel_id')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <!-- Button to open a new window -->
        <button id="kopi-chat-bot-open-window" class="button">Login to platform</button>

        <script>
            // JavaScript to handle button click and open a new window
            document.getElementById('kopi-chat-bot-open-window').addEventListener('click', function () {
                
                // Open a new window with the specified URL and query parameters
                var newWindow = window.open('https://app.kopi.chat/api/wp/chatroom/chat/?token=<?php echo esc_js($token) ?>&id=<?php echo esc_js(get_option("kpctb_channel_id")) ?>&timestamp=<?php echo esc_js($timestamp) ?>', '_blank');

                // Check if the new window was successfully opened
                if (newWindow) {
                    // Do something if the new window was opened successfully
                } else {
                    // Handle if the new window couldn't be opened
                    console.error('Failed to open a new window.');
                }
            });
        </script>
    </div>
    <?php
}

// Display the embedded code on the front-end
function kpctb_bot_embedded_code() {
    wp_enqueue_script( 'kpctb-code', get_option('kpctb_embedded_code'));
}

function kpctb_add_id_to_bot_script( $tag, $handle ) {
    if ( 'kpctb-code' !== $handle ) {
        return $tag;
    }
    return str_replace('src', 'data-c-id="'.esc_attr(get_option("kpctb_channel_id")).'" src', $tag );
}

add_filter( 'script_loader_tag', 'kpctb_add_id_to_bot_script', 10, 3 );
add_action('wp_footer', 'kpctb_bot_embedded_code');
