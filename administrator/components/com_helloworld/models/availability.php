<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * HelloWorld Model
 */
class HelloWorldModelAvailability extends JModelAdmin {

  protected $unit = '';

  /**
   * Overrideen Method to save the form data.
   *
   * @param   array  $data  The form data.
   *
   * @return  boolean  True on success, False on error.
   *
   * @since   12.2
   */
  public function save($data) {

    // Required objects
    $input = JFactory::getApplication()->input;

    $data = new JRegistry($input->get('jform', '', 'array'));

    // Get any data being able to use default values
    $id = $data->get('unit_id', 0);
    $start_date = $data->get('start_date', null);
    $end_date = $data->get('end_date', null);
    $availability_status = $data->get('availability', 1);

    // Allow an exception to be thrown.
    try {
      // Load in existing availability, so we can merge it with this new availability
      $availabilityTable = JTable::getInstance($type = 'Availability', $prefix = 'HelloWorldTable', $config = array());
      $availability = $this->getAvailability($id);

      $availability_by_day = HelloWorldHelper::getAvailabilityByDay($availability, $start_date, $end_date, $availability_status);
      $availability_by_period = HelloWorldHelper::getAvailabilityByPeriod($availability_by_day);

      // Delete existing availability
      // Need to wrap this in some logic
      $unit_id = $id;
      $this->delete($unit_id);

      // Bind the translated fields to the JTable instance
      if (!$availabilityTable->save($id, $availability_by_period)) {
        JError::raiseWarning(500, $availabilityTable->getError());
        return false;
      }

      // Update the availability last updated on field
      $table = $this->getTable('Unit', 'HelloWorldTable');

      $data = array();
      $data['id'] = $id;
      $data['availability_last_updated_on'] = JFactory::getDate()->toSql();

      if (!$table->bind($data)) {
        JError::raiseWarning(500, $table->getError());
        return false;
      }

      if (!$table->store()) {
        JError::raiseWarning(500, $table->getError());
        return false;
      }
    } catch (Exception $e) {
      $this->setError($e->getMessage());
      return false;
    }

    $this->setState($this->getName() . '.id', $id);


    return true;
  }

  /**
   * Returns a reference to the a Table object, always creating it.
   *
   * @param	type	The table type to instantiate
   * @param	string	A prefix for the table class name. Optional.
   * @param	array	Configuration array for model. Optional.
   * @return	JTable	A database object
   * @since	1.6
   */
  public function getTable($type = 'Availability', $prefix = 'HelloWorldTable', $config = array()) {
    return JTable::getInstance($type, $prefix, $config);
  }

  /**
   * Returns the availability for this property
   *
   */
  public function getAvailability($id = '') {

    $id = (!empty($id)) ? $id : (int) $this->getState($this->getName() . '.id');

    $query = $this->getAvailabilityQuery($id);

    try {
      $this->_db->setQuery($query);
      $result = $this->_db->loadObjectList();
    } catch (RuntimeException $e) {
      $this->setError($e->getMessage());
      return false;
    }

    return $result;
  }

  /**
   * Method to generate a query to get the availability for a particular property
   *
   * TO DO: Add a check to ensure that the user requesting the availability
   * is the owner...
   *
   * @param       int $id property id, not primary key in this case
   * @param       boolean $reset reset data
   * @return      boolean
   * @see JTable:load
   */
  public function getAvailabilityQuery($id = null, $reset = true) {
    // Create a new query object.
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);

    $query = $this->_db->getQuery(true);
    $query->select('unit_id, start_date, end_date, availability');
    $query->from($this->_db->quoteName('#__availability'));
    $query->where($this->_db->quoteName('unit_id') . ' = ' . $this->_db->quote($id));
    $this->_db->setQuery($query);

    return $query;
  }

  /**
   * Method to get the record form.
   *
   * @param	array	$data		Data for the form.
   * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
   * @return	mixed	A JForm object on success, false on failure
   * @since	2.5
   */
  public function getForm($data = array(), $loadData = true) {
    // Get the form.
    $form = $this->loadForm('com_helloworld.helloworld', 'availability', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) {
      return false;
    }

    return $form;
  }

  /**
   * Method to get the data that should be injected in the form.
   *
   * @return	mixed	The data for the form.
   * @since	1.6
   */
  protected function loadFormData() {
    // Check the session for previously entered form data.
    $data = JFactory::getApplication()->getUserState('com_helloworld.edit.availability.data', array());
    if (empty($data)) {
      $data = $this->getItem();
    }


    return $data;
  }

  /**
   * Method to get a single record.
   *
   * @param   integer  $pk  The id of the primary key.
   *
   * @return  mixed    Object on success, false on failure.
   *
   * @since   12.2
   */
  public function getItem($pk = null) {
    $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

    $table = $this->getTable('UnitVersions', 'HelloWorldTable');

    if ($pk > 0) {
      // Attempt to load the row.
      $return = $table->load($pk);

      // Check for a table object error.
      if ($return === false && $table->getError()) {
        $this->setError($table->getError());
        return false;
      }
    }

    // Convert to the JObject before adding other data.
    $properties = $table->getProperties(1);
    $item = JArrayHelper::toObject($properties, 'JObject');

    return $item;
  }

}
