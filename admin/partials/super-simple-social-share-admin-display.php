<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    <form method="post" action="options.php">
        <?php
        settings_fields('ssss_options');
        do_settings_sections($this->plugin_name);
        submit_button();
        ?>
    </form>

    <div class="ssss-admin-info">
        <h3>How to Use</h3>
        <p>Use the shortcode <code>[social_share]</code> to display the social sharing icons anywhere on your site.</p>

        <h3>Example</h3>
        <p>Add this to your post or page:</p>
        <pre>[social_share]</pre>

        <h3>Features</h3>
        <ul>
            <li>Simple, clean social sharing icons</li>
            <li>Customizable icon color</li>
            <li>Tooltips on hover</li>
            <li>Responsive design</li>
            <li>Uses FontAwesome icons</li>
        </ul>
    </div>
</div>