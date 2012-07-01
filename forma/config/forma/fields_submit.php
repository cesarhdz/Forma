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

$config['forma_fields_file'] = array(
        'button' => array(
            'type' => 'button',
            'attr_allowed' => array('class' => 'image'),
            'rules_default' => 'trim|is_image',
            'rules_allowed' => 'max_size|min_size|image_types',
        ),
 );
/* termina config/ceverla/forma_fields.php */