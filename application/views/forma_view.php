<h1>Forma Library Test</h1>
<div id="body">
	<p>The Forma Library is has been loaded!</p>
<pre><?php var_dump($forma) ?></pre>
<?php
/*
 * The form begins...
 */
echo $this->forma->open($forma['attr'], '', $forma['settings']);

// $this->forma->add_data_list('modulo', $modulist);
//   $this->forma->add_data_list('formato', $formalist);
    
//    $this->forma->add_input_values(array('modulo' => $modulo->id, 'formato' => ($formato_id) ? $formato_id : 1)); //#TODO Hacer dinámico el formato, y primero que sea visible el valor

//Agregamos los campo
    foreach($forma['fields'] AS $name => $field)
    {
     	$input = is_array($field['input']) ? $field['input'] : $name;

     	echo $this->forma->field(
                $field['type'],
                $input,
                $field['rules'],
                $field['default_value']
            );
    }

    echo $this->forma->close();

    echo $this->forma->show_errors();





?>
	<p>If you are exploring CodeIgniter for the very first time, you should start by reading the <a href="http://localhost/codeigniter/user_guide/">User Guide</a>.</p>
</div>

<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>

