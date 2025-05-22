<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <div class="ssss-admin-wrap">
        <div class="ssss-admin-header">
            <div class="ssss-header-content">
                <img src="<?php echo SSSS_PLUGIN_URL; ?>admin/images/plugin-logo.png" alt="Super Simple Social Share" class="ssss-plugin-logo" />
                <div class="ssss-header-text">
                    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                    <p class="ssss-version">Version <?php echo SSSS_VERSION; ?></p>
                </div>
            </div>
        </div>

        <form method="post" action="options.php">
            <?php
            settings_fields('ssss_options');
            ?>

            <div class="ssss-admin-content">
                <div class="ssss-admin-main">
                    <!-- Preview Section -->
                    <div class="ssss-admin-section ssss-preview-top">
                        <h2>Preview</h2>
                        <p>This is how your social sharing icons will appear:</p>
                        <div class="ssss-preview-container">
                            <?php echo $this->generate_preview(); ?>
                        </div>
                        <div class="ssss-preview-save">
                            <?php submit_button('Save & Update Preview', 'secondary', 'submit', false); ?>
                            <p class="description">Save changes to see updated preview with your current settings.</p>
                        </div>
                    </div>

                    <!-- Appearance Section -->
                    <div class="ssss-admin-section">
                        <h2>Appearance</h2>

                        <div class="ssss-form-group">
                            <label for="icon_color">Icon Color</label>
                            <?php $this->icon_color_callback(); ?>
                        </div>

                        <div class="ssss-form-group">
                            <label>Brand Colors</label>
                            <?php $this->use_brand_colors_callback(); ?>
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

                        <div class="ssss-form-group">
                            <label for="layout_direction">Layout Direction</label>
                            <?php $this->layout_direction_callback(); ?>
                        </div>
                    </div>

                    <!-- Social Networks Section -->
                    <div class="ssss-admin-section">
                        <h2>Social Networks</h2>
                        <p>Select which social networks to display and their order.</p>

                        <div class="ssss-form-group">
                            <label>Enable/Disable Networks</label>
                            <div class="ssss-checkbox-group">
                                <?php $this->enable_icons_callback(); ?>
                            </div>
                        </div>

                        <div class="ssss-form-group">
                            <label for="icon_order">Icon Order</label>
                            <?php $this->icon_order_callback(); ?>
                            <p class="description">Enter the order of icons separated by commas. Available options: facebook, twitter, pinterest, email, linkedin, instagram</p>
                        </div>

                        <div class="ssss-form-group">
                            <label for="instagram_username">Instagram Username</label>
                            <?php $this->instagram_username_callback(); ?>
                        </div>

                        <div class="ssss-form-group">
                            <label for="linkedin_profile">LinkedIn Profile URL</label>
                            <?php $this->linkedin_profile_callback(); ?>
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
                            <code class="ssss-shortcode-copy" data-shortcode="[social_share]" title="Click to copy">[social_share]</code>
                        </div>
                        <div class="ssss-copy-notification" id="ssss-copy-notification">Shortcode copied to clipboard!</div>
                    </div>

                    <!-- Support -->
                    <div class="ssss-admin-section">
                        <h2>Support</h2>

                        <!-- FAQ Accordion -->
                        <div class="ssss-faq-accordion">
                            <div class="ssss-faq-item">
                                <div class="ssss-faq-question">
                                    <h4>How do I add social sharing to my posts/pages? <span class="ssss-faq-toggle">+</span></h4>
                                </div>
                                <div class="ssss-faq-answer">
                                    <p>Simply add the shortcode <code>[social_share]</code> to any post, page, or widget where you want the social sharing icons to appear.</p>
                                </div>
                            </div>

                            <div class="ssss-faq-item">
                                <div class="ssss-faq-question">
                                    <h4>Why aren't my alignment settings working? <span class="ssss-faq-toggle">+</span></h4>
                                </div>
                                <div class="ssss-faq-answer">
                                    <p>Make sure to save your settings after making changes. If alignment still isn't working, your theme's CSS might be overriding the plugin styles. Try switching to a different alignment option or contact support.</p>
                                </div>
                            </div>

                            <div class="ssss-faq-item">
                                <div class="ssss-faq-question">
                                    <h4>Can I customize which social networks are displayed? <span class="ssss-faq-toggle">+</span></h4>
                                </div>
                                <div class="ssss-faq-answer">
                                    <p>Yes! In the "Social Networks" section, you can enable/disable specific networks and change their display order using the "Icon Order" field.</p>
                                </div>
                            </div>
                        </div>

                        <p>Need help? Check out the documentation or contact support.</p>
                        <p>
                            <a href="https://github.com/mattruetz/super-simple-social-share" target="_blank" class="button">Documentation</a>
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>