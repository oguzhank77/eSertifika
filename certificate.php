<?php

/**
 * @author Kurt Otto <kurt@ottopia.com.au>
 * @version 2.0
 *
 * Certificate Builder
 *
 */

require_once('config.php');
require_once('classes/Wkhtmltopdf/class.Wkhtmltopdf.php');

// Setup default variables
$wkoptions = array(
		'path'        => $CFG->temp_data,
		'binpath'     => $CFG->wkhtmltopdf_bin
);
$tploptions = array (
		'template' => 'default'
);

// Get posted variable for template
if(isset($_POST['template'])) {
	$tploptions['template'] = $_POST['template'];
}

// Setup template ini path
$template_ini  = 'templates/' . $tploptions['template'] . '/' . $tploptions['template'] . '.ini';

// Load template.ini and set any pdf related settings
// This will override any default settings
if(file_exists($template_ini)) {
	$ini_array = parse_ini_file($template_ini, true);
	if(isset($ini_array['pdf'])) {
		// Merge and override the settings array with the ini array
		$wkoptions = array_merge($wkoptions, $ini_array['pdf']);
	}
}
// Set a default title if it is not provided through the ini file.
if(!isset($wkoptions['title'])) {
	$wkoptions['title'] = "Turkcell LMS Başarı Belgesi";
}

// Merge and override the template options with posted variables
// This will override any default and ini settings
$tploptions = array_merge($tploptions, $_POST);

// Encode options into render URL
$renderFile = $CFG->base_url . '/render.php';
if(isset($tploptions)) {
	$renderFile .= '?' . implode('&', array_map(function($key, $val) {
		return urlencode($key) . '=' . urlencode($val);
	},
	array_keys($tploptions), $tploptions)
	);
}
// Render for download
try {
	$wkhtmltopdf = new Wkhtmltopdf($wkoptions);
	$wkhtmltopdf->setUrl($renderFile);
	$wkhtmltopdf->output(Wkhtmltopdf::MODE_DOWNLOAD, $wkoptions['title'] . '.pdf');
} catch (Exception $e) {
	echo $e->getMessage();
}