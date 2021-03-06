<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_classification')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
  
// Register the Helloworld helper file
JLoader::register('AttributesHelper', dirname(__FILE__) . '/helpers/attributes.php');

// import joomla controller library
jimport('joomla.application.component.controller');
 
// Get an instance of the controller 
$controller = JControllerLegacy::getInstance('Attributes');
 
// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();