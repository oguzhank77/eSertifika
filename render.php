<?php

/**
 * Render engine
 *
 * @author Kurt Otto <kurt@ottopia.com.au>
 * @version 2.0
 *
 * Renders a template from default, ini and posted keys and values.
 * Placeholder keys in template must be wrapped in double square brackets like so [[]].
 * For example, [[user_full_name]].
 *
 * Reserved keys:
 *   date_format[suffix]
 *       Will automatically generate a date key with option suffix containing the date generated
 *       using the date_format provided. For Example, 'date_format' => 'j M, Y' will generate a key
 *       'date' => '7 January, 2013' where as 'date_format_day' => 'jS' will generate 'date_day' => '7th'.
 *   date[suffix]
 *       Automatically generated as above.
 *   template
 *       Defines which template to use.
 *
 * Unless specifically set by posted variables, date variables are generated using the server timezone.
 */

require_once('classes/class.Template.php');

// Setup values for default template
$data = array(
		'template' => 'default',
		'name'     => 'John Smith',
		'date'     => date('d/m/Y')
);

// Load posted certificate template
if(isset($_GET['template'])) {
	$data['template'] = $_GET['template'];
}

// Set template paths
$template_ini  = 'templates/' . $data['template'] . '/' . $data['template'] . '.ini';
$template_file = 'templates/' . $data['template'] . '/' . $data['template'] . '.tpl';

// Load template.ini and set any template related variables
// This will override any default settings
if(file_exists($template_ini)) {
	$ini_array = parse_ini_file($template_ini, true);
	if(isset($ini_array['template'])) {
		// Iterate through keys and generate coressponding date keys for date_format keys
		// e.g. date_format - date, date_format_1 - date_1, date_format_day - date_day
		foreach($ini_array['template'] as $key => $value) {
			if (!strncmp($key, 'date_format', strlen('date_format'))) {
				$data['date' . substr($key, strlen('date_format'))] = date($value);
			}
		}
		// Merge and override the data array with the ini array
		$data = array_merge($data, $ini_array['template']);
	}
}

// Merge and override the data with posted variables
// This will override any default and ini settings
$data = array_merge($data, $_GET);
// Iterate through keys and generate coressponding date keys for date_format keys
// e.g. date_format - date, date_format_1 - date_1, date_format_day - date_day
foreach($_GET as $key => $value) {
	if (!strncmp($key, 'date_format', strlen('date_format'))) {
		$data['date'.substr($key, strlen('date_format'))] = date($value);
	}
}

// Load template and render page
if(file_exists($template_file)) {
	$layout = new Template($template_file);
	foreach ($data as $key => $value) {
		$layout->set($key, $value);
	}
	echo $layout->output();
} else {
	echo 'Template could not be found.';
}

