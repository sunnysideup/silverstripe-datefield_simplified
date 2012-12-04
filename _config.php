<?php


/**
*@author Nicolaas [at] sunnysideup.co.nz
*
**/


Director::addRules(50, array(
	SimpleDateField_Controller::get_url() . '//$Action/$Value' => 'SimpleDateField_Controller',
));
//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START datefield MODULE ----------------===================

//===================---------------- END datefield MODULE ----------------===================

