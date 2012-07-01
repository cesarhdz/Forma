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

$config['forma_fields_input'] = array(
        'variable' => array(
            'type' => 'text',
            'attr_allowed' => 'placeholder',
	    'attr_default' => '',
            'rules_default' => 'trim',
            'rules_allowed' => 'min_length|max_length|required', 
        ),
        'name' => array(
            'type' => 'text',
            'attr_allowed' => 'placeholder',
            'rules_default' => 'trim|valid_name',
            'rules_allowed' => 'max_length|required',
        ),
        'numeric' => array(
            'type' => 'number',
            'attr_allowed' => '',
            'rules_default' => 'trim|is_numeric',
            'rules_allowed' => 'greater_than|lower_than|required|step',
        ),
        'price' => array(
            'type' => 'text',
            'attr_allowed' => 'placeholder',
            'rules_default' => 'trim|numeric',
            'rules_allowed' => 'min|max',
            'extra' => 'label, prefix, suffix, description',
        ),
        'email' => array(
            'type' => 'email',
            'attr_allowed' => 'placeholder',
            'rules_default' => 'trim|valid_email',
            'rules_allowed' => '',
        ),
        'minitexto' => array(
            'type' => 'textarea',
            'attr_allowed' => 'placeholder',
            'attr_default' => array('rows' =>'2', 'cols' => '90', 'style' => 'resize:initial;'),
            'rules_default' => 'trim',
            'rules_allowed' => 'min_length|max_length|required',
        ),
        'texto' => array(
            'type' => 'textarea',
            'attr_allowed' => 'placeholder',
            'attr_default' => array('cols' => '90', 'rows' => '8'),
            'rules_default' => 'trim',
            'rules_allowed' => 'min_length|max_length|required',
        ),
        'tel' => array(
            'type' => 'tel',
            'attr_allowed' => '',
            'rules_default' => 'trim|tel',
            'rules_allowed' => '',
        ),
        'date' => array(
            'type' => 'date',
            'attr_allowed' => '',
            'rules_default' => 'trim|date',
            'rules_allowed' => '', //#TODO Validar rango de fechas
        ),
        'password' => array(
            'type' => 'date',
            'attr_allowed' => '',
            'rules_default' => 'trim', //#TODO Regex for passwords with symbols, number,lower and upper case
            'rules_allowed' => 'min_length|max_length|matches',
        ),
        'color' => array(
            'type' => 'color',
            'attr_allowed' => '',
            'rules_default' => 'trim', //#TODO Regex for color
            'rules_allowed' => '',
        ),
        'html' => array(
            'type' => 'textarea',
            'attr_defautl' => '',
            'attr_allowed' => array('class' => 'wysiwyg', 'cols' => '90', 'rows' => '8'),
            'rules_default' => 'trim|purify',
            'rules_allowed' => 'tags_allowed|placeholder',
        ),
        'data_list' => array( //#TODO CHeck new element and try to make it compatible with a select list but with attributes like description of each item
            'attr_default' => '',
            'attr_default' => array('class' => 'uniform', 'cols' => '24', 'rows' => '8'),
            'type' => 'textarea',
            'rules_default' => 'trim',
            'rules_allowed' => '',
        ),
        //#TODO Falta un autocomplete con un data-list
        //#TODO Pensar si search también sería pertinenete
 );
/* termina config/ceverla/forma/fields_input.php */