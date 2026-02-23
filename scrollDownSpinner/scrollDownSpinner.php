<?php



use Elementor\Controls_Manager;



class Scroll_Down_Spinner extends \Elementor\Widget_Base {

    public function get_name() {
        return 'scroll_down_spinner';
    }

    public function get_title() {
        return esc_html__('Scroll Down Spinner', 'lp-widgets');
    }

    public function get_icon() {
        return 'eicon-spinner';
    }

    public function get_categories(){
        return ['general'];
    }

    public function get_keywords() {
        return ['scroll', 'down', 'spinner'];
    }

    public function get_style_depends() {
        return ['scrollDown'];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Impostazioni', 'lp-widgets'),
            ]
        );

        $this->add_control(
            'dimension',
            [
                'label'       => esc_html__('Dimensione', 'lp-widgets'),
                'type'        => Controls_Manager::TEXT,
                'default'     => '120',
                'description' => esc_html__("Only numbers allowed", 'lp-widgets'),
            ]
        );

        $this->add_control(
            'color_switch',
            [
                'label' => esc_html__('Colore', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Light', 'lp-widgets'),
                'label_off' => esc_html__('Dark', 'lp-widgets'),
                'return_value' => 'dark',
                'default' => 'light',
                'description' => esc_html__("Default is Light; switch on to make it Dark", 'lp-widgets'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Assegna bianco o nero in base allo switch
        $color = $settings['color_switch'] === 'dark' ? '' : 'inverse';

        if (empty($settings['dimension'])) {
            return;
        }
?>
        <img decoding="async" class="looped-animation-rotate <?php echo esc_attr($color); ?>" height="<?php echo esc_attr($settings['dimension']); ?>" width="<?php echo esc_attr($settings['dimension']); ?>" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/widgets/scrollDownSpinner/scrollDownSpinner.png'); ?>" alt="Scroll to see more content">
<?php
    }
}
