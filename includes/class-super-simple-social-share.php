<?php

class Super_Simple_Social_Share
{
    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $admin;

    public function __construct()
    {
        $this->version = SSSS_VERSION;
        $this->plugin_name = 'super-simple-social-share';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies()
    {
        // Initialize admin class
        $this->admin = new Super_Simple_Social_Share_Admin($this->plugin_name, $this->version);

        // Load FontAwesome
        add_action('wp_enqueue_scripts', array($this, 'enqueue_fontawesome'));
    }

    public function enqueue_fontawesome()
    {
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
        wp_enqueue_style($this->plugin_name, SSSS_PLUGIN_URL . 'public/css/super-simple-social-share.css', array(), $this->version);
        wp_enqueue_script($this->plugin_name, SSSS_PLUGIN_URL . 'public/js/super-simple-social-share.js', array('jquery'), $this->version, true);
    }

    private function define_admin_hooks()
    {
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    private function define_public_hooks()
    {
        add_shortcode('social_share', array($this, 'social_share_shortcode'));
    }

    public function social_share_shortcode($atts)
    {
        $options = get_option('ssss_options');
        $icon_color = isset($options['icon_color']) ? $options['icon_color'] : '#000000';
        $icon_size = isset($options['icon_size']) ? $options['icon_size'] : '24';
        $icon_order = isset($options['icon_order']) ? $options['icon_order'] : 'facebook,twitter,pinterest,email,linkedin,instagram';
        $horizontal_align = isset($options['horizontal_align']) ? $options['horizontal_align'] : 'left';
        $vertical_align = isset($options['vertical_align']) ? $options['vertical_align'] : 'top';

        // Get the current URL using the correct method
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $social_networks = array(
            'facebook' => array(
                'icon' => 'fa-square-facebook',
                'icon_type' => 'fab',
                'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($current_url),
                'tooltip' => 'Share on Facebook',
                'enabled' => isset($options['enable_facebook']) ? $options['enable_facebook'] : true
            ),
            'twitter' => array(
                'icon' => 'fa-square-x-twitter',
                'icon_type' => 'fab',
                'url' => 'https://twitter.com/intent/tweet?url=' . urlencode($current_url) . '&text=' . urlencode(get_the_title()),
                'tooltip' => 'Share on Twitter',
                'enabled' => isset($options['enable_twitter']) ? $options['enable_twitter'] : true
            ),
            'pinterest' => array(
                'icon' => 'fa-square-pinterest',
                'icon_type' => 'fab',
                'url' => 'https://pinterest.com/pin/create/button/?url=' . urlencode($current_url) . '&media=' . urlencode(get_the_post_thumbnail_url()) . '&description=' . urlencode(get_the_title()),
                'tooltip' => 'Share on Pinterest',
                'enabled' => isset($options['enable_pinterest']) ? $options['enable_pinterest'] : true
            ),
            'email' => array(
                'icon' => 'fa-envelope',
                'icon_type' => 'fas',
                'url' => 'mailto:?subject=' . urlencode(get_the_title()) . '&body=' . urlencode($current_url),
                'tooltip' => 'Share via Email',
                'enabled' => isset($options['enable_email']) ? $options['enable_email'] : true
            ),
            'linkedin' => array(
                'icon' => 'fa-linkedin',
                'icon_type' => 'fab',
                'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($current_url),
                'tooltip' => 'Share on LinkedIn',
                'enabled' => isset($options['enable_linkedin']) ? $options['enable_linkedin'] : true
            ),
            'instagram' => array(
                'icon' => 'fa-square-instagram',
                'icon_type' => 'fab',
                'url' => '#',
                'tooltip' => 'Follow on Instagram',
                'enabled' => isset($options['enable_instagram']) ? $options['enable_instagram'] : true
            )
        );

        // Sort networks based on order setting and filter enabled ones
        $order_array = explode(',', $icon_order);
        $ordered_networks = array();
        foreach ($order_array as $network) {
            if (isset($social_networks[$network]) && $social_networks[$network]['enabled']) {
                $ordered_networks[$network] = $social_networks[$network];
            }
        }

        $alignment_class = 'ssss-align-' . $horizontal_align . ' ssss-valign-' . $vertical_align;

        $output = '<div class="ssss-social-share ' . esc_attr($alignment_class) . '">';
        foreach ($ordered_networks as $network => $data) {
            $output .= sprintf(
                '<a href="%s" class="ssss-social-icon" target="_blank" rel="noopener noreferrer" data-tooltip="%s">
                    <i class="%s %s" style="color: %s; font-size: %spx;"></i>
                </a>',
                esc_url($data['url']),
                esc_attr($data['tooltip']),
                esc_attr($data['icon_type']),
                esc_attr($data['icon']),
                esc_attr($icon_color),
                esc_attr($icon_size)
            );
        }
        $output .= '</div>';

        return $output;
    }

    public function add_plugin_admin_menu()
    {
        // Add top-level menu
        add_menu_page(
            'Super Simple Social Share',
            'Social Share',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-share',
            30
        );

        // Add submenu page (same as main menu)
        add_submenu_page(
            $this->plugin_name,
            'Settings',
            'Settings',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page')
        );
    }

    public function register_settings()
    {
        register_setting(
            'ssss_options',
            'ssss_options',
            array($this, 'validate_options')
        );

        add_settings_section(
            'ssss_main_section',
            'Social Share Settings',
            array($this, 'section_callback'),
            $this->plugin_name
        );

        add_settings_field(
            'icon_color',
            'Icon Color',
            array($this, 'icon_color_callback'),
            $this->plugin_name,
            'ssss_main_section'
        );

        add_settings_field(
            'icon_size',
            'Icon Size (px)',
            array($this, 'icon_size_callback'),
            $this->plugin_name,
            'ssss_main_section'
        );

        add_settings_field(
            'icon_order',
            'Icon Order',
            array($this, 'icon_order_callback'),
            $this->plugin_name,
            'ssss_main_section'
        );

        add_settings_field(
            'horizontal_align',
            'Horizontal Alignment',
            array($this, 'horizontal_align_callback'),
            $this->plugin_name,
            'ssss_main_section'
        );

        add_settings_field(
            'vertical_align',
            'Vertical Alignment',
            array($this, 'vertical_align_callback'),
            $this->plugin_name,
            'ssss_main_section'
        );

        add_settings_field(
            'enable_icons',
            'Enable/Disable Icons',
            array($this, 'enable_icons_callback'),
            $this->plugin_name,
            'ssss_main_section'
        );
    }

    public function validate_options($input)
    {
        $new_input = array();

        if (isset($input['icon_color'])) {
            $new_input['icon_color'] = sanitize_hex_color($input['icon_color']);
        }

        if (isset($input['icon_size'])) {
            $new_input['icon_size'] = absint($input['icon_size']);
        }

        if (isset($input['icon_order'])) {
            $new_input['icon_order'] = sanitize_text_field($input['icon_order']);
        }

        if (isset($input['horizontal_align'])) {
            $new_input['horizontal_align'] = sanitize_text_field($input['horizontal_align']);
        }

        if (isset($input['vertical_align'])) {
            $new_input['vertical_align'] = sanitize_text_field($input['vertical_align']);
        }

        // Validate enable/disable options
        $networks = array('facebook', 'twitter', 'pinterest', 'email', 'linkedin', 'instagram');
        foreach ($networks as $network) {
            $new_input['enable_' . $network] = isset($input['enable_' . $network]) ? true : false;
        }

        return $new_input;
    }

    public function section_callback()
    {
        echo '<p>Configure your social share settings below:</p>';
    }

    public function icon_color_callback()
    {
        $options = get_option('ssss_options');
        $color = isset($options['icon_color']) ? $options['icon_color'] : '#000000';
        echo '<input type="color" id="icon_color" name="ssss_options[icon_color]" value="' . esc_attr($color) . '" />';
    }

    public function icon_size_callback()
    {
        $options = get_option('ssss_options');
        $size = isset($options['icon_size']) ? $options['icon_size'] : '24';
        echo '<input type="number" id="icon_size" name="ssss_options[icon_size]" value="' . esc_attr($size) . '" min="12" max="48" step="1" />';
    }

    public function icon_order_callback()
    {
        $options = get_option('ssss_options');
        $order = isset($options['icon_order']) ? $options['icon_order'] : 'facebook,twitter,pinterest,email,linkedin,instagram';
        echo '<input type="text" id="icon_order" name="ssss_options[icon_order]" value="' . esc_attr($order) . '" class="regular-text" />';
    }

    public function horizontal_align_callback()
    {
        $options = get_option('ssss_options');
        $align = isset($options['horizontal_align']) ? $options['horizontal_align'] : 'left';
?>
        <select name="ssss_options[horizontal_align]" id="horizontal_align">
            <option value="left" <?php selected($align, 'left'); ?>>Left</option>
            <option value="center" <?php selected($align, 'center'); ?>>Center</option>
            <option value="right" <?php selected($align, 'right'); ?>>Right</option>
        </select>
    <?php
    }

    public function vertical_align_callback()
    {
        $options = get_option('ssss_options');
        $align = isset($options['vertical_align']) ? $options['vertical_align'] : 'top';
    ?>
        <select name="ssss_options[vertical_align]" id="vertical_align">
            <option value="top" <?php selected($align, 'top'); ?>>Top</option>
            <option value="bottom" <?php selected($align, 'bottom'); ?>>Bottom</option>
        </select>
        <?php
    }

    public function enable_icons_callback()
    {
        $options = get_option('ssss_options');
        $networks = array(
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'pinterest' => 'Pinterest',
            'email' => 'Email',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram'
        );

        foreach ($networks as $network => $label) {
            $enabled = isset($options['enable_' . $network]) ? $options['enable_' . $network] : true;
        ?>
            <div class="ssss-checkbox-item">
                <input type="checkbox"
                    id="enable_<?php echo esc_attr($network); ?>"
                    name="ssss_options[enable_<?php echo esc_attr($network); ?>]"
                    value="1"
                    <?php checked($enabled, true); ?> />
                <label for="enable_<?php echo esc_attr($network); ?>"><?php echo esc_html($label); ?></label>
            </div>
<?php
        }
    }

    public function display_plugin_admin_page()
    {
        include_once SSSS_PLUGIN_DIR . 'admin/partials/super-simple-social-share-admin-display.php';
    }

    public function enqueue_admin_styles($hook)
    {
        // Only load on our plugin's pages
        if (strpos($hook, $this->plugin_name) === false) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name . '-admin',
            SSSS_PLUGIN_URL . 'admin/css/super-simple-social-share-admin.css',
            array(),
            $this->version,
            'all'
        );

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            SSSS_PLUGIN_URL . 'admin/js/super-simple-social-share-admin.js',
            array('jquery'),
            $this->version,
            false
        );
    }

    public function run()
    {
        // Plugin is running
    }
}
