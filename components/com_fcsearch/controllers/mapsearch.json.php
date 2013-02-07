<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Suggestions JSON controller for Finder.
 *
 * @package     Joomla.Site
 * @subpackage  com_finder
 * @since       2.5
 */
class FcSearchControllerMapSearch extends JControllerLegacy
{
	/**
	 * Method to find search query suggestions.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function markers($cachable = false, $urlparams = false)
	{
		$return = array();
   
    // Get the suggestions.
    $model = $this->getModel('Markers', 'FcSearchModel');
    $state = $model->getState();
    
    $results = $model->getResults();
    
    // Process the results so we don't need to do that in the browser
    foreach ($results as &$result) {
      $result->link = JRoute::_('index.php?option=com_accomodation&id='.$result->id);
      $result->pricestring = JText::_('COM_FCSEARCH_SEARCH_FROM');
      $result->thumbnail = JRoute::_(JPATH_SITE . '/images/property/thumb/' . $result->id . '/' . $result->thumbnail );
    }
    
		
    // Check the data.
		if (empty($results))
		{
			$results = array();
		}

		// Use the correct json mime-type
		header('Content-Type: application/json');

		// Send the response.
		echo json_encode($results);
		JFactory::getApplication()->close();
	}
}