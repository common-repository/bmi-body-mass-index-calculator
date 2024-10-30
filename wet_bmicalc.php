<?php
/*
Plugin Name: Body Mass Index (BMI)
Plugin URI: http://detoxdietabc.com/goodies/wp-bmi-calculator
Description: Allows the user to calculate the Body Mass Index (BMI) from body weight and height.
Author: Robert Wetzlmayr
Version: 1.4.1
Author URI: http://detoxdietabc.com/
License: GPL 2.0, @see http://www.gnu.org/licenses/gpl-2.0.html
*/

class wet_bmicalc {

    function init() {
    	// check for the required WP functions, die silently for pre-2.2 WP.
    	if (!function_exists('wp_register_sidebar_widget'))
    		return;

    	// load all l10n string upon entry
        load_plugin_textdomain('wet_bmicalc');

        // let WP know of this plugin's widget view entry
    	wp_register_sidebar_widget('wet_bmicalc', __('Body Mass Index', 'wet_bmicalc'), array('wet_bmicalc', 'widget'),
            array(
            	'classname' => 'wet_bmicalc',
            	'description' => __('Allows the user to calculate the Body Mass Index (BMI) from body weight and height.', 'wet_bmicalc')
            )
        );

        // let WP know of this widget's controller entry
    	wp_register_widget_control('wet_bmicalc', __('Body Mass Index', 'wet_bmicalc'), array('wet_bmicalc', 'control'),
    	    array('width' => 300)
        );

        // short code allows insertion of wet_bmicalc into regular posts as a [wet_bmicalc] tag.
        // From PHP in themes, call do_shortcode('wet_bmicalc');
        add_shortcode('wet_bmicalc', array('wet_bmicalc', 'shortcode'));
    }

	// back end options dialogue
	function control() {
	    $options = get_option('wet_bmicalc');
		if (!is_array($options))
			$options = array('title'=>__('Calculate Your Body Mass Index', 'wet_bmicalc'), 'buttontext'=>__('Calculate'));
		if ($_POST['wet_bmicalc-submit']) {
			$options['title'] = strip_tags(stripslashes($_POST['wet_bmicalc-title']));
			$options['buttontext'] = strip_tags(stripslashes($_POST['wet_bmicalc-buttontext']));
			update_option('wet_bmicalc', $options);
		}
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$buttontext = htmlspecialchars($options['buttontext'], ENT_QUOTES);

		echo '<p style="text-align:right;"><label for="wet_bmicalc-title">' . __('Title:') .
		' <input style="width: 200px;" id="wet_bmicalc-title" name="wet_bmicalc-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="wet_bmicalc-buttontext">' .  __('Button Text:', 'widgets') .
		' <input style="width: 200px;" id="wet_bmicalc-buttontext" name="wet_bmicalc-buttontext" type="text" value="'.$buttontext.'" /></label></p>';
		echo '<input type="hidden" id="wet_bmicalc-submit" name="wet_bmicalc-submit" value="1" />';
	}

    function view($is_widget, $args=array()) {
    	if($is_widget) extract($args);

    	// get widget options
    	$options = get_option('wet_bmicalc');
    	$title = $options['title'];
    	$buttontext = $options['buttontext'];

    	// l10n strings
    	$lbl_height =  __('Height in cm:', 'wet_bmicalc');
    	$lbl_weight = __('Weight in kg:', 'wet_bmicalc');
    	$answer = __('Your <acronym title="Body Mass Index">BMI</acronym> is', 'wet_bmicalc');
    	$bmi_table = __('Jump to the BMI table&nbsp;&raquo;', 'wet_bmicalc');

    	// all calculation is done by the client, trying to compensate for common errors like mixing meters with centimeters.
    	$point = __('.', 'wet_bmicalc'); // decimal point
    	$bs = '\\';

    	$out[] = <<<EOT
            <script type="text/javascript">
            function wet_bmicalc()
            {
            	var theform = document.getElementById('wet_bmicalc_form');
            	var bmi = document.getElementById('wet_bmicalc_bmi');
            	var pane = document.getElementById('wet_bmicalc_pane');
            	var h = theform.wet_bmicalc_height.value;
            	h = h.replace(/{$bs}{$point}/, ".");
            	if ( h > 100 ) h = h / 100;

            	var w = theform.wet_bmicalc_weight.value;
            	w = w.replace(/{$bs}{$point}/, ".");
            	if ( w * h > 0 ) {
            		bmi.innerHTML = (w / (h * h)).toFixed(1).replace(/\./, "{$point}");
            		pane.style.display = "block";
            	} else {
            		pane.style.display = "none";
            	}
            }
            </script>
EOT;
    	// the widget's form
		$out[] = $before_widget . $before_title . $title . $after_title;
		$out[] = '<div style="margin-top:5px;">';
        $out[] = '<noscript><p>'.__('This Widget requires Javascript', 'wet_bmicalc').'</p></noscript>';
		$out[] = <<<FORM
<form id='wet_bmicalc_form' method='post'>
	<p>
	<label for='wet_bmicalc_height'>{$lbl_height}</label><br />
	<input id='wet_bmicalc_height' type='text' name='wet_bmicalc_height' value="" size='6' />
	</p>
	<p>
	<label for='wet_bmicalc_weight'>{$lbl_weight}</label><br />
	<input id='wet_bmicalc_weight' type='text' name='wet_bmicalc_weight' value='' size='6' />
	</p>
	<div id='wet_bmicalc_pane' style='display:none'>
	<p>{$answer} <strong id='wet_bmicalc_bmi'></strong>.</p>
	</div>
	<p><a href='http://detoxdietabc.com/body-mass-index/'>{$bmi_table}</a></p>
	<p><input type='submit' value='{$buttontext}' onclick='wet_bmicalc(); return false;' /></p>
</form>
FORM;
		$out[] = '</div>';
    	$out[] = $after_widget;
    	return join($out, "\n");
    }

    function shortcode($atts, $content=null) {
        return wet_bmicalc::view(false);
    }

    function widget($atts) {
        echo wet_bmicalc::view(true, $atts);
    }
}

add_action('widgets_init', array('wet_bmicalc', 'init'));

?>