<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$config['fields_properties'] = array(
        'required' => array(
            'type' => 'boolean',
            'attr' => 'required="required"',
            'rule' => 'required',
        ),
        'min' => array(
            'type' => 'numeric',
            'rule' => 'greater_than[%s]',
        ),
        'max' => array(
            'type' => 'numeric',
            'rule' => 'lower_than[%s]',
        ),
        'max_length' => array(
            'type' => 'numeric',
            'rule' => 'max_length[%s]',
        ),
        'min_length' => array(
            'type' => 'numeric',
            'rule' => 'min_length[%s]',
        ),
        'data_list' => array(
            'type' => 'data_list',
         ),
        'black_list' => array(
            'type' => 'data_list',
         ),
         'default' => array(
             'type' => 'variable',
         ),
         'placeholder' => array(
             'type' => 'variable',
             'attr' => 'placeholder="%s"',
         ),
         'pattern' => array(
             'type' => 'string',
             'attr' => 'pattern="%s"',
         ),
         'max_size' => array(
             'type' => 'numeric',
             'rule' => 'max_size[%s]',
         ),
         'min_size' => array(
             'type' => 'numeric',
             'rule' => 'min_size[%s]',
         ),
         'file_types' => array(
             'type' => 'multiple',
             'rules' => 'allowed_types[%s]',
             'options' => '',
         ),
         'image_types' => array(
             'type' => 'multiple',
             'rules' => 'allowed_types[%s]',
             'options' => 'gif, png, jpg',
         ),
         'step' => array(
             'type' => 'numeric',
         ),
         'matches' => array(
             'type' => 'variable',
             'rules' => 'in_list[%s]',
             'options' => "forma['fields']",
         )
);

/* termina config/ceverla/forma_fields.php */