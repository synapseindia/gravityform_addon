<?php

GFForms::include_addon_framework();

class GFSimpleAddOn extends GFAddOn {

	protected $_version = GF_SIMPLE_ADDON_VERSION;
	protected $_min_gravityforms_version = '2.2.6';
	protected $_slug = 'notificationaddon';
	protected $_path = 'gravityaddon/class-gfsimpleaddon.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Notification Add-On';
	protected $_short_title = 'Notification Add-On';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFSimpleAddOn
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFSimpleAddOn();
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


	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'my_script_js',
				'src'     => $this->get_base_url() . '/js/my_script.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'strings' => array(
					'first'  => esc_html__( 'First Choice', 'notificationaddon' ),
					'second' => esc_html__( 'Second Choice', 'notificationaddon' ),
					'third'  => esc_html__( 'Third Choice', 'notificationaddon' )
				),
				'enqueue' => array(
					array(
						'admin_page' => array( 'form_settings' ),
						'tab'        => 'notificationaddon'
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
				'handle'  => 'my_styles_css',
				'src'     => $this->get_base_url() . '/css/my_styles.css',
				'version' => $this->_version,
				'enqueue' => array(
					array(
						'admin_page' => array( 'form_settings' ),
						'tab'        => 'notificationaddon'
					)
				)
			)
		);

		return array_merge( parent::styles(), $styles );
	}


	// # FRONTEND FUNCTIONS --------------------------------------------------------------------------------------------

	/**
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


	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

	

	/**
	 * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
		return array(
			array(
				'title'  => esc_html__( 'Custom Notification  Settings', 'notificationaddon' ),
				'fields' => array(				
					array(
						'label'   => esc_html__( 'Enable Custom Notification', 'notificationaddon' ),
						'type'    => 'checkbox',
						'name'    => 'custom_notification_enabled',
						'tooltip' => esc_html__( 'Enable Custom Notification setting to send this form notification emails', 'notificationaddon' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Enabled', 'notificationaddon' ),
								'name'  => 'custom_notification_enabled',
							),
						),
					),
					array(
						'label' => esc_html__( 'Notification condition', 'notificationaddon' ),
						'type'  => 'my_custom_field_type',
						'name'  => 'my_custom_field',
						'args'  => array(
							'text'     => array(
								'label'         => esc_html__( 'A textbox sub-field', 'notificationaddon' ),
								'name'          => 'notify_email[]',
								'name_field'    => 'notify_email',
								'input_type'    => 'text',
								'default_value' => '',
								'class'             => 'medium',
							),
							'text1' => array(
								'label'         => esc_html__( 'A textbox sub-fieldnumber', 'notificationaddon' ),
								'name'          => 'limit_n[]',
								'name_field'    => 'limit_n',
								'input_type'    => 'number',
								'default_value' => '',
								'class'         => 'small',
							),
							'text2' => array(
								'label'         => esc_html__( 'A textbox sub-fieldnumber', 'notificationaddon' ),
								'name'          => 'limit_w[]',
								'name_field'    => 'limit_w',
								'input_type'    => 'number',
								'default_value' => '',
								'class'         => 'small',
							),
							'text3' => array(
								'label'         => esc_html__( 'A textbox sub-fieldnumber', 'notificationaddon' ),
								'name'          => 'limit_m[]',
								'name_field'    => 'limit_m',
								'input_type'    => 'number',
								'default_value' => '',
								'class'         => 'small',
							),
						),
					),
					array(
						'label'             => esc_html__( 'Overflow', 'notificationaddon' ),
						'type'              => 'text',
						'name'              => 'overflow_email',
						'tooltip'           => esc_html__( 'Please enter an email which will work when above limit will reach', 'notificationaddon' ),
						'class'             => 'medium',
					),
					array(
						'label'             => esc_html__( 'Admin Email', 'notificationaddon' ),
						'type'              => 'text',
						'name'              => 'admin_email',
						'tooltip'           => esc_html__( 'Please enter an email which will be admin email', 'notificationaddon' ),
						'class'             => 'medium',
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
		// get the text field settings from the main field and then render the text field
		
		$form = GFAddOn::get_current_form();
		
		$minus_html = '<a class="gf_delete_field_choice" title="remove this rule"><i class="gficon-subtract"></i></a>';
		$html_script = '<div class="customnotifi_multidiv">';
				foreach($field['args'] as $key=>$value){
						if($key=='text1'){ $html_script .= '<span> Limit: Daily </span>';}
						if($key=='text2'){ $html_script .= '<span> Weekly </span>';}
						if($key=='text3'){ $html_script .= '<span> Monthly </span>';}
						//$text_field = $field['args'][$key];						
						$name = $value['name'];
						$input_type = $value['input_type'];
						$inp_class = $value['class'];
						$inp_id = $value['name_field'];
						$default_value = $value['default_value'];
						$html_script .= '<input type="'.$input_type.'" name="_gaddon_setting_'.$name.'" value="'.$default_value.'" class="'.$inp_class.' gaddon-setting gaddon-text" id="'.$inp_id.'">';
				}
		$html_script .= '<a class="gf_insert_field_choice" title="add another rule"><i class="gficon-add"></i></a>'.$minus_html.'</div>';
		?>
		<script>var notification_conditionhtml = '<?php echo $html_script;?>';</script>		
		<?php
		if(!empty($form['notificationaddon']['notify_email'])){
			$srry_elment = array_filter($form['notificationaddon']['notify_email']);			
			foreach($srry_elment as $notf => $fvalue){
				if($notf >= 1){
					$minus = $minus_html;
				}else{
					$minus = '';
				}				
				echo '<div class="customnotifi_multidiv">';
				foreach($field['args'] as $key=>$value){
						if($key=='text1'){ echo '<span> Limit: Daily </span>';}
						if($key=='text2'){ echo '<span> Weekly </span>';}
						if($key=='text3'){ echo '<span> Monthly </span>';}
						//$text_field = $field['args'][$key];
						$limi_v = $form['notificationaddon'][$value['name_field']][$notf];
						$name = $value['name'];
						$input_type = $value['input_type'];
						$inp_class = $value['class'];
						$inp_id = $value['name_field'];
						echo '<input type="'.$input_type.'" name="_gaddon_setting_'.$name.'" value="'.$limi_v.'" class="'.$inp_class.' gaddon-setting gaddon-text" id="'.$inp_id.'">';
				}
				echo '<a class="gf_insert_field_choice" title="add another rule"><i class="gficon-add"></i></a>'.$minus.'</div>';
				}
		}else{
			echo '<div class="customnotifi_multidiv">';
			foreach($field['args'] as $key=>$value){
				//if($key=='text' || $key=='text1' ){
					if($key=='text1'){ echo '<span> Limit: Daily </span>';}
					if($key=='text2'){ echo '<span> Weekly </span>';}
					if($key=='text3'){ echo '<span> Monthly </span>';}
					$text_field = $field['args'][$key];
					$this->settings_text( $text_field );
				//}			
			}
			echo '<a class="gf_insert_field_choice" title="add another rule"><i class="gficon-add"></i></a></div>';
		}
	}


	// # SIMPLE CONDITION EXAMPLE --------------------------------------------------------------------------------------

	/**
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
					'label' => esc_html__( 'Enabled', 'notificationaddon' ),
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


	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * The feedback callback for the 'mytextbox' setting on the plugin settings page and the 'mytext' setting on the form settings page.
	 *
	 * @param string $value The setting value.
	 *
	 * @return bool
	 */
	public function is_valid_setting( $value ) {
		return strlen( $value ) < 10;
	}
	public function is_valid_email_setting( $value ) {
		//return strlen( $value ) < 10;
		if ( is_email( $value ) ) {
			  return true;
		}else{
			return false;
		}
	}

}