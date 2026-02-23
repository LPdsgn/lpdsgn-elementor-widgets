<?php

use Elementor\Controls_Manager;

class Scroll_Down_Indicator extends \Elementor\Widget_Base
{

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        require_once(get_stylesheet_directory() . '/widgets/scrollDownIndicator/hex_to_rgba.php');
    }

    public function get_name() {
        return 'scroll_down_indicator';
    }

    public function get_title() {
        return esc_html__('Scroll Down Indicator', 'textdomain');
    }

    public function get_icon() {
        return 'eicon-scroll';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_keywords() {
        return ['scroll', 'down', 'indicator'];
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
            'section_id',
            [
                'label'       => esc_html__('ID destinazione', 'lp-widgets'),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'example',
                'description' => esc_html__("By clicking this scroll button, to which section in your page you want to go? Just write that's section ID here such 'my-header'. N.B: No need to add '#'.", 'lp-widgets'),
            ]
        );

        $this->add_control(
            'color_switch',
            [
                'label' => esc_html__('Colore', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Dark', 'lp-widgets'),
                'label_off' => esc_html__('Light', 'lp-widgets'),
                'return_value' => 'light',
                'default' => 'dark',
                'description' => esc_html__("Default is Dark; switch on to make it Light", 'lp-widgets'),
            ]
        );

        /*
        $this->add_control(
            'color',
            [
                'label'     => esc_html__('Colore', 'lp-widgets'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#000',
                'selectors' => [
                    '{{WRAPPER}} .next-section' => '--indicator-color: {{VALUE}};'
                ],
            ]
        );
        */

        /*
        $this->add_control(
            'opacity',
            [
                'label' => esc_html__('Opacity', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.01,
                    ],
                ],
                'default' => [
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .next-section' => 'opacity: {{SIZE}};',
                ],
            ]
        );
        */

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        # $color = $settings['color'];
        # $opacity = $settings['opacity']['size']; // Assicurati di accedere alla chiave corretta per il valore dell'opacità

        // Assegna bianco o nero in base allo switch
        $color = $settings['color_switch'] === 'light' ? '#000000' : '#ffffff';

        // Converti il colore esadecimale in RGBA con l'opacità specificata
        $rgba_color = hex_to_rgba($color, 0.5);

        if (empty($settings['section_id'])) {
            return;
        }
?>
        <div class="next-section-wrap mouse-wheel">
            <a href="#<?php echo esc_attr($settings['section_id']); ?>" class="next-section skip-hash" style="--indicator-color: <?php echo esc_attr($rgba_color); ?>;">
                <span class="screen-reader-text">Navigate to the next section</span>
                <svg class="scroll-icon" viewBox="0 0 30 45" enable-background="new 0 0 30 45">
                    <path class="scroll-icon-path" fill="none" stroke="<?php echo esc_attr($color); ?>" stroke-width=" 2" stroke-miterlimit="10" d="M15,1.118c12.352,0,13.967,12.88,13.967,12.88v18.76  c0,0-1.514,11.204-13.967,11.204S0.931,32.966,0.931,32.966V14.05C0.931,14.05,2.648,1.118,15,1.118z"></path>
                </svg>
                <span class="track-ball"></span>
            </a>
        </div>
<?php
    }
}
