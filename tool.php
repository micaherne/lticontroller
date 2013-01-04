<?php

require_once('../../config.php');
require_once($CFG->libdir.'/pluginlib.php');
require_once($CFG->dirroot.'/local/ltiprovider/ims-blti/blti.php');

// The frankenstyle name of the tool
$tool = required_param('tool', PARAM_TEXT);
$oauth_consumer_key = required_param('oauth_consumer_key', PARAM_TEXT);

$pluginmanager = plugin_manager::instance();
if($plugininfo = $pluginmanager->get_plugin_info($tool)) {
    //print_r($plugininfo);
} else {
    echo "Tool $tool not found";
}

// Lookup secret for consumer
$secret = 'secret';

// Do not set session, do not redirect
$context = new BLTI($secret, false, false);

if(!$context->valid) {
    die("Invalid request");
}

$consumer_key = $context->info['oauth_consumer_key'];
// Examine the context from the LTI request and see if a course for that context exists,
$context_id = $context->info['context_id'];
$courseidnumber = sha1($context_id.$consumer_key);

if(!$course = $DB->get_record('course', array('idnumber' => $courseidnumber))) {
    // Create it if not (ltiprovider takes care of creating and enrolling the user)
    $courseconfig = get_config('moodlecourse');
    $newcourse = clone($courseconfig);
    // TODO: What if these haven't been passed?
    $newcourse->fullname = $context->info['context_title'];
    $newcourse->shortname = $context->info['context_label'];
    // TODO: Make this a setting
    $newcourse->category = 1;
    
    $newcourse->idnumber = $courseidnumber;
    $course = create_course($newcourse);
    
    // TODO: Should also rename the first topic to the same fullname, so it shows nicely in create form
}

// Examine the resource_link_id from the LTI request and see if a module instance has already been created for this resource_link_id in this context
$resource_link_id = $context->info['resource_link_id'];

// If so, pass control onto ltiprovider to do the actual launch
// Otherwise, check the roles from the LTI request to see if the user is allowed to create a new instance and show error message if not
// Create a new instance of the tool and show the user the configuration form
$params = array('add' => $tool, 
        'type' => $tool, 
        'course' => $course->id, 
        'section' => 1, 
        'return' => 0,
        'sr' => 0);
redirect(new moodle_url('/course/modedit.php', $params));

/* TODO: General todos:
 * 
 * 1. Create a theme where all layouts use embedded.php and set as default
 */