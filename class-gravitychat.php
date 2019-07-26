<?php

GFForms::include_addon_framework();

class GFChat extends GFAddOn {

	protected $_version = GF_CHAT_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gravitychat';
	protected $_path = 'gravitychat/gravitychat.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Chat Add-On';
	protected $_short_title = 'GravityChat Add-On';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFChat
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFChat();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();
		add_filter( 'gform_submit_button', array( $this, 'form_submit_button' ), 10, 2 );
		add_action( 'gform_after_submission', array( $this, 'after_submission' ), 10, 2 );
	}


	/**
	 * SCRIPTS & STYLES
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'gravitychat_js',
				'src'     => $this->get_base_url() . '/js/gravitychat.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'strings' => array(
					'first'  => esc_html__( 'First Choice', 'gravitychat' ),
					'second' => esc_html__( 'Second Choice', 'gravitychat' ),
					'third'  => esc_html__( 'Third Choice', 'gravitychat' )
				),
				'enqueue' => array(
					array(
						'admin_page' => array( 'form_settings' ),
						'tab'        => 'gravitychat'
					)
				)
			),

		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gravitychat_css',
				'src'     => $this->get_base_url() . '/css/gravitychat.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'poll' ) )
				)
			)
		);

		return array_merge( parent::styles(), $styles );
	}

	/**
	 * FRONTEND FUNCTIONS
	 * Add the text in the plugin settings to the bottom of the form if enabled for this form.
	 *
	 * @param string $button The string containing the input tag to be filtered.
	 * @param array $form The form currently being displayed.
	 *
	 * @return string
	 */
	function form_submit_button( $button, $form ) {
		$settings = $this->get_form_settings( $form );
		if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
			$text   = $this->get_plugin_setting( 'mytextbox' );
			$button = "<div>{$text}</div>" . $button;
		}

		return $button;
	}

	/**
	 * ADMIN FUNCTIONS
	 * Creates a custom page for this add-on.
	 */
	public function plugin_page() {
		echo 'This page appears in the Forms menu';
	}

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'GravityChat Add-On Settings', 'gravitychat' ),
				'fields' => array(
					array(
						'name'              => 'mytextbox',
						'tooltip'           => esc_html__( 'This is the tooltip', 'gravitychat' ),
						'label'             => esc_html__( 'This is the label', 'gravitychat' ),
						'type'              => 'text',
						'class'             => 'small',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					)
				)
			)
		);
	}

	/**
	 * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
		return array(
			array(
				'title'  => esc_html__( 'GravityChat Form Settings', 'gravitychat' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'My checkbox', 'gravitychat' ),
						'type'    => 'checkbox',
						'name'    => 'enabled',
						'tooltip' => esc_html__( 'This is the tooltip', 'gravitychat' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Enabled', 'gravitychat' ),
								'name'  => 'enabled',
							),
						),
					),
					array(
						'label'   => esc_html__( 'My checkboxes', 'gravitychat' ),
						'type'    => 'checkbox',
						'name'    => 'checkboxgroup',
						'tooltip' => esc_html__( 'This is the tooltip', 'gravitychat' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'First Choice', 'gravitychat' ),
								'name'  => 'first',
							),
							array(
								'label' => esc_html__( 'Second Choice', 'gravitychat' ),
								'name'  => 'second',
							),
							array(
								'label' => esc_html__( 'Third Choice', 'gravitychat' ),
								'name'  => 'third',
							),
						),
					),
					array(
						'label'   => esc_html__( 'My Radio Buttons', 'gravitychat' ),
						'type'    => 'radio',
						'name'    => 'myradiogroup',
						'tooltip' => esc_html__( 'This is the tooltip', 'gravitychat' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'First Choice', 'gravitychat' ),
							),
							array(
								'label' => esc_html__( 'Second Choice', 'gravitychat' ),
							),
							array(
								'label' => esc_html__( 'Third Choice', 'gravitychat' ),
							),
						),
					),
					array(
						'label'      => esc_html__( 'My Horizontal Radio Buttons', 'gravitychat' ),
						'type'       => 'radio',
						'horizontal' => true,
						'name'       => 'myradiogrouph',
						'tooltip'    => esc_html__( 'This is the tooltip', 'gravitychat' ),
						'choices'    => array(
							array(
								'label' => esc_html__( 'First Choice', 'gravitychat' ),
							),
							array(
								'label' => esc_html__( 'Second Choice', 'gravitychat' ),
							),
							array(
								'label' => esc_html__( 'Third Choice', 'gravitychat' ),
							),
						),
					),
					array(
						'label'   => esc_html__( 'My Dropdown', 'gravitychat' ),
						'type'    => 'select',
						'name'    => 'mydropdown',
						'tooltip' => esc_html__( 'This is the tooltip', 'gravitychat' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'First Choice', 'gravitychat' ),
								'value' => 'first',
							),
							array(
								'label' => esc_html__( 'Second Choice', 'gravitychat' ),
								'value' => 'second',
							),
							array(
								'label' => esc_html__( 'Third Choice', 'gravitychat' ),
								'value' => 'third',
							),
						),
					),
					array(
						'label'             => esc_html__( 'My Text Box', 'gravitychat' ),
						'type'              => 'text',
						'name'              => 'mytext',
						'tooltip'           => esc_html__( 'This is the tooltip', 'gravitychat' ),
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'label'   => esc_html__( 'My Text Area', 'gravitychat' ),
						'type'    => 'textarea',
						'name'    => 'mytextarea',
						'tooltip' => esc_html__( 'This is the tooltip', 'gravitychat' ),
						'class'   => 'medium merge-tag-support mt-position-right',
					),
					array(
						'label' => esc_html__( 'My Hidden Field', 'gravitychat' ),
						'type'  => 'hidden',
						'name'  => 'myhidden',
					),
					array(
						'label' => esc_html__( 'My Custom Field', 'gravitychat' ),
						'type'  => 'my_custom_field_type',
						'name'  => 'my_custom_field',
						'args'  => array(
							'text'     => array(
								'label'         => esc_html__( 'A textbox sub-field', 'gravitychat' ),
								'name'          => 'subtext',
								'default_value' => 'change me',
							),
							'checkbox' => array(
								'label'   => esc_html__( 'A checkbox sub-field', 'gravitychat' ),
								'name'    => 'my_custom_field_check',
								'choices' => array(
									array(
										'label'         => esc_html__( 'Activate', 'gravitychat' ),
										'name'          => 'subcheck',
										'default_value' => true,
									),
								),
							),
						),
					),
					array(
						'label' => esc_html__( 'Simple condition', 'gravitychat' ),
						'type'  => 'custom_logic_type',
						'name'  => 'custom_logic',
					),
					array(
						'label' => esc_html__( 'Field Select', 'gravitychat' ),
						'type'  => 'field_select',
						'name'  => 'my_field',
					),
				),
			),
		);
	}

	/**
	 * Define the markup for the my_custom_field_type type field.
	 *
	 * @param array $field The field properties.
	 * @param bool|true $echo Should the setting markup be echoed.
	 */
	public function settings_my_custom_field_type( $field, $echo = true ) {
		echo '<div>' . esc_html__( 'My custom field contains a few settings:', 'gravitychat' ) . '</div>';

		// get the text field settings from the main field and then render the text field
		$text_field = $field['args']['text'];
		$this->settings_text( $text_field );

		// get the checkbox field settings from the main field and then render the checkbox field
		$checkbox_field = $field['args']['checkbox'];
		$this->settings_checkbox( $checkbox_field );
	}

	/**
	 * SIMPLE CONDITION EXAMPLE
	 * Define the markup for the custom_logic_type type field.
	 *
	 * @param array $field The field properties.
	 * @param bool|true $echo Should the setting markup be echoed.
	 */
	public function settings_custom_logic_type( $field, $echo = true ) {

		// Get the setting name.
		$name = $field['name'];

		// Define the properties for the checkbox to be used to enable/disable access to the simple condition settings.
		$checkbox_field = array(
			'name'    => $name,
			'type'    => 'checkbox',
			'choices' => array(
				array(
					'label' => esc_html__( 'Enabled', 'gravitychat' ),
					'name'  => $name . '_enabled',
				),
			),
			'onclick' => "if(this.checked){jQuery('#{$name}_condition_container').show();} else{jQuery('#{$name}_condition_container').hide();}",
		);

		// Determine if the checkbox is checked, if not the simple condition settings should be hidden.
		$is_enabled      = $this->get_setting( $name . '_enabled' ) == '1';
		$container_style = ! $is_enabled ? "style='display:none;'" : '';

		// Put together the field markup.
		$str = sprintf( "%s<div id='%s_condition_container' %s>%s</div>",
			$this->settings_checkbox( $checkbox_field, false ),
			$name,
			$container_style,
			$this->simple_condition( $name )
		);

		echo $str;
	}

	/**
	 * Build an array of choices containing fields which are compatible with conditional logic.
	 *
	 * @return array
	 */
	public function get_conditional_logic_fields() {
		$form   = $this->get_current_form();
		$fields = array();
		foreach ( $form['fields'] as $field ) {
			if ( $field->is_conditional_logic_supported() ) {
				$inputs = $field->get_entry_inputs();

				if ( $inputs ) {
					$choices = array();

					foreach ( $inputs as $input ) {
						if ( rgar( $input, 'isHidden' ) ) {
							continue;
						}
						$choices[] = array(
							'value' => $input['id'],
							'label' => GFCommon::get_label( $field, $input['id'], true )
						);
					}

					if ( ! empty( $choices ) ) {
						$fields[] = array( 'choices' => $choices, 'label' => GFCommon::get_label( $field ) );
					}

				} else {
					$fields[] = array( 'value' => $field->id, 'label' => GFCommon::get_label( $field ) );
				}

			}
		}

		return $fields;
	}

	/**
	 * Evaluate the conditional logic.
	 *
	 * @param array $form The form currently being processed.
	 * @param array $entry The entry currently being processed.
	 *
	 * @return bool
	 */
	public function is_custom_logic_met( $form, $entry ) {
		if ( $this->is_gravityforms_supported( '2.0.7.4' ) ) {
			// Use the helper added in Gravity Forms 2.0.7.4.

			return $this->is_simple_condition_met( 'custom_logic', $form, $entry );
		}

		// Older version of Gravity Forms, use our own method of validating the simple condition.
		$settings = $this->get_form_settings( $form );

		$name       = 'custom_logic';
		$is_enabled = rgar( $settings, $name . '_enabled' );

		if ( ! $is_enabled ) {
			// The setting is not enabled so we handle it as if the rules are met.

			return true;
		}

		// Build the logic array to be used by Gravity Forms when evaluating the rules.
		$logic = array(
			'logicType' => 'all',
			'rules'     => array(
				array(
					'fieldId'  => rgar( $settings, $name . '_field_id' ),
					'operator' => rgar( $settings, $name . '_operator' ),
					'value'    => rgar( $settings, $name . '_value' ),
				),
			)
		);

		return GFCommon::evaluate_conditional_logic( $logic, $form, $entry );
	}

	/**
	 * Performing a custom action at the end of the form submission process.
	 *
	 * @param array $entry The entry currently being processed.
	 * @param array $form The form currently being processed.
	 */
	public function after_submission( $entry, $form ) {

		// Evaluate the rules configured for the custom_logic setting.
		$result = $this->is_custom_logic_met( $form, $entry );

		if ( $result ) {
			// Do something awesome because the rules were met.
		}
	}

	/**
	 * HELPERS
	 * The feedback callback for the 'mytextbox' setting on the plugin settings page and the 'mytext' setting on the form settings page.
	 *
	 * @param string $value The setting value.
	 *
	 * @return bool
	 */
	public function is_valid_setting( $value ) {
		return strlen( $value ) < 10;
	}

}
