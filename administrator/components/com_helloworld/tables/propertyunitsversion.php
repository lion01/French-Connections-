<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
 
// import Joomla nested table library
jimport('joomla.database.table');

// import the model helper lib
//jimport('joomla.application.component.model');

/**
 * Hello Table class
 */
class HelloWorldTablePropertyUnitsVersion extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(&$db) 
	{
		parent::__construct('#__property_units_versions', 'id', $db);
	}	
  
  /*
   * Overridden store method to capture the created by and modified dates etc
   * 
   * 
   */
  public function store($updateNulls = false) {
    
    $date = JFactory::getDate();
    $user = JFactory::getUser();

    if ($this->id) {
      // Existing item
      $this->modified = $date->toSql();
      $this->modified_by = $user->get('id');
    } else {
      // New newsfeed. A feed created and created_by field can be set by the user,
      // so we don't touch either of these if they are set.

      if (empty($this->created_by)) {
        $this->created_by = $user->get('id');
      }

      if (empty($this->created_on)) {
        $this->created_on = $date->toSql();
      }

    }

    return parent::store($updateNulls);
  }

  
  /*
   * Method to get the version id of the most recent unpublished version
   * 
   * 
   */
  
  public function getLatestUnitVersion($id = null)
  {
    print_r($this->id);
    die;
    // Get the database connection 
    $db = JFactory::getDbo();
    
  }  
  
}
