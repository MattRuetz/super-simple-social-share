<?php

class Super_Simple_Social_Share
{
    protected $loader;
    protected $plugin_name;
    protected $version;

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
    }

    private function define_public_hooks()
    {
        add_shortcode('social_share', array($this, 'social_share_shortcode'));
    }

    public function social_share_shortcode($atts)
    {
        $options = get_option('ssss_options');
        $icon_color = isset($options['icon_color']) ? $options['icon_color'] : '#000000';

        $social_networks = array(
            'facebook' => array(
                'icon' => 'fa-square-facebook',
                'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode(get_permalink()),
                'tooltip' => 'Share on Facebook'
            ),
            'twitter' => array(
                'icon' => 'fa-square-x-twitter',
                'url' => 'https://twitter.com/intent/tweet?url=' . urlencode(get_permalink()) . '&text=' . urlencode(get_the_title()),
                'tooltip' => 'Share on Twitter'
            ),
            'pinterest' => array(
                'icon' => 'fa-square-pinterest',
                'url' => 'https://pinterest.com/pin/create/button/?url=' . urlencode(get_permalink()) . '&media=' . urlencode(get_the_post_thumbnail_url()) . '&description=' . urlencode(get_the_title()),
                'tooltip' => 'Share on Pinterest'
            ),
            'email' => array(
                'icon' => 'fa-envelope',
                'url' => 'mailto:?subject=' . urlencode(get_the_title()) . '&body=' . urlencode(get_permalink()),
                'tooltip' => 'Share via Email'
            ),
            'instagram' => array(
                'icon' => 'fa-square-instagram',
                'url' => '#',
                'tooltip' => 'Follow on Instagram'
            )
        );

        $output = '<div class="ssss-social-share">';
        foreach ($social_networks as $network => $data) {
            $output .= sprintf(
                '<a href="%s" class="ssss-social-icon" target="_blank" rel="noopener noreferrer" data-tooltip="%s">
                    <i class="fab %s" style="color: %s;"></i>
                </a>',
                esc_url($data['url']),
                esc_attr($data['tooltip']),
                esc_attr($data['icon']),
                esc_attr($icon_color)
            );
        }
        $output .= '</div>';

        return $output;
    }

    public function add_plugin_admin_menu()
    {
        add_options_page(
            'Super Simple Social Share Settings',
            'Social Share',
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
    }

    public function validate_options($input)
    {
        $new_input = array();

        if (isset($input['icon_color'])) {
            $new_input['icon_color'] = sanitize_hex_color($input['icon_color']);
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

    public function display_plugin_admin_page()
    {
        include_once SSSS_PLUGIN_DIR . 'admin/partials/super-simple-social-share-admin-display.php';
    }

    public function run()
    {
        // Plugin is running
    }
}
