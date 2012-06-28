<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Forma Class
 *  This class makes creating forms easier, its features are
 *  Builds fields insteads of simple inputs, with some degreee of custimization in the output.
 *  Allows different types of fields including, submits, locked (with display purpose only), and any other html string
 *  With this Class you can store you forms in database or config files and create dinamically
 *  Fill the values of inputs automatically
 *  Remember the field sent (set_value, set_select, set_checkbox)
 *  You can use lang() function in order to translate labels and legends autmatically
 *  Also support validation features
 *  #TODO Add functions for uploading files automatically
 *  #TODO Add functions for saving in database
 *  #TODO Automatically detect POST, in order to load the field only once $this->forma->validate OR $this->forma->display_stored_form
 * 
 * @author César Hernádez
 * @package CodeIgniter 2.0+
 * @version 1.2
 * @date 2th december 2011
 */

class Forma {

    //Store elements for proccessing
    protected $_form, $_fieldset, $_field, $_option, $_submit, $_rules;
    
    //Hold values and options for fields
    private $_input_values = array(), $_data_list = array();
    
    //Hold default settings, see config/forma/defaults.php
    protected $_fields, $_rules2attr, $_allowed_items, $_default_settings, $_templates;
    
    //Store the main configurable settings
    private $settings = array();
    
    //Theme, stores the templates and more
    private $_CI;
    
    //To fix the form validation bug
    private $_fields_in_post = array();

    /*
     * Constructor
     * @param array Settings
     */
    public function __construct($settings = array())
    {
	$this->_CI = & get_instance();

	//Load CI form helper
	$this->_CI->load->helper(array('form', 'language', 'url'));

	//Load Lang
	$this->_CI->lang->load('forma');

	//Load form config options, form default's file and add to the object
	$this->_CI->config->load("forma/defaults");
	foreach ($this->_CI->config->item("forma_defaults") as $key => $val)
	{
	    $key = '_' . $key;
	    $this->$key = $val;
	}


	log_message('debug', 'Forma Class Initialized');
    }

    //---------  CONFIG FUNCTIONS  ----------------------|
    /*
     * Load Field Types
     * Adds the config for building field types
     * 
     * @param array Types to load or where ot load
     * @return void
     */
    public function load_field_types($group, $types)
    {
	if (array_key_exists($group, $this->_fields))
	{
	    foreach ($types as $name => $options)
	    {
		//Avoid overwritig and ensure it usable
		if (!array_key_exists($name, $this->_fields[$group]) && isset($options['type']))
		    $this->_fields[$group][$name] = $options;
	    }
	}
    }

    /*
     * Setting
     * @access public
     * @param array configurable settings
     */
    public function add_settings($settings)
    {
	if (is_array($settings)) $this->settings = array_merge($this->settings, $settings);
    }
    
    public function setting($key)
    {
	return (array_key_exists($key, $this->settings)) ? $this->settings[$key] : $this->_default_settings[$key];
    }

    /*
     * RESET
     */
    public function reset()
    {
	$this->_field = array();
	$this->_fieldset = array();
	$this->_form = array();
	$this->_data_list = array();
	$this->errors = array();
	$this->settings = array();
    }

    /*
     * Set CSRF
     * 
     * If isset, the csrf is set as a variable than can be parsed by the loader in case we use cache files and csrf is active
     * @access public
     * @param string Name
     * @param string Value
     * @deprecate ?
     */
    public function set_csrf_vars($name, $value)
    {
	$this->settings['csrf_parse'] = true;
	$this->settings['csrf_token_name'] = $name;
	$this->settings['csrf_token_value'] = $value;
    }

    //---------  LOADING DATA FUNCTIONS  ----------------------|
    /*
     * Add data_list
     * Via this function we can set a list for future use, for filling select, checkboxes or datalists
     * We only need to prep list: to the name and set in the options variable
     * @access public
     */
    public function add_data_list($name, $list)
    {
	if (is_string($list))
	    $list = $this->_explode($list);

	//#TODO SUpport translation in preloaded datalits
	$this->_data_list[$name] = & $list;
    }

    /*
     * Add values
     */
    public function add_input_values($values)
    {
	//If is in object shape we transform to array
	if (is_object($values))
	    $values = (array) $values;

	//We set the values
	if (is_array($values) && count($values))
	    $this->_input_values = array_merge($this->_input_values, $values);
    }

    //---------  CREATE ITEM FUNCTIONS  ----------------------|

    /*
     * Open
     * @access public
     * @param  Form Array
     *  @arg ID	    String Required
     *  @arg Action String Optional
     *  @arg Class  String Optional
     *  @arg Attr   Mixed Extra attributes
     * @param Extra Mixed Optional
     *  @arg Title  String
     *  @arg Directions String
     * @param Show Errors bool Optional
     */
    public function open($form, $hidden = '', $settings = '')
    {
	//The form could be a string which means we use it as action, but we can set more attibutes
	if (is_string($form) AND $form != '')
	    $form = array('id' => $form);

	//Initialize the form
	$this->initialize($settings, $form['id']);

	//Almost the same way form_open function figures action attribute out, so if action is empty we set to the current uri
	$action = base_url();
	$action .= ($form['action']) ?  $form['action'] : $this->_CI->uri->uri_string();

	//Method and accept-charset are set
	$attr = array(
	    'method' => ($form['method'] == 'get') ? 'get' : 'post',
	    'accept-charset' => 'utf-8',
	    'id' => $this->_form['id'],
	);
	$errors = ($this->setting('error_level') == 1) ? validation_errors('<div class="errors">', '</div>') : '';
	
	//Return opening tag, hidden and errors(if needed)
	return '<form action="' .  $action . '" ' .   _parse_form_attributes($form, $attr) . '>'
		. $this->_get_hidden($hidden)
		. $this->_open_tag($this->_templates['form']['tag'])
		. $errors;
    }

    /*
     * Form open Multipart
     * 
     * Only adds enctype attribute and return form open method
     * @param mixed form
     * @param array
     * @param mixed
     */
    public function open_multipart($form='', $hidden ='')
    {
	//The form could be a string which means we use it as action
	if (is_string($form))
	{
	    $form = array('id' => $form);
	}

	$form['enctype'] = 'multipart/form-data';
	return $this->open($form, $hidden);
    }

    /*
     * From Close
     * Only parse template if needed
     */
    public function close($template = '')
    {
	//Since we are finished we reset to default settings
	$this->reset();

	//Finally the output
	return '</form>';
    }

    /*
     * Fieldset Function
     * Create a complet set of fields or only fieldset open based on fields array
     * @access public
     * @param array Fieldset, Holds the legend and extra attributes like messagge, help, heading, legend
     * @param array Attr elements
     */
    public function fieldset($fieldset = '', $attr = '')
    {
	$legend = (is_array($fieldset) AND count($fieldset)) ? $fieldset['legend'] : $fieldset;

	//Gather extra elements
	$extra = (is_array($fieldset)) ? $this->_add_extra($fieldset, 'fieldset') : array();

	return '<fieldset' . _parse_attributes($attr) . '>'
	. $extra['before_legend']
	. $this->_wrap($legend, 'legend', TRUE)
	. $extra['after_legend']
	. $this->_open_tag($this->_templates['fieldset']['tag']);
    }

    /*
     * Field Function
     * @return An html formated field, with input, label, and extra info. It doen't not work for submit buttons fieldset
     * @access Public
     * @since 1
     * @param Array Field
     *  @arg Type  String Required
     *  @arg Name  String Required
     *  @arg Label String Optional
     *  @arg Hint  String Optional
     * @param Array Input Optional, if String Is the value
     * 	@arg Attr  String|Array Atributes for the input
     *  @arg Rules String, Rules of validation
     *  @arg PrefixString, Text before input
     *  @arg SuffixString, Text after input
     * @param Options Mixed
     */
    public function field($field, $input, $rules = '', $default_value = NULL)
    {
	//We will work with arrays
	if (is_object($field)) $field = (array) $field;
	if (is_object($input)) $input = (array) $input;
	
	//Get name and type
	$name = (is_string($input)) ? $input : $input['name'];
	$type = (is_string($field)) ? $field : $field['type'];
	//Initialize the field or return
	if (! $this->_initialize_field($type, $name)) return;
	//If we have an array with more than one variable, we have extra elements, soo we get them
//	$extra = (is_array($field) AND count($field) > 1) ? $this->_add_extra($extra) : array();

	//Then we look for input attributes
	$input_attr = $this->_get_input_attr($input, $rules);

	//Prepare Options for fields that need then //#TODO CHeck input['options'] is string
	$options = '';
	if ($this->_field['builder'] == 'options')
	{
	    //Look for options, inside $input, we only accept list in CSV string or the wildcard col:
	    if (is_array($input))
		$options = (is_string($input['options'])) ? $input['options'] : '';

	    //Load options or return false
	    $options = & $this->_options_2_array($options);

	    //No options? we have nothing to display
	    if (!$options)
		return $this->_set_error(1, sprintf(lang('forma_error_no_options'), $this->_field['name']));
	}

	//We guess the label
	$f_id = (is_array($field)) ? $field['id'] : '';
	$f_class = (is_array($field)) ? $field['class'] : '';
        
	$label = (is_array($field)) ? $field['label'] : '';
	$label = $this->_guess_label($label);    
        
        if(is_array($field) AND count($field) > 1)
        {
            $extra = $this->_add_extra($field, 'field');
        }

	//Since we hace everything we need, we return the field, we don´t store so we gain performance?
	//#TODO Add before and after label
	//#TODO Make error position dinamic
	return $this->_open_field($f_id, $f_class)
	    . $this->_wrap(form_label($label, $this->_field['id']), $this->_templates['field']['label_tag'])
	    . $this->_field_error()
	    . $this->_open_tag($this->_templates['field']['input_tag'])
	    . $extra['before_input']
	    . $this->_input($default_value, $input_attr, $options)
	    . $extra['after_input']
	    . $this->_close_tag($this->_templates['field']['input_tag'])
	    . $extra['before_close']
	    . $this->_close_field();
    }

    /*
     * Submit
     * Return submit buttons
     * @param Mixed Array/string buttons
     * 	@arg Each button
     * @param Mixed Array/string extra attributes
     */
    public function submit($buttons, $extra = '')
    {
	$this->_initialize_submit();
	
	$extra = array(); //#TODO Add extra elements for submit buttons

	return $this->_open_tag($this->_templates['submit']['tag'])
	. $extra['before']
	. $this->_submit_buttons($buttons)
	. $extra['after']
	. $this->_close_tag($this->_templates['submit']['tag']);
    }

    /*
     * Fieldset Close
     */
    public function fieldset_close($extra = '')
    {
	//#TODO Add extra elements for fieldset close
	return $this->_close_tag($this->_templates['fieldset']['tag']).'</fieldset>';
    }

    /*
     * Hidden input function
     * Allows to intoduce Attributes other than name and value to the field, like id
     */
    public function hidden($name, $value='')
    {
	$defaults = array('type' => 'hidden');

	//If is array name, we are dealing with many inputs
	if (is_array($name) AND count($name) > 0)
	{
	    $hidden = '';

	    //Let's do it recursively
	    foreach ($name as $k => $v)
	    {
		$hidden .= $this->hidden($k, $v);
	    }
	}
	//Name is string, so we only need to parse the attributes
	else
	{
	    $attr = _parse_form_attributes(array(
		'value' => $value,
		'name' => $name,
//		'id' => $this->_form['id'] . '_' . $name
		    ), $defaults);

	    $hidden = "<input {$attr} />";
	}

	return $hidden;
    }

    //---------  VALIDATION FUNCTIONS  ----------------------|
    /*
     * Validate
     */
    public function validate($rules=NULL, $fix_bug = true)
    {
	if(! count($_POST)) return FALSE;
	$rules = (is_array($rules))? $rules : $this->_rules;
	
	//Only if we have rules we load the object and validate
	if (count($rules) AND is_array($rules))
	{
	    //Load validation class
	    $this->_CI->load->library('form_validation');

	    //Set the rules in validation Object
	    $this->_CI->form_validation->set_rules($this->_rules);

	    //We have everything we need to run validation
	    return $this->_CI->form_validation->run();
	    
	    //@deprecated Because I think set_values function have a bug: if we don't set the rules we have no answer 
	    //i try to fix it, if fix_bug is set to true
//	    $this->_fix_set_values_bug();
//	    return $is_valid;
	}

	//No rules, no valididation we return true
	return TRUE;
    }

    /*
     * Add Field Rules (for Validation)
     */
    public function add_field_rules($type, $name, $rules = '', $label = '')
    {
	//Initialize the field
	$type = (is_array($type)) ? $type['type'] : $type;
	$name = (is_array($name)) ? $name['name'] : $name;
	$this->_initialize_field($type, $name);
	
	//Try to fix set value //#TODO Transform into initialize field in order to autogenerat the form
//	if (isset($_POST[$field['fields']['name']]))
//	    $this->_fields_in_post[] = $field['fields']['name'];

	//Submits don't require validation #TODO Avoid fields with norules enter the loo
	if ($this->_field['builder'] != 'submit')
	{
	    $label = $this->_guess_label($label);
	    $this->_set_rules($rules, $label);
	}
    }

    /*
     * Set Rules
     */
    private function _set_rules($rules, $label)
    {
	//Get field rules, filtered by field type
	$rules = $this->_filter_rules($rules);

	//Return If we have no rules but before we try to fix a bug in set_values function from form validation
	if ($rules == FALSE)
	{
	    if ($this->_field['name'] AND $post = & $this->_CI->input->post($this->_field['name']))
		$this->_fields_in_post[$field['name']] = & $post;

	    return;
	}

	$rules = implode('|', $rules);

	//If we have rules we add to the rules array
	if ($rules != '')
	{
	    $this->_rules[] = array(
		'field' => ($this->_field['type']['is_multiple']) ? $this->_field['name'] . '[]' : $this->_field['name'],
		'rules' => $rules,
		'label' => $this->_guess_label($label)
	    );
	}
    }

    /*
     * Get field rules
     * Filter rules
     */
    private function _filter_rules($rules)
    {
	$rules = $this->_get_rules($rules);
	$out = array();

	if (count($rules))
	{
	    foreach ($rules AS $rule => $param)
		$out[] = ($param) ? "{$rule}[{$param}]" : $rule;
	}
	return $out;
    }

    //---------  INITIALIZE FUNCTIONS  ----------------------|
    /*
     * Open Field
     * Create the content that goes before the input
     * @param string Label If we need a diferent label than the name we can provide it
     */
    private function _open_field($id, $class)
    {
	//Get Classes from field and validation errors
	$id = (is_string($id) AND $id) ? ' id="'.$id.'"' : '';
	$class = (is_string($class) AND $class) ? " {$class}" : '';
	$class = ' class="'."{$this->_field['builder']} {$this->_field['class']}$class";

	//Then if we have name and POST, we look for error
	if (count($_POST) && $this->setting('error_level'))
	{
	    if ($this->_CI->form_validation->_field_data[$this->_field['name']]['error'] != '')
		$class .= ' error';
	}

	//Open the field
	return '<' . $this->_templates['field']['tag'] . $id . $class . '">';
    }

    /*
     * Close Field
     */
    private function _close_field($hint='')
    {
	//Unset field, we no longer use it
	$this->_field = array();
	return $this->_close_tag($this->_templates['field']['tag']);
    }

    /*
     * Initialize field
     * @access private
     * @arg array Attributes of the field
     * @param string name required
     * @param string type required
     */
    private function _initialize_field($type, $name)
    {
	//Start with an empty field
	$this->_field = array();
	//If type is not a string or was not provided
	if (!is_string($type) OR !$type)
	    return $this->_set_error(1, lang('forma_error_no_type'));


	//Input name or return error
	if (!is_string($name) OR !$name)
	    return $this->_set_error(1, lang('forma_error_no_name'));

	//Look for method and type
	$builder = explode('_', $type);

	//Look for the builder if we have no builder we trigger an error
	if (!array_key_exists($builder[0], $this->_fields))
	    return $this->_set_error(1, sprintf(lang('forma_error_invalid_type'), $type));

	//Start filling the new field
	$this->_field['builder'] = $builder[0];
	$this->_field['name'] = $name;

	//We no longer use path, only set the id
	$this->_field['id'] = ($this->_form['id']) ? $this->_form['id'] . '_' . $name : $name;

	//We won't need config because it is only a locked field
	if ($this->_field['builder'] == 'locked')
	{
	    $this->_field['tag'] = $builder[1];
	    return true;
	}

	//Extra if we are creating a field
	$this->_field['class'] = (array_key_exists($builder[1], $this->_fields[$builder[0]])) ? $builder[1] : 'default';

	//We add configurations of input
	$this->_field['input'] = & $this->_fields[$builder[0]][$this->_field['class']];
	
	return true;
    }

    /*
     * Initizalize form
     */
    function initialize($settings, $id='')
    {
	//Start with an empty object
	$this->reset();
	
	$id = (is_array($id)) ? $id['id'] : $id;
	$this->_form = (is_string($id) AND $id != '') ? array('id' => $id) : array();

	//Setting
	if (is_array($settings) AND count($settings))
	    $this->add_settings($settings);
    }

    /*
     * Initialize Fieldset
     */

    private function _initialize_fieldset($name)
    {
	//Check we have a name
	if (!is_string($name) OR $name == '')
	    return $this->_set_error(1, lang('forma_error_no_fieldset_id'));

	$this->_fieldset = array(
	    'name' => $name,
	    'path' => $this->_form['id'] . '_',
	);
    }

    /*
     * Initialize Option
     */
    private function _initialize_option($value)
    {
	$this->_option = array(
	    'path' => $this->_field['path'] . $this->_field['name'] . '_',
	    'name' => ($this->_field['input']['is_multiple']) ? $this->_field['name'] . '[]' : $this->_field['name'],  
	    ////Do we need to add value? : $this->_field['name']['val],
	);
    }

    /*
     * Initialize submit buttons
     */
    private function _initialize_submit()
    {
	$this->_submit = array(
	    'path' => $this->_form['id'] . '_submit_',
	);
    }

    //---------  BUILT INPUT FUNCTIONS  ----------------------|
    /*
     * Built Input
     * @access private
     * @since 1.1
     * @return void
     * We no longer use the old way of parsing, we instead built the field, in order to make it faster
     */
    private function _input(&$value, $attr, $options)
    {
	//Look for values, it may be stored previously
	$value = ($value) ? $value : $this->_input_values[$this->_field['name']];

	//For locked items we only display the value
	if ($this->_field['builder'] == 'locked')
	    return $this->_wrap($value, $this->_field['tag']); 

	//And the we built the input
	$method = '_build_' . $this->_field['builder'];
	return $this->$method($value, $attr, $options);
    }

    private function _submit_buttons($buttons)
    {
	//#TODO Support Extra Attributes For buttons

	$set = '';

	if (is_array($buttons))
	{
	    foreach ($buttons AS $key => $val)
	    {
		//If we have an uri, we are dealing with a link
		if (substr($val, 0, 4) == 'uri:')
		{
		    $val = substr($val, 4);
		    $text = ($this->setting('translate')) ? $this->_lang($key) : $val;
		    $set .= anchor($val, $text);
		}
		else
		{
		    $val = ($this->setting('translate')) ? $this->_lang($key) : $val;

		    $set .= form_submit('submit', $val);
		}
	    }
	}

	return $set;
    }

    /*
     * Build boolean
     */
    private function _build_boolean($value, $attr)
    {
	$checked = $this->_set_selected($value, 1, 'checkbox');
	return form_checkbox($this->_field['name'], 1, $checked, _parse_form_attributes($attr, $this->_input_attr_defaults()));
    }

    /*
     * Built File
     */
    private function _build_file($value, $attr)
    {
	return form_upload($this->_field['name'], '', _parse_form_attributes($attr, array('id' => $this->_field['id'])));
    }

    /*
     * Built Submit
     * It accept buttons and submit types
     */
    private function _build_submit($value, $attr)
    {
	//It also supports translation or autofill if no value was given
	$value = ($value) ? $value : $this->_field['name'];

	//We support translation for Submit Button Values
	if ($this->setting('translate'))
	    $value = $this->_lang($value, $this->_field['id']);

	//We support buttons and submit but although button are display they already have no functionality #TODO Make Buttons Work
	$function = 'form_' . $this->_fields[$this->_field['builder']][$this->_field['class']]['type'];
	return $function('submit', $value, _parse_form_attributes($attr, $this->_input_attr_defaults()));
    }

    /*
     * Private Function Built Input
     */
    private function _build_input($value, $attr)
    {
	//We only add attibutes and return the input
	$value = $this->_set_value($value, $this->_field['name']);
	return '<input ' . _parse_form_attributes($attr, $this->_input_attr_defaults()) . 'value="' . $value . '" />';
    }

    /*
     * Built Text
     */
    private function _build_text($value, $attr)
    {
	//We only add attibutes and return the input
	$extra = _attributes_to_string($this->_fields[$this->_field['builder']][$this->_field['class']]['attr_default']);
	return '<textarea ' . _parse_form_attributes($attr, $this->_input_attr_defaults()) . $extra . '>'
	. $this->_set_value($value, $this->_field['name'])
	. '</textarea>';
    }

    /*
     * Build options fields
     */
    private function _build_options($selected, $attr, $options)
    {
	if ($this->_field['input']['type'] == 'select')
	{
	    $options = & $this->_get_select_options($options, $selected);

	    //Set first option #TODO Make dinamic via settings the visibility of first select value
	    $first = (!$selected) ?  '<option>' . lang('forma_select0') . '</option>' : '';

	    $multiple = ($this->_field['input']['is_multiple']) ? 'multiple="multiple" ' : '';
	    return '<select ' . _parse_form_attributes($attr, $this->_input_attr_defaults()) . $multiple . " />" . $first . $options . "</select>";
	}
	else
	{
	    //#TODO Add custom field for extra info in checkboxes
	    return $this->_get_multioptions($options, $selected);
	}
    }
    
    private function _get_hidden($hidden)
    {
	//Set use hidden
	$inputs = (is_array($hidden)) ? $hidden : array();
	
	// Look for CSRF
	if ($this->_CI->config->item('csrf_protection') === TRUE)
	{
	    //This is the original way CI handles CSRF
	    if (!$this->setting('csrf_parse'))
		$inputs[$this->_CI->security->get_csrf_token_name()] = $this->_CI->security->get_csrf_hash();

	    //But if we cache files, we only need to provide the template tags to be parsed by a custom Loader Controller
	    else
		$inputs[$this->setting('csrf_token_name')] = $this->setting('csrf_token_value');
	}
	
	return $this->hidden($inputs);
    }

    //---------  INPUT ATTRIBUTES AND VALUES FUNCTIONS  ----------------------|
    private function _get_input_attr($input, $rules)
    {
	//We first look for attributes
	$attr = (is_array($input)) ? $this->_filter_attributes($input, $this->_field['input']['attr_allowed']) : array();

	//Then we add rules default anr user rules, and then try to convert into attributes, finally we return
	return array_merge($attr, $this->_rules_2_attr($this->_field['input']['rules_default'] . '|' . $rules));
    }

    /*
     * Private function _filter_attributes
     * @param Attr array we asume we have attributes
     * @param Allowed Atributes Mixed
     * return Atributes in array shape
     */
    private function _filter_attributes($attr, $allowed)
    {
	//Transform string into array
	if (is_string($allowed) AND $allowed != '')
	    $allowed = explode('|', $allowed);

	//No attributes allowed, no point in following
	if (!is_array($allowed) OR !count($allowed))
	    return array();

	$filtered = array();

	//#TODO Support for lang:placeholder
	foreach ($attr as $key => $val)
	{
	    //Multiple = true or multiple = multiple gives the same result: multiple = multiple
	    if (in_array($key, $allowed))
		$out[$key] = (is_bool($val) AND ($val)) ? $key : $val;
	}

	return $filtered;
    }

    /*
     * Input Attr Defaults
     */
    private function _input_attr_defaults()
    {
	return array(
	    'name' => $this->_field['name'],
	    'id' => $this->_field['id'],
	);
    }

    private function _rules_2_attr($rules)
    {
	//We convert rules into array
	//#TODO Change name of cuntion get_rules into _rules_2_array
	$rules = $this->_get_rules($rules);

	//We must return an array
	$attr = array();
	
	if (count($rules))
	{
	    //Convert rules to attributes
	    foreach ($rules as $rule => $param)
	    {
		//Check if is allowed, an is in rules to attr
		if (array_key_exists($rule, $this->_rules2attr))
		{
		    $key = $this->_rules2attr[$rule][0];
		    $attr[$key] = sprintf($this->_rules2attr[$rule][1], $param);
		}
	    }
	}
	
	return $attr;
    }

    /*
     * Private Function Set Value
     * For each input decides what value, only fot inputs and textareas
     * 
     * #NOTE If we have run validation and we have not set rules for th field
     * the set_value funtion will not work, unless we run the method _fix_set_values_bug
     */
    private function _set_value(&$value, $name)
    {
	//Look for value in fields_values if value key is not set
	return (!isset($_POST[$name])) ? $value : set_value($name, $value);
    }

    /*
     * Set Selected
     * For each input decides what value, only fot inputs and textareas
     * 
     * return boolean
     * #NOTE If we have run validation and we have not set rules for th field
     * the set_value funtion will not work, unless we run the method _fix_set_values_bug
     */
    private function _set_selected($default, $value)
    {
	//We'll ever use the filed name as name regarless the item is an option, because we have take into account by adding the value
	$function = 'set_' . $this->_field['input']['type'];
	$selected = ($default) ? TRUE : FALSE;
	return $function($this->_field['name'], $value, $selected);
    }

    /*
     * Get Options
     * @param Options String
     * @return Array of Options OR FALSE
     */
    private function _options_2_array($options)
    {
	//Is no options we may have pre-saved in data_list, we return without translation
	if (!$options)
	    return (array_key_exists($this->_field['name'], $this->_data_list)) ? $this->_data_list[$this->_field['name']] : FALSE;

	//#TODO Unique function for this format list:items, lang:line, parent:template
	//If options are in database, we look for them and return them without any translation
	if (substr($options, 0, 4) === 'col:')
	    return $this->_get_db_col(substr($options, 4));

	//We can also set the options as a CSV or Tab separated list
	$options = $this->_explode($options);

	//If we have no options there is no reason to follow next steps
	if (count($options) == 0 OR !is_array($options))
	    return FALSE;

	//Try to translate each option
	if ($this->setting('translate'))
	{
	    foreach ($options as $value => $label)
		$options[$value] = $this->_lang($value, $this->_field['id'], $label);
	}

	//Return the options	
	return $options;
    }

    /*
     * Crete Options for select fields
     * Need to rewrite the select function from helper because otherwise it'll loop twice if we want to set selected values
     */
    private function _get_select_options($options, $selected)
    {
	$ops = '';

	//We use an array in order to support multiple select if needed
	if (!is_array($selected)) $selected = array($selected);

	//Borrowed from form_dropdown function of CI form helper, because we need to set values automatically
	foreach ($options as $key => $val)
	{
	    $key = (string) $key;

	    if (is_array($val) && !empty($val))
	    {
		$ops .= '<optgroup label="' . $key . '">' . "\n";

		foreach ($val as $optgroup_key => $optgroup_val)
		{
		    //Lines added to give set_select support
		    $default = (in_array($optgroup_key, $selected)) ? TRUE : FALSE;
		    $sel = $this->_set_selected($default, $key);

		    $ops .= '<option value="' . $optgroup_key . '"' . $sel . '>' . (string) $optgroup_val . "</option>\n";
		}

		$ops .= '</optgroup>' . "\n";
	    }
	    else
	    {
		//Lines added to give set_select support
		$default = (in_array($key, $selected)) ? TRUE : FALSE;
		$sel = $this->_set_selected($default, $key);

		$ops .= '<option value="' . $key . '"' . $sel . '>' . (string) $val . "</option>\n";
	    }
	}

	return $ops;
    }

    /*
     * Multi options, for inputs like checkbox, radio and more
     * they need a label, and they have been translated if needed
     */
    private function _get_multioptions($options, $selected)
    {
	$ops = '';

	if (!is_array($selected))
	    $selected = array();

	foreach ($options as $val => $label)
	{
	    //Initialize the option
	    $this->_initialize_option($val);

	    $default = (in_array($val, $selected)) ? TRUE : FALSE;

	    $input = array(
		'name' => $this->_option['name'],
		'id' => $this->_option['path'] . $val,
		'value' => $val,
		'checked' => $this->_set_selected($default, $val),
	    );

	    //We are ready to execute the function
	    $function = 'form_' . $this->_field['input']['type'];
	    
	    $ops.= $function($input) . form_label($label, $input['id']);
	}

	return $ops;
    }

    //---------  LABELS AND EXTRA ITEMS FUNCTIONS  ----------------------|
    /*
     * Function to add extra info
     */
    private function _add_extra(&$extra, $level = 'field')
    {
	$out = array();

        //Check if we have an array, so then we continue
	if (is_array($extra) AND is_array($this->_templates[$level]['extra']))
	{
            foreach($extra AS $key => $val)
            {
                if(array_key_exists($key, $this->_templates[$level]['extra']))
                {
                    $out[$key] = $this->_wrap($val, $this->_templates[$level]['extra'][$key]);
                }
            }
//            
//	    //format of $level is parent:template
////	    $level = explode(':', $level);
//preprint($this->_templates[$level]);
//	    //If template, we loop to return extra wrapped
//	    if (is_array($this->_templates[$level]['extra']))
//	    {
//            preprint($extra, 'extra');
//		foreach ($this->_templates[$level]['extra'][$level[1]] AS $key => $tag)
//		{
//		    //So we have extra info to display
//		    if (array_key_exists($key, $extra))
//		    {
//			return $this->_wrap($extra[$key], $tag); //#TODO Make available to add more extra
//		    }
//		}
//	    }
	}
        
        return $out;
    }

    private function _guess_label($str)
    {
        if($str === FALSE) return;
        
	$label = ($str != '') ? $str : $this->_field['name'];

	return ($this->setting('translate') AND $str == '')
		? $this->_lang($label)
		: ucfirst($label);
    }

    private function _field_error()
    {
	$tag = $this->_templates['field']['error_tag'];

	if (count($_POST) AND $this->setting('error_level') == 2)
	    return form_error($this->_field['name'], $this->_open_tag($tag), $this->_close_tag($tag));

	return '';
    }

    //---------  HELPERS  ----------------------|
    /*
     * Wrap
     * Wraps a text in tag
     * @param Text string
     * @param Tag string format: tag.class
     */
    private function _wrap(&$text, $tag = 'div', $translate = FALSE)
    {
	//Si no hay texto, no seguimos
	if(! strlen($text)) return;
	
	if($translate) $text = $this->_lang($text);

	//If no text, we return empty string
	if (!is_string($text) OR !$text OR $tag == '')
	    return $text;

	return "{$this->_open_tag($tag)}{$text}{$this->_close_tag($tag)}";
    }

    /*
     * Lang
     * Use this function if we want to make use of hierarchy and take advantage of context traslations
     * @param string line to be translated
     * @param string prefix (opt)
     * @param string suffix (opt)
     */
    private function _lang($line, $prefix = '', $default = NULL)
    {
	//If we have a prefix, we add underscore, this is for auto translating
	$prefix .= ( $prefix) ? '_' : '';

	//We also support the lang:line notation, so if the text strats with lang: we will ignore prefix
	$base = (substr($line, 0, 5) === 'lang:') ? substr($line, 5) : $line;
	$lang_prefix = ($this->setting('lang_prefix')) ? $this->setting('lang_prefix').'_' : '';
        
        $line = ($base == $line) ? $lang_prefix.$prefix.$base : $line;

	//Try to translate
	if (FALSE === ($string = lang(strtolower($line))))
	{
	    $this->_set_error(3, sprintf(lang('forma_error_no_lang'), $line));

	    //We return the original string
	    return (is_string($default)) ? ucfirst($default) : ucfirst($base);
	}

	//We have a string to return
	return ucfirst($string);
    }

    /*
     * Explode Rules
     * @access private
     * @return array 
     * @arg 1 rule
     * @arg 2 param
     */
    private function _explode_rules($rules)
    {
	$out = array();

	if ($rules)
	{
	    $rules = explode('|', $rules);

	    foreach ($rules as $rule)
	    {
		$param = '';

		if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
		{
		    $rule = $match[1];
		    $param = $match[2];
		}

		$out[$rule] = $param;
	    }
	}

	return $out;
    }

    /*
     * Open Tag
     */
    private function _open_tag($tag)
    {
	return $this->_get_tag($tag);
    }

    /*
     * Get Tag
     * We only add class, beacuse is extra information
     */
    private function _get_tag($tag, $type = 'open')
    {
	//If no tag we return empty string
	if (!is_string($tag) OR !$tag)
	    return '';

	// Look for tag and class
	$tag = explode('.', $tag);

	//If is closing tag, we already have it
	if ($type == 'close')
	    return "</{$tag[0]}>";

	//Else we need the class, and then return it
	$class = (isset($tag[1])) ? ' class="' . $tag[1] . '"' : '';
	return "<{$tag[0]}{$class}>";
    }

    /*
     * Close Tag
     */
    private function _close_tag($tag)
    {
	return $this->_get_tag($tag, 'close');
    }

    /*
     * Explode
     * Convert a data list in array to be inserted in a multioption input
     * @access private
     * @return Array
     */
    private function _explode($string)
    {
	if (!$string)
	    return array();

	return explode($this->setting('data_list_delimiter'), $string);
    }

    /*
     * Get Rules
     * #TODO CHang name into filter rules
     */
    private function _get_rules($user_rules)
    {
	//Convert rules into array
	$user_rules = $this->_explode_rules($user_rules);
	$rules = array();
	$allowed = $this->_explode_rules($this->_field['input']['rules_allowed']);

	//Filter the filed rules defined by user, we accept callback but rules2attr will filter
	foreach ($user_rules as $rule => $param)
	{
	    if (array_key_exists($rule, $allowed) OR substr($rule, 0, 9) == 'callback_')
		$rules[$rule] = $param;
	}

	//We get default rules
	return ($this->_field['input']['rules_default'] != '') ? array_merge($this->_explode_rules($this->_field['input']['rules_default']), $rules) : $rules;
    }

    /*
     * Fix set values bug
     */
    function _fix_set_values_bug()
    {
	foreach ($this->_fields_in_post as $name)
	    $this->_CI->form_validation->_field_data[$name] = array('field' => $name, 'postdata' => $this->_CI->input->post($name));
    }

    /*
     * Get db col
     * Table must have an id column, get the list
     */
    private function _get_db_col($col)
    {
	$col = explode('.', $col);

	//Get allowed cols
	$allowed = $this->setting('allowed_cols');
	if(!is_array($allowed)) return false;
	
	//We only search in allowed cols, first we check the table is registered
	if (!array_key_exists($col[0], $allowed))
	    return FALSE;

	//...then if the col is allowed
	if (in_array($col[1], $allowed[$col[0]]))
	{
	    //Prepare the query, 1=>table, 2=>col, 3=>where
	    $sql = "SELECT `{$col[1]}`, id FROM {$col[0]}";
	    $sql .= ( is_string($col[2]) AND $col[2]) ? " WHERE $col[2] ;" : ';';

	    $q = $this->_CI->db->query($sql);

	    //Format results
	    $list = array();
	    if ($q->num_rows() > 0)
	    {
		foreach ($q->result_array() as $row)
		    $list[$row['id']] = ucfirst($row[$col[1]]);
	    }

	    return $list;
	}
    }

    //---------  DEBUGG  ----------------------|
    /*
     * Show errors Function
     */
    public function show_errors($glue = '<hr />', $level = 3)
    {
	$errors = '';

	if (count($this->errors) AND is_array($this->errors))
	{
	    foreach ($this->errors AS $error)
	    {
		//Filter errors
		if ($error['level'] > $level)
		    continue;

		//Switch to display the level in words
		switch ($error['level'])
		{
		    case 1:
			$errors .= 'Error: ';
			break;
		    case 2:
			$errors .= 'Warning: ';
			break;
		    case 3:
			$errors .= 'Notice: ';
			break;
		}

		$errors .= $error['msg'] . ' ' . $glue . "\n";
	    }
	}
	else
	{
	    $errors = lang('forma_no_errors');
	}

	return $errors;
    }

    /*
     * Error function
     * 
     * @param INT Level config log threshold scale
     * @param string Message to be showed
     */
    private function _set_error($level, $msg)
    {
	//Only if we are in development we have acces to errors
	if (ENVIRONMENT == 'development')
	{
	    $this->errors[] = array('level' => $level, 'msg' => $msg);
	    log_message($level, $msg);
	}

	return FALSE;
    }

}

/* End of file Forma.php */
/* Location: ./application/libraries/ */