<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Displays options as a list of check boxes.
 * Multiselect may be forced to be true.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @see         JFormFieldCheckbox
 * @since       11.1
 */
class JFormFieldFacilities extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Facilities';

	/**
	 * Flag to tell the field to always be in multiple values mode.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $forceMultiple = true;

	/**
	 * Method to get the field input markup for check boxes.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = array();

		// Initialize some field attributes.
		$class = $this->element['class'];
    //$labelClass = $this->element['labelclass'];
    
    $checkedOptions = explode(',', (string) $this->element['checked']);

		// Start the checkbox field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

		// Get the field options.
		$options = $this->getOptions();

		// Build the checkbox field output.
		
		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			if (!isset($this->value) || empty($this->value))
			{
				$checked = (in_array((string) $option->value, (array) $checkedOptions) ? ' checked="checked"' : '');
			}
			else
			{
				$value = !is_array($this->value) ? explode(',', $this->value) : $this->value;
				$checked = (in_array((string) $option->value, $value) ? ' checked="checked"' : '');
			}

			$disabled = !empty($option->disable) ? ' disabled="disabled"' : '';
			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$html[] = '<label class="'. $class .'" for="' . $this->id . $i . '"' . $class . '>' . JText::_($option->text);

			$html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '"' . ' value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . '/>';
			$html[] = '</label>';

		}

		// End the checkbox field output.
		$html[] = '</fieldset>';

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();
    
    // This is passed in from the form field XML definition
 		$classificationID = $this->element['id']? $this->element['id'] : 1;  
    
    $lang = JFactory::getLanguage();
    $db		= JFactory::getDbo();

    $query	= $db->getQuery(true);
    
    // Retrieve based on the current editing language
    if ($lang->getTag() === 'en-GB') {
      $query->select('a.id as value, a.title AS text,  a.published');
    } else {
      $query->select('a.id as value, c.title as text, a.published');
    }
		$query->from('#__attributes AS a');
    $query->join('LEFT', $db->quoteName('#__attributes_type').' AS b ON a.attribute_type_id = b.id');

    // If any other language that en-GB load in the translation based on the lang->getTag() function...
    if ($lang->getTag() != 'en-GB') {  
      $query->join('LEFT', $db->quoteName('#__attributes_translation').' as c on c.attribute_id = a.id');
      $query->where("c.language_code = '" . $lang->getTag()."'");
    }
    
		$query->where('b.id='.$classificationID);
    $query->where('a.published = 1');
		
    
    
    // Get the options.
		$db->setQuery($query);

		$facilities = $db->loadObjectList();

    // Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}
    
    
		foreach ($facilities as $option)
		{


			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', $option->value, $option->text, 'value', 'text',
				( $option->published == 'true')
			);


			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
