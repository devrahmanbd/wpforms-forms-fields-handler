<?php
/*
Plugin Name: WPForms Forms Field Handler
Plugin URI: https://devrahman.com/
Description: Plugin to set allowed, preferred, and blocked countries in WPForms phone number field, and optionally restrict input to numbers only.
Author: Dev Rahman
Version: 1.0
Author URI: https://devrahman.com/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function wpffh_menu() {
    add_options_page(
        'WPForms Forms Field Handler Settings',
        'WPForms Forms Field Handler', 
        'manage_options',
        'wpffh-forms-field-handler',
        'wpffh_settings_page'
    );
}
add_action( 'admin_menu', 'wpffh_menu' );

function wpffh_settings_page() {
    ?>
    <div class="wrap">
        <h1>WPForms Forms Field Handler Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'wpffh-forms-field-handler-group' );
            do_settings_sections( 'wpffh-forms-field-handler-group' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Preferred Countries</th>
                    <td>
                        <input type="text" name="wpffh_preferred_countries" value="<?php echo esc_attr( get_option( 'wpffh_preferred_countries' ) ); ?>" />
                        <p class="description">Comma-separated list of preferred countries (e.g., gb,us,de,in).</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Allowed Countries</th>
                    <td>
                        <input type="text" name="wpffh_allowed_countries" value="<?php echo esc_attr( get_option( 'wpffh_allowed_countries' ) ); ?>" />
                        <p class="description">Comma-separated list of allowed countries (e.g., gb,us,de,in).</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Blocked Countries</th>
                    <td>
                        <input type="text" name="wpffh_blocked_countries" value="<?php echo esc_attr( get_option( 'wpffh_blocked_countries' ) ); ?>" />
                        <p class="description">Comma-separated list of blocked countries (e.g., gb,us,de,in).</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Restrict to Numbers Only</th>
                    <td>
                        <input type="checkbox" name="wpffh_restrict_numbers_only" value="1" <?php checked(1, get_option( 'wpffh_restrict_numbers_only', 0 ), true); ?> />
                        <p class="description">Check this box to restrict the phone field to numbers only.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php wpffh_display_branding(); ?>
    </div>
    <?php
}

function wpffh_settings() {
    register_setting( 'wpffh-forms-field-handler-group', 'wpffh_preferred_countries' );
    register_setting( 'wpffh-forms-field-handler-group', 'wpffh_allowed_countries' );
    register_setting( 'wpffh-forms-field-handler-group', 'wpffh_blocked_countries' );
    register_setting( 'wpffh-forms-field-handler-group', 'wpffh_restrict_numbers_only' );
}
add_action( 'admin_init', 'wpffh_settings' );
function wpffh_enqueue_script() {
    $preferred_countries = get_option( 'wpffh_preferred_countries', '' );
    $allowed_countries = get_option( 'wpffh_allowed_countries', '' );
    $blocked_countries = get_option( 'wpffh_blocked_countries', '' );
    $restrict_numbers_only = get_option( 'wpffh_restrict_numbers_only', 0 );
    ?>
    <script type="text/javascript">
        jQuery(document).on('wpformsReady', function() {
            jQuery('.wpforms-smart-phone-field').each(function() {
                var $el = jQuery(this),
                    iti = $el.data('plugin_intlTelInput'),
                    options;
                
                if (iti.d) {
                    options = Object.assign({}, iti.d);
                } else if (iti.options) {
                    options = Object.assign({}, iti.options);
                }
                
                if (!options) {
                    return;
                }
                
                $el.intlTelInput('destroy');
                
                <?php if ($preferred_countries) : ?>
                options.preferredCountries = ['<?php echo str_replace(',', "','", $preferred_countries); ?>'];
                <?php endif; ?>
                
                <?php if ($allowed_countries) : ?>
                options.onlyCountries = ['<?php echo str_replace(',', "','", $allowed_countries); ?>'];
                <?php endif; ?>
                
                <?php if ($blocked_countries) : ?>
                options.onlyCountries = options.onlyCountries ? options.onlyCountries.filter(country => !['<?php echo str_replace(',', "','", $blocked_countries); ?>'].includes(country)) : iti.getCountryData().map(country => country.iso2).filter(country => !['<?php echo str_replace(',', "','", $blocked_countries); ?>'].includes(country));
                <?php endif; ?>
                
                $el.intlTelInput(options);
                
                $el.siblings('input[type="hidden"]').attr('name', 'wpforms[fields][' + options.hiddenInput + ']');
                
                <?php if ($restrict_numbers_only) : ?>
                $el.addClass('numbersOnly');
                <?php endif; ?>
            });
            
            <?php if ($restrict_numbers_only) : ?>
            jQuery('.numbersOnly').keypress(function(e) {
                var charCode = (e.which) ? e.which : event.keyCode;
                if (String.fromCharCode(charCode).match(/[^0-9]/g)) {
                    return false;
                }
            });
            <?php endif; ?>
        });
    </script>
    <?php
}
add_action( 'wpforms_wp_footer_end', 'wpffh_enqueue_script', 30 );


function wpffh_display_branding() {
    $logo_url = plugin_dir_url(__FILE__) . 'assets/devrahman.png';
    $website_url = 'https://devrahman.com';
    $facebook_url = 'https://facebook.com/devrahmanbd';
    $github_url = 'https://github.com/devrahmanbd';
    $linkedin_url = 'https://linkedin.com/in/devrahmanbd';
    ?>
    <div style="text-align: center; margin-top: 20px;">
        <img src="<?php echo esc_url($logo_url); ?>" alt="Dev Rahman Logo" style="max-height: 50px;"><br>
        <p>
            WPForms Forms Field Handler made with love by <a href="<?php echo esc_url($website_url); ?>" target="_blank">Dev Rahman</a>.<br>
            All rights reserved by <a href="<?php echo esc_url($website_url); ?>" target="_blank">DevRahman.com</a>.<br>
            <a href="<?php echo esc_url($facebook_url); ?>" target="_blank"><img src="<?php echo plugin_dir_url(__FILE__) . 'assets/Facebook.svg'; ?>" alt="Facebook" style="max-height: 20px;"></a> |
            <a href="<?php echo esc_url($github_url); ?>" target="_blank"><img src="<?php echo plugin_dir_url(__FILE__) . 'assets/Github.svg'; ?>" alt="GitHub" style="max-height: 20px;"></a> |
            <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank"><img src="<?php echo plugin_dir_url(__FILE__) . 'assets/Linkedin.svg'; ?>" alt="LinkedIn" style="max-height: 20px;"></a>
        </p>
    </div>
    <?php
}