<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Forma add field config
 * Forma para agregar campos
 * //    echo $forma->field(array(
//	'type' => 'multi_option',
//	'name' => 'color',
//	'placeholder' => '1',
//	'template_input' => '<div class="input {type}">{input}</div>',
//	'template_label' => '<div class="label>{label}</div>"',
//	'template_option' => '<div class="option">{option}{label}</div>',
//	'template_option_label' => '<div class="option_label>{label}</div>',
//    ), 'una variable');
 */

$config['forma_add_field'] = array(
    'form' => array(
	'settings' => array(
	    'translate' => true,
	    'lang_prefix' => 'fields_',
	),
	'theme' => 'cvadmin',
	'attr'  => array(
	    'id' => 'fields',
	),
	'submit' => array(
	    'ok' => 'add',
    //		'cancel' => 'cancelar',
    //		'url' => 'admin/paginas'
	),
    ),
    'fields' => array(
	array( 
	    'fieldset' => 'new',
	    'label' => array(
		'type'    => 'input_name',
		'name' => 'label',
		'rules' => 'required|callback_campo_unico',
	    ),
	    'type' => array(
		'type'    => 'options_radio',
		'name' => 'type',
		'rules' => 'is_selected',
//		'options' => 'list:editor'
	    ),
	    'requerido' => array(
		  'type' => 'boolean',
		  'name' => 'es_requerido',
		  'rules' => 'selected',
		  'options' => 'es_requerido',
	    ),
	),
    ),
);
/* termina config/forma/add_field.php */