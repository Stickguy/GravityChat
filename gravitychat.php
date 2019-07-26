<?php
/*
Plugin Name: Gravity Forms GravityChat Add-On
Plugin URI: http://www.gravityforms.com
Description: An Add On to create Conversational Forms
Version: 2.1
Author: Frank Ford
Author URI: https://github.com/Stickguy/

------------------------------------------------------------------------
Copyright 2019 Frank Ford

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define( 'GF_CHAT_ADDON_VERSION', '2.1' );

add_action( 'gform_loaded', array( 'GF_Chat_AddOn_Bootstrap', 'load' ), 5 );

class GF_Chat_AddOn_Bootstrap {

		public static function load() {

				if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
						return;
				}

				require_once( 'class-gravitychat.php' );

				GFAddOn::register( 'GFChat' );
		}

}

function gravitychat_addon() {
		return GFChat::get_instance();
}

/*
 * Register GravityChat Frontend Javascript and CSS
 *
 */
function gravitychat_shortcode_wp_enqueue_scripts() {
	wp_register_style( 'gravitychat-styles', plugin_dir_url( __FILE__ ). '/css/conversational-form.min.css' );
	wp_register_style( 'gchat-styles', plugin_dir_url( __FILE__ ). '/css/gchatstyle.css' );
	wp_register_script( 'gravitychat-scripts', plugin_dir_url( __FILE__ ). '/js/conversational-form.min.js' );
}
add_action( 'wp_enqueue_scripts', 'gravitychat_shortcode_wp_enqueue_scripts' );

/*
 * Return Last Value from Form Submission if it is a URL
 *
 */
function findRedirectValue($array){
    $theURL = '';
	$arr = array_filter($array);
    end($arr);
    $lastNonZeroKey = key($arr);
	if (filter_var($array[$lastNonZeroKey], FILTER_VALIDATE_URL)) {
        $theURL = $array[$lastNonZeroKey];
}
	return $theURL;
}

/*
 * Handle GravityChat Form Submission
 *
 */
function chatbot_form_submit() {
    $formData = $_POST['subdat'] ;
	$formID = $_POST['formid'] ;
	$input_values = [];
	foreach ($formData as $key => $value) {
		$keyname = "input_" . substr($key,3); //rename form fields to match the names gravity forms expects
		if(is_array($value)){ // Gravity froms returns several form fields as arrays
			$multival = '';
			foreach($value as $answer){
				if($answer != ''){
					$multival .= $answer . ', ';
				}
			}
		$input_values[$keyname] = substr($multival, 0, -2);	// remove comma and space from end of string
		} else {
		$input_values[$keyname] = $value;
		}

	}
	$result = GFAPI::submit_form( $formID, $input_values ); //submit form
	logToFile("ff_key.log", "Function Fired" . print_r($redirectURL, TRUE)  );
	//echo 'That Worked Yo';

	$redirectURL = findRedirectValue($input_values);
	if($redirectURL != ''){
		echo $redirectURL;
	}
	wp_die();
}

add_action( 'wp_ajax_chatbot_submit', 'chatbot_form_submit' );
add_action( 'wp_ajax_nopriv_chatbot_submit', 'chatbot_form_submit' );

/*
 * Create GravityChat Shortcode
 * Shortcode: [gravitychat id="1"]
 */
function create_gravitychat_shortcode($atts) {

	$atts = shortcode_atts(
		array(
			'id' => '',
		),
		$atts,
		'gravitychat'
	);

	// enqueue Javascript & CSS only when the shortcode runs
	wp_enqueue_style( 'gravitychat-styles' );
	wp_enqueue_style( 'gchat-styles' );
	wp_enqueue_script( 'gravitychat-scripts' );

	if($atts['id']){
		$id = $atts['id']; // form id from shortcode
	}
	$gform = GFAPI::get_form( $id ); // get form object from gravity forms API

    $form = '<form method="POST" id="chatbot-form"><br>'; // will instantiate using jquery to allow callbacks

	foreach ($gform['fields'] as $field) {

		$fieldName = 'cf-' . $field['id']; // Use the field ID to name the fields so it remains consistant with Gravity Forms
		$cfConditional = '';
		if($field['conditionalLogic'] != '') {
			/* This function is only designed to take 'is' as the operator for the conditional logic.
			 * Use of other Operators will lead to unexpected results */
			if($field['conditionalLogic']['actionType'] == 'show' && $field['conditionalLogic']['logicType'] == 'all') {
			foreach ($field['conditionalLogic']['rules'] as $clogic) {
				$cfConditional .= ' cf-conditional-cf-' . $clogic['fieldId'] . '="'. $clogic['value'] .'"';
		       }
			}
		}
    switch ($field['type']) {
    case "text":
		$form .= '<fieldset>';
        $form .= '<label for="' . $fieldName . '">' . $field['label'] . '</label><br>';
		$form .= '<input name="' . $fieldName . '" cf-questions="' . $field['label'] . '" type="' . $field['type'] . '"' . $cfConditional . '><br>';
		$form .= '</fieldset>';
        break;
    case "html":
		$form .= '<cf-robot-message cf-questions="' . htmlspecialchars($field['content']) . '"' . $cfConditional . ' />';
        break;
	case "radio":
		$form .= '<fieldset>';
		$form .= '<label for="' . $fieldName . '">' . $field['label'] . '</label><br>';
			foreach ($field['choices'] as $choice) {
				$form .= '<div class="radio"><label>';
				$form .= '<input name="' . $fieldName . '" cf-questions="' . $field['label'] . '" type="' . $field['type'] . '"' . $cfConditional . ' value="' . $choice['value'] . '"> ' . $choice['text'];
				$form .= '</label></div>';
			}
		$form .= '</fieldset>';
        break;
    case "email":
		$form .= '<fieldset>';
        $form .= '<label for="' . $fieldName . '">' . $field['label'] . '</label><br>';
		$form .= '<input name="' . $fieldName . '" cf-questions="' . $field['label'] . '" type="' . $field['type'] . '"' . $cfConditional . '><br>';
		$form .= '</fieldset>';
        break;
    default:
          }
	}
	$form .= '<button type="submit" class="btn btn-default">Submit</button>'; // append submit button to form
	$form .= '</form>';
    ob_start();

		echo $form;
?>
		<script>
		window.onload = function(){
			(function($) {

    /* instantiate form using jquery and setup callbacks */
    var conversationalForm = window.cf.ConversationalForm.startTheConversation({
    // HTMLFormElement
    formEl: document.getElementById("chatbot-form"),
	context: document.getElementById("cf-context"),
	hideUserInputOnNoneTextInput: true,
    userInterfaceOptions: {
      controlElementsInAnimationDelay: 250,
      robot: {
        robotResponseTime: 500,
        chainedResponseTime: 400
      },
      user:{
        showThinking: true,
        showThumb: true
      }
    },
    submitCallback: function(){

		var formData = conversationalForm.getFormData(true);
        var data = {
		     'action': 'chatbot_submit',
		     'subdat': formData,
			 'formid': <?php echo $id; ?>
		                         };
        var ajaxurl = '<?php echo get_home_url(); ?>/wp-admin/admin-ajax.php'; // define ajaxurl for front end usage

		jQuery.post(ajaxurl, data, function(response) {
		//	alert('Got this from the server: ' + response);
		//	If the response is a URL. Redirect to that URL.
		if(response){
			window.location.href = response;
			//alert('Redirect Here: ' + response);
		}
		});

		// remove Conversational Form
		// window.ConversationalForm.remove();
					}
     });
     })(jQuery);
}
		</script>
<?php
return ob_get_clean();
}
add_shortcode( 'gravitychat', 'create_gravitychat_shortcode' );
