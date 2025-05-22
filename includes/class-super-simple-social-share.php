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

    private function get_current_post_title()
    {
        // Try to get the actual post being viewed, not the theme builder template
        $queried_object = get_queried_object();

        // If we have a post object from the query, use its title
        if ($queried_object && isset($queried_object->post_title)) {
            return $queried_object->post_title;
        }

        // If we're on a singular post/page, try to get the title
        if (is_singular()) {
            global $post;
            if ($post && isset($post->post_title)) {
                return $post->post_title;
            }
        }

        // Fallback to wp_title or site title
        $title = wp_title('', false);
        if (empty($title)) {
            $title = get_bloginfo('name');
        }

        return $title;
    }

    private function get_instagram_url($options)
    {
        $instagram_username = isset($options['instagram_username']) ? $options['instagram_username'] : '';

        if (empty($instagram_username)) {
            return '#'; // Return # if no username is set
        }

        // Clean the username (remove @ if present and any spaces)
        $username = trim(str_replace('@', '', $instagram_username));

        return 'https://www.instagram.com/' . $username . '/';
    }

    private function get_linkedin_url($options, $current_url, $post_title)
    {
        $linkedin_profile = isset($options['linkedin_profile']) ? $options['linkedin_profile'] : '';

        // If profile URL is provided, use it instead of sharing
        if (!empty($linkedin_profile)) {
            return esc_url($linkedin_profile);
        }

        // Fall back to sharing attempt (may not work reliably)
        return 'https://www.linkedin.com/feed/?shareActive=true&text=' . urlencode($post_title . ' ' . $current_url);
    }

    private function get_linkedin_tooltip($options)
    {
        $linkedin_profile = isset($options['linkedin_profile']) ? $options['linkedin_profile'] : '';

        if (!empty($linkedin_profile)) {
            return 'Visit LinkedIn Profile';
        }

        return 'Share on LinkedIn';
    }

    private function get_brand_colors()
    {
        return array(
            'facebook' => '#4267B2',
            'twitter' => '#1DA1F2',
            'pinterest' => '#E60023',
            'email' => '#666666', // Dark gray for email
            'linkedin' => '#0A66C2',
            'instagram' => '#405DE6'
        );
    }

    private function get_icon_color($network, $options)
    {
        $use_brand_colors = isset($options['use_brand_colors']) ? $options['use_brand_colors'] : false;

        if ($use_brand_colors) {
            $brand_colors = $this->get_brand_colors();
            return isset($brand_colors[$network]) ? $brand_colors[$network] : $options['icon_color'];
        }

        return isset($options['icon_color']) ? $options['icon_color'] : '#000000';
    }

    public function social_share_shortcode($atts)
    {
        $options = get_option('ssss_options');
        $icon_color = isset($options['icon_color']) ? $options['icon_color'] : '#000000';
        $icon_size = isset($options['icon_size']) ? $options['icon_size'] : '24';
        $icon_order = isset($options['icon_order']) ? $options['icon_order'] : 'facebook,twitter,pinterest,email,linkedin,instagram';
        $horizontal_align = isset($options['horizontal_align']) ? $options['horizontal_align'] : 'left';
        $vertical_align = isset($options['vertical_align']) ? $options['vertical_align'] : 'top';
        $layout_direction = isset($options['layout_direction']) ? $options['layout_direction'] : 'horizontal';

        // Get the current URL using the correct method
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        // Get the actual post title (handle theme builders like Divi)
        $post_title = $this->get_current_post_title();

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
                'url' => 'https://twitter.com/intent/tweet?url=' . urlencode($current_url) . '&text=' . urlencode($post_title),
                'tooltip' => 'Share on Twitter',
                'enabled' => isset($options['enable_twitter']) ? $options['enable_twitter'] : true
            ),
            'pinterest' => array(
                'icon' => 'fa-square-pinterest',
                'icon_type' => 'fab',
                'url' => 'https://pinterest.com/pin/create/button/?url=' . urlencode($current_url) . '&media=' . urlencode(get_the_post_thumbnail_url()) . '&description=' . urlencode($post_title),
                'tooltip' => 'Share on Pinterest',
                'enabled' => isset($options['enable_pinterest']) ? $options['enable_pinterest'] : true
            ),
            'email' => array(
                'icon' => 'fa-envelope',
                'icon_type' => 'fas',
                'url' => 'mailto:?subject=' . urlencode($post_title) . '&body=' . urlencode($current_url),
                'tooltip' => 'Share via Email',
                'enabled' => isset($options['enable_email']) ? $options['enable_email'] : true
            ),
            'linkedin' => array(
                'icon' => 'fa-linkedin',
                'icon_type' => 'fab',
                'url' => $this->get_linkedin_url($options, $current_url, $post_title),
                'tooltip' => $this->get_linkedin_tooltip($options),
                'enabled' => isset($options['enable_linkedin']) ? $options['enable_linkedin'] : true
            ),
            'instagram' => array(
                'icon' => 'fa-square-instagram',
                'icon_type' => 'fab',
                'url' => $this->get_instagram_url($options),
                'tooltip' => 'Follow on Instagram',
                'enabled' => isset($options['enable_instagram']) ? $options['enable_instagram'] : false
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
        $direction_class = 'ssss-' . $layout_direction;

        $output = '<div class="ssss-social-share ' . esc_attr($alignment_class) . ' ' . esc_attr($direction_class) . '">';
        foreach ($ordered_networks as $network => $data) {
            $network_color = $this->get_icon_color($network, $options);
            $output .= sprintf(
                '<a href="%s" class="ssss-social-icon" target="_blank" rel="noopener noreferrer" data-tooltip="%s">
                    <i class="%s %s" style="color: %s; font-size: %spx;"></i>
                </a>',
                esc_url($data['url']),
                esc_attr($data['tooltip']),
                esc_attr($data['icon_type']),
                esc_attr($data['icon']),
                esc_attr($network_color),
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

        add_settings_field(
            'instagram_username',
            'Instagram Username',
            array($this, 'instagram_username_callback'),
            $this->plugin_name,
            'ssss_main_section'
        );

        add_settings_field(
            'linkedin_profile',
            'LinkedIn Profile URL',
            array($this, 'linkedin_profile_callback'),
            $this->plugin_name,
            'ssss_main_section'
        );

        add_settings_field(
            'layout_direction',
            'Layout Direction',
            array($this, 'layout_direction_callback'),
            $this->plugin_name,
            'ssss_main_section'
        );

        add_settings_field(
            'use_brand_colors',
            'Use Brand Colors',
            array($this, 'use_brand_colors_callback'),
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

        if (isset($input['instagram_username'])) {
            $new_input['instagram_username'] = sanitize_text_field($input['instagram_username']);
        }

        if (isset($input['linkedin_profile'])) {
            $new_input['linkedin_profile'] = esc_url_raw($input['linkedin_profile']);
        }

        if (isset($input['layout_direction'])) {
            $new_input['layout_direction'] = sanitize_text_field($input['layout_direction']);
        }

        if (isset($input['use_brand_colors'])) {
            $new_input['use_brand_colors'] = true;
        } else {
            $new_input['use_brand_colors'] = false;
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
            'instagram' => 'Instagram (Links to Profile)'
        );

        foreach ($networks as $network => $label) {
            $enabled = isset($options['enable_' . $network]) ? $options['enable_' . $network] : ($network === 'instagram' ? false : true);
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

    public function instagram_username_callback()
    {
        $options = get_option('ssss_options');
        $username = isset($options['instagram_username']) ? $options['instagram_username'] : '';
        echo '<input type="text" id="instagram_username" name="ssss_options[instagram_username]" value="' . esc_attr($username) . '" class="regular-text" placeholder="yourusername" />';
        echo '<p class="description">Enter your Instagram username (without @). This will link to your Instagram profile instead of sharing content.</p>';
    }

    public function linkedin_profile_callback()
    {
        $options = get_option('ssss_options');
        $profile = isset($options['linkedin_profile']) ? $options['linkedin_profile'] : '';
        echo '<input type="url" id="linkedin_profile" name="ssss_options[linkedin_profile]" value="' . esc_attr($profile) . '" class="regular-text" placeholder="https://www.linkedin.com/in/yourprofile" />';
        echo '<p class="description">Enter your LinkedIn profile URL. If provided, LinkedIn will link to your profile instead of attempting to share (recommended due to LinkedIn API limitations).</p>';
    }

    public function layout_direction_callback()
    {
        $options = get_option('ssss_options');
        $direction = isset($options['layout_direction']) ? $options['layout_direction'] : 'horizontal';
        ?>
        <select name="ssss_options[layout_direction]" id="layout_direction">
            <option value="horizontal" <?php selected($direction, 'horizontal'); ?>>Horizontal</option>
            <option value="vertical" <?php selected($direction, 'vertical'); ?>>Vertical</option>
        </select>
        <p class="description">Choose whether to display social icons in a horizontal row or vertical column.</p>
    <?php
    }

    public function use_brand_colors_callback()
    {
        $options = get_option('ssss_options');
        $use_brand_colors = isset($options['use_brand_colors']) ? $options['use_brand_colors'] : false;
    ?>
        <label>
            <input type="checkbox" name="ssss_options[use_brand_colors]" value="1" <?php checked($use_brand_colors, true); ?> />
            Use each network's brand color instead of custom color
        </label>
        <p class="description">When enabled, each icon will use its official brand color (Facebook blue, X blue, Pinterest red, etc.)</p>
<?php
    }

    public function generate_preview()
    {
        // Generate a preview using current settings but with disabled links
        $options = get_option('ssss_options');
        $icon_color = isset($options['icon_color']) ? $options['icon_color'] : '#000000';
        $icon_size = isset($options['icon_size']) ? $options['icon_size'] : '24';
        $icon_order = isset($options['icon_order']) ? $options['icon_order'] : 'facebook,twitter,pinterest,email,linkedin,instagram';
        $horizontal_align = isset($options['horizontal_align']) ? $options['horizontal_align'] : 'left';
        $vertical_align = isset($options['vertical_align']) ? $options['vertical_align'] : 'top';
        $layout_direction = isset($options['layout_direction']) ? $options['layout_direction'] : 'horizontal';

        // Define social networks (same as in shortcode)
        $social_networks = array(
            'facebook' => array(
                'icon' => 'fa-square-facebook',
                'icon_type' => 'fab',
                'tooltip' => 'Share on Facebook',
                'enabled' => isset($options['enable_facebook']) ? $options['enable_facebook'] : true
            ),
            'twitter' => array(
                'icon' => 'fa-square-x-twitter',
                'icon_type' => 'fab',
                'tooltip' => 'Share on Twitter',
                'enabled' => isset($options['enable_twitter']) ? $options['enable_twitter'] : true
            ),
            'pinterest' => array(
                'icon' => 'fa-square-pinterest',
                'icon_type' => 'fab',
                'tooltip' => 'Share on Pinterest',
                'enabled' => isset($options['enable_pinterest']) ? $options['enable_pinterest'] : true
            ),
            'email' => array(
                'icon' => 'fa-envelope',
                'icon_type' => 'fas',
                'tooltip' => 'Share via Email',
                'enabled' => isset($options['enable_email']) ? $options['enable_email'] : true
            ),
            'linkedin' => array(
                'icon' => 'fa-linkedin',
                'icon_type' => 'fab',
                'tooltip' => $this->get_linkedin_tooltip($options),
                'enabled' => isset($options['enable_linkedin']) ? $options['enable_linkedin'] : true
            ),
            'instagram' => array(
                'icon' => 'fa-square-instagram',
                'icon_type' => 'fab',
                'tooltip' => 'Follow on Instagram',
                'enabled' => isset($options['enable_instagram']) ? $options['enable_instagram'] : false
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
        $direction_class = 'ssss-' . $layout_direction;

        $output = '<div class="ssss-social-share ssss-preview-disabled ' . esc_attr($alignment_class) . ' ' . esc_attr($direction_class) . '">';
        foreach ($ordered_networks as $network => $data) {
            $network_color = $this->get_icon_color($network, $options);
            $output .= sprintf(
                '<span class="ssss-social-icon ssss-preview-icon" data-tooltip="%s" title="%s">
                    <i class="%s %s" style="color: %s; font-size: %spx;"></i>
                </span>',
                esc_attr($data['tooltip']),
                esc_attr($data['tooltip']),
                esc_attr($data['icon_type']),
                esc_attr($data['icon']),
                esc_attr($network_color),
                esc_attr($icon_size)
            );
        }
        $output .= '</div>';

        return $output;
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

        // Load FontAwesome for the preview
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');

        // Load public CSS for preview
        wp_enqueue_style(
            $this->plugin_name . '-public',
            SSSS_PLUGIN_URL . 'public/css/super-simple-social-share.css',
            array(),
            $this->version,
            'all'
        );

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
