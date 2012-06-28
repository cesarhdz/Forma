<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Forma deault config options
 */

$config['forma_defaults'] = array(
    //Validation rules than can be translated into attributes, so we won´t need javascript validation, only a polyfill
    'rules2attr' => array(
	'required' => array('required', 'required'),
	'lower_than' => array('max', '%s'),
	'greater_than' => array('min', '%s'),
    ),
    
    //Main Settings
    'default_settings' => array(
	'error_level' => 2, //0 means no error, 1 global error, 2 per field error
	'translate' => true,
	'lang_prefix' => '', //Main prefix if translate is true
	'data_list_delimiter' => ',',
	'attr_allowed' => 'class|style|title|autofocus',
	
	'allowed_cols' => array(
	    'etiquetas' => array('etiqueta'),
	    'formatos' => array('formato'),
	),
	
	//Holds token name and values, for cached files than can be served with current csrf values
	'csrf_parse' => false,
	'csrf_token_name' => 'csrf_token_name',
	'csrf_token_value' => 'csrf_token_value',
    ),
    
    //Default templates for fields
    'templates' => array(
	'form' => array(
	    'tag' => '',
	),
	'fieldset' => array(
	    'tag' => '',
	    'extra' => array(
		'before_fields' => '',
		'after_fields' => '' 
	    ),
	),
	'field'	    => array(
	    'tag' => 'dl', //Without class
	    'input_tag' => 'dd',
	    'label_tag' => 'dt',
	    'error_tag' => 'dt.error',
	    'options_tag' => 'ul.options',
	    'extra' => array(
		'before_errors' => NULL,
		'before_label' => NULL,
		'after_label'   => NULL,
		'before_input' => NULL,
		'after_input' => NULL,
		'before_close' => NULL,
	    ),
	),
	'option' => array(
	    'tag' => 'li',
	),
	'submit' => array(
	  'tag' => 'fieldset.submit',
	),
    ),//Templates
 );

//Here comes the main field types, thera are more in extra fields, but these are the basic
$config['forma_defaults']['fields'] = array(
	
    //The simplest input, only a checkbox, but ready to be extended if we have new html elements
    'boolean' => array(
	'default' => array(
	    'type' => 'checkbox',
            'rules_allowed' => 'isset',
	),
    ),
	
    //Accept submit or button types
    'submit' => array(
	'default' => array(
	    'type' => 'submit',
	  ),
	
	 //Creates a button instead input type submit   
	 'button' => array(
	     'type' => 'button',
	 ),
    ),
    
    //Accept input text in all flavors: tel, email, number, range, etc and textareas
    'input' => array(
	'default' => array(
	    'type' => 'text',
	    'attr_allowed' => 'placeholder',
	    'rules_default' => 'trim',
	    'rules_allowed' => 'required|min_length|max_length|valid_name|alfanum|numeric'
	),
	
	//Valid passwrod is a custom function from MY_VALIDATION, it can be replaced
	'password' => array(
	    'type' => 'password',
	    'rules_default' => 'trim|valid_password',
	    'rules_allowed' => 'required|min_length|max_length|matches'
	),
	
	//Allows to set a valid name for user, or items that can be translated into slug, uses custom functions
	'name' => array(
	    'type' => 'text',
            'attr_allowed' => 'placeholder',
            'rules_default' => 'trim|valid_name',
            'rules_allowed' => 'max_length|required',
	),
	
	//Allows to set a valid tile
	'title' => array(
	    'type' => 'text',
            'attr_allowed' => 'placeholder',
            'rules_default' => 'trim',
            'rules_allowed' => 'max_length|required|valid_title',
	),
	
	'numeric' => array(
            'type' => 'number',
            'attr_allowed' => '',
            'rules_default' => 'trim|is_numeric',
            'rules_allowed' => 'greater_than|lower_than|required',
        ),
	
	'email' => array(
            'type' => 'email',
            'attr_allowed' => 'placeholder',
            'rules_default' => 'trim|valid_email',
            'rules_allowed' => '',
        ),
	
	'price' => array(
	    'type' => 'text',
            'attr_allowed' => 'placeholder',
            'rules_default' => 'trim|numeric',
            'rules_allowed' => 'min|max',
	    'extra' => array('prefix' => lang('forma_currency_prefix'), 'suffix' => lang('forma_currency_suffix')),
	),
	
	'date' => array(
	    'type' => 'date',
            'attr_allowed' => '',
            'rules_default' => 'trim',
	    'rules_allowed' => '' //#TODO Add custom functios for date
	),
    ),
    
    'text' => array(
	//Longtext
	'default' => array(
            'type' => 'textarea',
            'attr_allowed' => 'placeholder',
            'attr_default' => array('cols' => '90', 'rows' => '8'),
            'rules_default' => 'trim',
            'rules_allowed' => 'max_length|required',
        ),
	
	//Similar to a tweet
	'micro' => array(
            'type' => 'textarea',
            'attr_allowed' => 'placeholder',
            'attr_default' => array('style' => 'resize:initial;'),
            'rules_default' => 'trim',
            'rules_allowed' => 'max_length|required',
        ),
	
	//Works with and WYSIWYG editor, only adds the class, it also can be a div with new html5 attribute contenteditable
	'html' => array(
            'type' => 'textarea',
            'attr_defautl' => '',
            'attr_allowed' => array('class' => 'wmd', 'cols' => '90', 'rows' => '8'),
            'rules_default' => 'trim|purify',
            'rules_allowed' => 'tags_allowed|placeholder',
        ),
    ),
    
    //Accept select, checkboxes or radios
    'options'   => array(
	'default' => array(
	    'type' => 'select',
	    'attr_allowed' => 'multiple',
	    'rules_allowed' => 'required'
	),
    
        'radio' => array(
            'type' => 'radio',
            'attr_allowed' => '',
            'rules_default' => '',
            'rules_allowed' => 'data_list|required',
        ),
    
        'checkbox' => array(
            'type' => 'checkbox',
            'attr_allowed' => '',
            'rules_default' => '',
            'rules_allowed' => 'data_list|required',
	    'is_multiple' => true,
        ),
    
        'multiple' => array(
            'type' => 'select',
            'attr_allowed' => '',
            'rules_default' => '',
            'rules_allowed' => 'data_list|required',
	    'is_multiple' => true,
        ),
    ),
    
    //Doesn't need type because we only have one type    
    'file'	    => array(
	'default' => array(
	    'type' => 'upload',
	    'attr_allowed' => 'accept|multiple',
	),
	'image' => array(
	    'attr_allowed' => 'accept|multiple',
	),
    ),
    
    //Item, in case we need to add only a reference, not a input field
    'locked'	=> array(
	'default' => array(
	    'type' => 'p',
	),
    )
);
/* termina config/ceverla/forma/defaults.php */