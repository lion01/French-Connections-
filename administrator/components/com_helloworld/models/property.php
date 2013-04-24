<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * HelloWorld Model
 */
class HelloWorldModelProperty extends JModelAdmin {

  /**
   * Returns a reference to the a Table object, always creating it.
   *
   * @param	type	The table type to instantiate
   * @param	string	A prefix for the table class name. Optional.
   * @param	array	Configuration array for model. Optional.
   * @return	JTable	A database object
   * @since	1.6
   */
  public function getTable($type = 'PropertyListing', $prefix = 'HelloWorldTable', $config = array()) {
    return JTable::getInstance($type, $prefix, $config);
  }

  /**
   * Method to get the record form.
   *
   * @param	array	$data		Data for the form.
   * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
   * @return	mixed	A JForm object on success, false on failure
   * @since	1.6
   */
  public function getForm($data = array(), $loadData = true) {

    // Get the form.
    $form = $this->loadForm('com_helloworld.property', 'property', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) {
      return false;
    }

    return $form;
  }
  
	/**
	 * getItem is overridden so that we can, if necessary, load any updated fields from the version tables.
   * 
   * We check the review state flag and if that is true, we load property details from the #__property_listings_versions table and update the fields
   * 
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
		}

    // Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
    
    // If review flag is true, there is an unpublished version in the versions table.
    if ($properties['review']) {
      
      // Need to load the new version details here to replace those loaded here.

    }
    
		$item = JArrayHelper::toObject($properties, 'JObject');

		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
	}
  
  /**
   * Method to get the script that have to be included on the form
   *
   * @return string	Script files
   */
  public function getScript() {
    return 'administrator/components/com_helloworld/models/forms/helloworld.js';
  }

  /**
   * Method to get a list of units for a given property listing
   *
   * @param   integer  $pk  The id of the property listing.
   *
   * @return  mixed    Object on success, false on failure.
   *
   * @since   12.2
   */
  public function getUnits() {

    $input = JFactory::getApplication()->input;
    $id = $input->get('id', '', 'int');

    $return = false;

    // Get the units table
    $units_table = $this->getTable('Units', 'HelloWorldTable');

    // Set the primary key to be the parent ID column, this allow us to fetch the units for this listing ID.
    $units_table->set('_tbl_key', 'parent_id');

    if ($id > 0) {
      // Attempt to load the row.
      $return = $units_table->load_units($id);

      // Check for a table object error.
      if ($return === false && $units_table->getError()) {
        $this->setError($units_table->getError());
        return false;
      }
    } else {
      return false;
    }

    // Convert to the JObject before adding other data.
    $units = JArrayHelper::toObject($return, 'JObject');

    return $units;
  }

  public function getProgress() {

    $return = false;

    // Get the units table
    $units_table = $this->getTable('PropertyUnits', 'HelloWorldTable');

    if ($id) {
      // Attempt to load the row.
      $return = $units_table->progress($id);

      // Check for a table object error.
      if ($return === false && $units_table->getError()) {
        $this->setError($units_table->getError());
        return false;
      }
    }

    return $return;
  }

  /**
   * Method to get the data that should be injected in the form.
   *
   * @return	mixed	The data for the form.
   * @since	1.6
   */
  protected function loadFormData() {
    // Check the session for previously entered form data.
    $data = JFactory::getApplication()->getUserState('com_helloworld.edit.property.data', array());

    if (empty($data)) {
      $data = $this->getItem();
    }

    return $data;
  }

  /*
   * This method checks whether the property being edited is a unit.
   * If it is then we take the lat and long from the parent property 
   * and force those to be the same for this property.
   * 
   * This can happen from two places.
   * Firstly, if a user is adding a new property they may choose a parent property
   * in which case we take the parent_id from the user session.
   * 
   * Secondly, if the user is editing an existing property which already has a 
   * parent_id set. I.e. is already marked as a unit. In this case it will be set
   * in the $data scope.
   * 
   * param JForm $form The JForm instance for the view being edited
   * param array $data The form data as derived from the view (may be empty)
   * 
   * @return void
   * 
   */

  protected function preprocessForm(JForm $form, $data) {

    // More robustly checked on the component level permissions?
    // E.g. at the moment any user who is not owner can edit this? 
    // e.g. add a new permission core.edit.property.changeparent    

    $canDo = $this->getState('actions.permissions', array());
    // If we don't come from a view then this maybe empty so we reset it.
    if (empty($canDo)) {
      $canDo = HelloWorldHelper::getActions();
    }

    // Check the change parent ability of this user
    if (!$canDo->get('helloworld.edit.property.owner')) {
      $user = JFactory::getUser();
      // Set the default owner to the user creating this.
      $form->setFieldAttribute('created_by', 'type', 'hidden');
      $form->setFieldAttribute('created_by', 'default', $user->id);
    }

    // Set the location details accordingly, needed for one of the form field types... 
    if (!empty($data->latitude) && !empty($data->longitude)) {
      $form->setFieldAttribute('city', 'latitude', $data->latitude);
      $form->setFieldAttribute('city', 'longitude', $data->longitude);
    }
  }

  /**
   * Method to return the location details based on the city the user has chosen
   *
   * @param   int    $city, the nearest town/city
   * 
   * @return  mixed
   *
   * @since   11.1
   */
  protected function getLocationDetails($city) {

    $location_details_array = array();

    // Get the table instance for the classification table
    JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_classification/tables');

    $table = $this->getTable('Classification', 'ClassificationTable');

    if (!$location_details = $table->getPath($city)) {
      $this->setError($table->getError());
      return false;
    };

    // Loop over the location details and pass them back as an array
    foreach ($location_details as $key => $value) {

      if ($value->level > 0) {
        $location_details_array[] = $value->id;
      }
    }


    return $location_details_array;
  }

  /**
   * Method to test whether a record can be deleted.
   *
   * @param   object  $record  A record object.
   *
   * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
   *
   * @since   11.1
   */
  protected function canEditState() {
    $comtask = JRequest::getVar('task', '', 'POST', 'string');

    $task = explode('.', $comtask);

    $user = JFactory::getUser();

    if ($task[1] == 'orderdown' || $task[1] == 'orderup') {
      return $user->authorise('helloworld.edit.reorder', $this->option);
    } else if ($task[1] == 'publish' || $task[1] == 'unpublish' || $task[1] == 'trash') {
      return $user->authorise('core.edit.state', $this->option);
    } else {
      return false;
    }
  }

  /**
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @param	string	An optional ordering field.
   * @param	string	An optional direction (asc|desc).
   *
   * @return	void
   * @since	1.6
   */
  protected function populateState($ordering = null, $direction = null) {

    $canDo = HelloWorldHelper::getActions();
    $this->setState('actions.permissions', $canDo);

    // List state information.
    parent::populateState();
  }

  /**
   * Overidden method to save the form data.
   *
   * @param   array  $data  The filtered form data.
   *
   * @return  boolean  True on success, False on error.
   *
   * @since   12.2
   */
  public function save($data) {

    $dispatcher = JEventDispatcher::getInstance();
    $table = $this->getTable();
    $key = $table->getKeyName();
    $pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
    $isNew = true;

    $city = (!empty($data['city'])) ? $data['city'] : '';

    // Get the location details (area, region, dept) and update the data array
    $location_details = $this->getLocationDetails($city);

    // Update the location details in the data array...ensures that property will always be in the correct area, region, dept, city etc
    if (!empty($location_details)) {
      $data['area'] = $location_details[0];
      $data['region'] = $location_details[1];
      $data['department'] = $location_details[2];
      $data['city'] = $location_details[3];
    }

    // Include the content plugins for the on save events.
    JPluginHelper::importPlugin('content');

    // Allow an exception to be thrown.
    try {

      // If $data['review'] is true we need to update that new version in the version table
      if ($data['review']) {

        // Get the latest unpublished version id for this listing, that exists in the db
        $new_version = $this->getLatestListingVersion($data['id']);

        if ($new_version->version_id > 0) {
          // Here, we know we have an unpublished version, so need to save changes into new version, not over existing version.
          $table = $this->getTable('PropertyListingsVersion');

          // Set the version ID that we want to bind and store the data against...
          $table->version_id = $new_version->version_id;
        }
      } else { // Here we don't explicitly know if there is a new version
        // Load the exisiting row, if there is one. 
        if ($pk > 0) {
          $table->load($pk);
          $isNew = false;
        }

        // Let's have a before bind trigger
        $new_version_required = $dispatcher->trigger('onContentBeforeBind', array($this->option . '.' . $this->name, $table, $isNew, $data));


        // $version should contain an array with one element. If the array contains true then we need to create a new version...
        if ($new_version_required[0]) {

          // Switch the table model to the version one
          $table = $this->getTable('PropertyListingsVersion');
          $table->set('_tbl_key', 'version_id');
        }
      }

      // Bind the data. This will bind to the appropriate table.
      if (!$table->bind($data)) {
        $this->setError($table->getError());
        return false;
      }

      // Prepare the row for saving
      $this->prepareTable($table);

      // Check the data.
      if (!$table->check()) {
        $this->setError($table->getError());
        return false;
      }

      // Trigger the onContentBeforeSave event.
      $result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));

      if (in_array(false, $result, true)) {
        $this->setError($table->getError());
        return false;
      }

      // Store the data.
      if (!$table->store()) {
        $this->setError($table->getError());
        return false;
      } else {

        // Save is successful, if we are creating 
        if ($new_version_required[0]) {

          // Update the existing property listing to indicate that we have a new version for it.
          // This should only happen the first time we create a new version.
          $table = $this->getTable();
                 

          $table->id = $pk;
          $table->review = 1;
          $table->modified = JFActory::getDate()->toSql();
         
          if (!$table->store()) {
            $this->setError($table->getError());
            return false;
          }
        }
      }
      
      // Save any admin notes, if present
      if (!empty($data['note'])) {

        $note = array();

        $note['property_id'] = $data['id'];
        $note['state'] = 1;
        $note['body'] = $data['note'];
        $note['created_time'] = JFactory::getDate()->toSql();

        $note_table = $this->getTable('Note', 'HelloWorldTable');

        // Bind the data.
        if (!$note_table->bind($note)) {
          $this->setError($note_table->getError());
          return false;
        }

        // Prepare the row for saving
        $this->prepareTable($note_table);

        // Check the data.
        if (!$note_table->check()) {
          $this->setError($note_table->getError());
          return false;
        }
        // Store the data.
        if (!$note_table->store()) {
          $this->setError($note_table->getError());
          return false;
        }
      }

      // Set the table key back to ID so the controller redirects to the right place
      $table->set('_tbl_key', 'id');

      // Clean the cache.
      $this->cleanCache();

      // Trigger the onContentAfterSave event.
      $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));
    } catch (Exception $e) {
      $this->setError($e->getMessage());

      return false;
    }

    $pkName = $table->getKeyName();

    if (isset($table->$pkName)) {
      $this->setState($this->getName() . '.id', $table->$pkName);
    }
    $this->setState($this->getName() . '.new', $isNew);

    return true;
  }

  /*
   * Method to get the version id of the most recent unpublished version
   * 
   * 
   */

  public function getLatestListingVersion($id = '') {
    // Retrieve latest unit version
    $db = $this->getDbo();
    $query = $db->getQuery(true);
    $query->select('version_id');
    $query->from('#__property_listings_versions');
    $query->where('id = ' . (int) $id);
    $query->where('state = 1');
    $query->order('version_id', 'desc');

    $db->setQuery((string) $query);

    try {
      $row = $db->loadObject();
    } catch (RuntimeException $e) {
      JError::raiseError(500, $e->getMessage());
    }

    return $row;
  }
  
  /* 
   * Method to get the full property listing details based on the property listing ID
   *
   * @param id int The property listing ID of the listing to be returned.
   *  
   */
  public function getFullListingDetails($id = '') 
  {
    
    
    
    
  }
  
}
