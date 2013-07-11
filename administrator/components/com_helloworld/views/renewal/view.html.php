<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HelloWorlds View
 */
class HelloWorldViewRenewal extends JViewLegacy {

  /**
   * HelloWorld raw view display method
   * This is used to check how many properties the user has
   * when they click 'new' on the property manager.
   *
   * @return void
   */
  function display($tpl = null) {

    $app = JFactory::getApplication();
    $input = $app->input;
    $this->id = $input->get('id', '', 'int');
    $this->extension = $input->get('option', '', 'string');

    $layout = $input->get('layout','','string');

    // Add the UserProfile model so we can get the user details and the form...
    //$this->setModel(JModelLegacy::getInstance('UserProfile', 'HelloWorldModel'), true);

    // Add the Property model so we can get the renewal details...
    $this->setModel(JModelLegacy::getInstance('Property', 'HelloWorldModel'), true);

    // Get an instance of the property model
    $property = $this->getModel('Property');

    // Get the units and image details they against this property
    $this->summary = $this->get('RenewalSummary');

    // Get an instance of the default model
    $user = $this->getModel();

    // And set a the owner_id property so we can use it
    $user->owner_id = $property->owner_id;

    if ($layout == 'payment') {
      // Get the payment form
      $this->form = $this->get('PaymentForm');
    }

    // Set the document
    $this->setDocument();

    // Set the document
    $this->addToolBar();

    // Display the template
    parent::display($tpl);

  }

  /**
   * Method to set up the document properties
   *
   * @return void
   */
  protected function setDocument() {
    $document = JFactory::getDocument();

    // Set the page title
    JToolBarHelper::title(JText::sprintf('COM_HELLOWORLD_HELLOWORLD_RENEWAL_PAYMENT_SUMMARY',$this->id));

    //$document->addScript(JURI::root() . "/administrator/components/com_helloworld/js/submitbutton.js", true, false);
    $document->addScript(JURI::root() . "/administrator/components/com_helloworld/js/vat.js", 'text/javascript', true, false);
    $document->addScript(JURI::root() . "/administrator/components/com_helloworld/js/submitbutton.js", 'text/javascript', true, false);

    JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');

  }

  /**
   * Setting the toolbar
   */
  protected function addToolBar() {
    // Register the JHtmlProperty class
    JLoader::register('JHtmlProperty', JPATH_COMPONENT . '/helpers/html/property.php');

    // Get component level permissions
    $canDo = HelloWorldHelper::getActions();

    $document = JFactory::getDocument();
		$document->setTitle(JText::sprintf('COM_HELLOWORLD_HELLOWORLD_RENEWAL_PAYMENT_SUMMARY',$this->id));

    // Display a helpful navigation for the owners
    if ($canDo->get('helloworld.ownermenu.view')) {

      $view = strtolower(JRequest::getVar('view'));

      $canDo = HelloWorldHelper::addSubmenu($view);

      // Add the side bar
      $this->sidebar = JHtmlSidebar::render();
    }


    JToolBarHelper::cancel('propertyversions.cancel', 'JTOOLBAR_CANCEL');

    JToolBarHelper::help('COM_HELLOWORLD_HELLOWORLD_NEW_PROPERTY_HELP_VIEW', true);
  }

}
