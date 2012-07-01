<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['forma_sample'] =  array (
        'settings' => array(
//            'lang_prefix' => 'pagina'

        ),

        'attr' => array(
           'id' => 'sample',
        ),
        
        'fields' => array(
            'encabezado' => array(
              'type' => 'input_title',
              'rules' => 'required|max_length[196]',
            ),
            'formato' => array(
              'type' => 'options_select',  
            ),
            'subtitulo' => array(
              'type' => 'input_title'  
            ),
            'descripcion' => array(
              'type' => 'text_micro',  
            ),
            'metatitulo' => array(
              'type' => 'input_title',  
            ),
            'slug' => array(
                'type' => 'input_title',
            )
        ),
);