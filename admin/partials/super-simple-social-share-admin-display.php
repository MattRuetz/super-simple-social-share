<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="ssss-admin-wrap">
    <div class="ssss-admin-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    </div>

    <form method="post" action="options.php">
        <?php
        settings_fields('ssss_options');
        ?>

        <div class="ssss-admin-content">
            <div class="ssss-admin-main">
                <!-- Appearance Section -->
                <div class="ssss-admin-section">
                    <h2>Appearance</h2>

                    <div class="ssss-form-group">
                        <label for="icon_color">Icon Color</label>
                        <?php $this->icon_color_callback(); ?>
                    </div>

                    <div class="ssss-form-group">
                        <label for="icon_size">Icon Size</label>
                        <?php $this->icon_size_callback(); ?>
                        <p class="description">Set the size of the social icons in pixels (12-48px)</p>
                    </div>

                    <div class="ssss-form-group">
                        <label for="horizontal_align">Horizontal Alignment</label>
                        <?php $this->horizontal_align_callback(); ?>
                    </div>

                    <div class="ssss-form-group">
                        <label for="vertical_align">Vertical Alignment</label>
                        <?php $this->vertical_align_callback(); ?>
                    </div>
                </div>

                <!-- Social Networks Section -->
                <div class="ssss-admin-section">
                    <h2>Social Networks</h2>
                    <p>Select which social networks to display and their order.</p>

                    <div class="ssss-form-group">
                        <label>Enable/Disable Networks</label>
                        <?php $this->enable_icons_callback(); ?>
                    </div>

                    <div class="ssss-form-group">
                        <label for="icon_order">Icon Order</label>
                        <?php $this->icon_order_callback(); ?>
                        <p class="description">Enter the order of icons separated by commas. Available options: facebook, twitter, pinterest, email, linkedin, instagram</p>
                    </div>
                </div>

                <div class="ssss-submit-section">
                    <?php submit_button('Save Settings'); ?>
                </div>
            </div>

            <div class="ssss-admin-sidebar">
                <!-- Usage Instructions -->
                <div class="ssss-admin-section">
                    <h2>How to Use</h2>
                    <p>Add the social sharing icons to any post or page using the shortcode:</p>
                    <div class="ssss-preview-section">
                        <code>[social_share]</code>
                    </div>
                </div>

                <!-- Features -->
                <div class="ssss-admin-section">
                    <h2>Features</h2>
                    <ul>
                        <li>Clean, modern social sharing icons</li>
                        <li>Customizable icon color and size</li>
                        <li>Flexible alignment options</li>
                        <li>Tooltips on hover</li>
                        <li>Responsive design</li>
                        <li>FontAwesome icons</li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="ssss-admin-section">
                    <h2>Support</h2>
                    <p>Need help? Check out the documentation or contact support.</p>
                    <p>
                        <a href="https://github.com/mattruetz/super-simple-social-share" target="_blank" class="button">Documentation</a>
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>