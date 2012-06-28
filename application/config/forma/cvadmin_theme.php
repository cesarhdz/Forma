<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Theme for fully featured forms
 */

$config['forma_theme'] = array(
    'name' => 'Ceverla Admin Theme',
    'class' => 'ceverla',
    'css' => '',
    'style' => '',
    'templates' => array(
	'form'		    => '{open}{errors}',
	'field'		    => '<div class="{class}">{label}<div class="input">{prefix}{input}{suffix}</div>{error}{msg}</div>',
	'field_submit'	    => '<div class="submit">{input}</div>',
	'fieldset'	    => '{open}{legend}{help}<div class="fields">{fields}</div>{close}',
	'legend'	    => '<div class="sidebar"><legend>{legend}</legend></div>',
	'field_boolean'     => '<div class="field boolean">{error}<div class="entrada">{input}</div></div>',
	'option_boolean'    => '{input}{label}',
	
//	'fieldset_open'	    => '{fieldset_open}',
//	'fieldset_close'    => '{fieldset_close}',
//	'multiple_open'	    => '<ul class="opciones">', 
//	'multiple_close'    => '</ul>',
	'option'	    => '<div class="option">{input}{label}</div>',
//	'label_option'	    => '<span class="option_label">{label}</span>',
	'label_field'	    => '<div class="label">{label}</div>',
	'error'		    => '<div class="error">{error}</div>',
	'suffix'	    => '<span class="suffix">{suffix}</span>',
	'prefix'	    => '<span class="prefix">{prefix}</span>',
	'msg'		    => '<p class="msg">{msg}</p>',
    ),
);

/* termina config/forma/cvadmin_theme.php */