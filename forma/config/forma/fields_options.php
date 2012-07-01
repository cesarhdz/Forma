<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Forma fields config
 * Guarda la información de los campos posibles, además de los básicos
 *  Formato:
 * 'field_type' => array(
 *  @arg type Input Type Necesario para formar el input
 *  @arg attr_allowed string Atributos Permitidos
 *  @arg rules_default string Reglas por default que cada tipo debe validar
 *  @arg rules_allowed string Reglas de validacion que soporta
 *  @arg attr_default string Atributos por default
 *  @arg template string Template básica para este tipo de campo
 * )
 */

$config['forma_fields_options'] = array(
        'dropdown' => array(
            'type' => 'select',
            'attr_allowed' => 'multiple',
            'rules_default' => '',
            'rules_allowed' => 'is_selected',
        ),
        'radios' => array(
            'type' => 'radio',
            'attr_allowed' => '',
            'rules_default' => '',
            'rules_allowed' => 'data_list|is_natural',
        ),
        'checkboxes' => array(
            'type' => 'checkbox',
            'attr_allowed' => '',
            'rules_default' => '',
            'rules_allowed' => 'data_list|is_natural',
	    'is_multiple' => true,
        ),
        'multiple' => array(
            'type' => 'select',
            'attr_allowed' => '',
            'rules_default' => '',
            'rules_allowed' => 'data_list',
	    'is_multiple' => true,
        ),
        'data_list' => array( //#TODO CHeck new element and try to make it compatible with a select list but with attributes like description of each item
            'attr_allowed' => array('class' => 'uniform', 'cols' => '24', 'rows' => '8'),
            'type' => 'textarea',
            'rules_default' => 'trim',
            'rules_allowed' => '',
        ),
        //#TODO Falta un autocomplete con un data-list
        //#TODO Pensar si search también sería pertinenete
 );
/* termina config/ceverla/forma_fields.php */