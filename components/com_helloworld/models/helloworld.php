<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * HelloWorld Model
 */
class HelloWorldModelHelloWorld extends JModelItem
{
	/**
	 * @var object item
	 */
	protected $item;
 
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState() 
	{
		$app = JFactory::getApplication();
		// Get the message id
		$id = JRequest::getInt('id');
		$this->setState('property.id', $id);
 
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		parent::populateState();
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
	public function getTable($type = 'HelloWorld', $prefix = 'HelloWorldTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
 
	/**
	 * Get the message
	 * @return object The message to be displayed to the user
	 */
	public function getItem() 
	{
		if (!isset($this->item)) 
		{
			// Get the language for this request 
			$lang = & JFactory::getLanguage()->getTag();
			// Get the state for this property ID
			$id = $this->getState('property.id');
		
			if ($lang === 'fr-FR') {
				$select = 'cat.title,catid,hel.id,hel.params,trans.greeting,trans.description,lang,occupancy,swimming,latitude,longitude,nearest_town';
			} else {
				$select = 'cat.title,catid,hel.id,hel.params,hel.greeting,hel.description,lang,occupancy,swimming,latitude,longitude,nearest_town';
			}

			$this->_db->setQuery($this->_db->getQuery(true)
				->from('#__helloworld as hel')
				->leftJoin('#__categories AS cat ON hel.catid = cat.id')
				->select($select)
				->leftJoin('#__helloworld_translations AS trans ON hel.id = trans.property_id')
				->where('hel.id='. (int)$id));

			if (!$this->item = $this->_db->loadObject()) 
			{
				$this->setError($this->_db->getError());
			}
			else
			{
				// Load the JSON string
				$params = new JRegistry;
				$params->loadJSON($this->item->params);
				$this->item->params = $params;
 
				// Merge global params with item params
				$params = clone $this->getState('params');
				$params->merge($this->item->params);
				$this->item->params = $params;
			}
		}
		return $this->item;
	}
}
