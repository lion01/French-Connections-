<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HelloWorlds View
 */
class HelloWorldViewHelloWorlds extends JViewLegacy {

  protected $state;

  /**
   * HelloWorlds view display method
   * @return void
   */
  function display($tpl = null) {
    // Find the user details
    $user = JFactory::getUser();
    $userID = $user->id;

    // Get data from the model
    $items = $this->get('Items');

    // Record the number of properties here in the user session scope
    JApplication::setUserState("com_helloworlds_property_count_$userID", count($items));

    $pagination = $this->get('Pagination');
    
    $this->state = $this->get('State');

    // Assign data to the view
    $this->items = $items;
    $this->pagination = $pagination;

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      JError::raiseError(500, implode('<br />', $errors));
      return false;
    }

    // Preprocess the list of items to find ordering divisions.
    foreach ($this->items as &$item) {
      $this->ordering[$item->parent_id][] = $item->id;
    }

    // Set the toolbar
    $this->addToolBar();

    // Add the side bar
    $this->sidebar = JHtmlSidebar::render();
   

    // Display the template
    parent::display($tpl);

    // Set the document
    $this->setDocument();
  }

  /**
   * Setting the toolbar
   */
  protected function addToolBar() {
    $document = JFactory::getDocument();
    $document->addStyleDeclaration('.icon-48-helloworld {background-image: url(../media/com_helloworld/images/fc-logo-48x48.png);}');

    $user = JFactory::getUser();
    
    // Here we register a new JButton which simply uses the ajax squeezebox rather than the iframe handler
    JLoader::register('JToolbarButtonAjaxpopup', JPATH_ROOT . '/administrator/components/com_helloworld/buttons/Ajaxpopup.php');

    // Here we register a new JButton which simply uses the ajax squeezebox rather than the iframe handler
    JLoader::register('JToolbarButtonAjaxpopupchooseowner', JPATH_ROOT . '/administrator/components/com_helloworld/buttons/Ajaxpopupchooseowner.php');

    $canDo = HelloWorldHelper::getActions();

    JToolBarHelper::title(JText::_('COM_HELLOWORLD_MANAGER_HELLOWORLDS'), 'helloworld');
    if ($canDo->get('core.create')) {
      JToolBarHelper::addNew('helloworld.addnew', 'COM_HELLOWORLD_HELLOWORLD_ADD_NEW_PROPERTY', false);
    }
    if ($canDo->get('core.edit') || ($canDo->get('core.edit.own'))) {
      JToolBarHelper::editList('helloworld.edit', 'JTOOLBAR_EDIT');
    }
    if ($canDo->get('core.delete')) {
      JToolBarHelper::deleteList('', 'helloworlds.delete', 'JTOOLBAR_DELETE');
    }

    if ($canDo->get('core.edit.state')) {
      JToolBarHelper::divider();
      JToolBarHelper::publish('helloworlds.publish', 'JTOOLBAR_PUBLISH', true);
      JToolBarHelper::unpublish('helloworlds.unpublish', 'JTOOLBAR_UNPUBLISH', true);
      JToolBarHelper::trash('helloworlds.trash');
    }

    if ($canDo->get('core.admin')) {
      JToolBarHelper::divider();
      JToolBarHelper::preferences('com_helloworld');
    }

    // We need to add options to the published filter to implement the PFR queue?
    $publishedOptions = JHtml::_('jgrid.publishedOptions');

    $PFR['value'] = -3;
    $PFR['text'] = 'COM_HELLOWORLD_HELLOWORLD_FOR_REVIEW';
    $PFR['disable'] = '';

    $publishedOptions[] = $PFR;



    // Check that the user is authorised to view the filters.
    if ($canDo->get('helloworld.filter')) {
      JHtmlSidebar::addFilter(
              JText::_('JOPTION_SELECT_PUBLISHED'), 'filter_published', JHtml::_('select.options', $publishedOptions, 'value', 'text', $this->state->get('filter.published'), true)
      );

      JHtmlSidebar::addFilter(
              JText::_('JOPTION_SELECT_CATEGORY'), 'filter_category_id', JHtml::_('select.options', JHtml::_('category.options', 'com_content'), 'value', 'text', $this->state->get('filter.category_id'))
      );

      JHtmlSidebar::addFilter(
              JText::_('JOPTION_SELECT_ACCESS'), 'filter_access', JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
      );

      JHtmlSidebar::addFilter(
              JText::_('JOPTION_SELECT_LANGUAGE'), 'filter_language', JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'))
      );
    }
    
    // Display a helpful navigation for the owners 
    if ($canDo->get('helloworld.ownermenu.view')) {
    
      $view = strtolower(JRequest::getVar('view'));
      
      JHtmlSidebar::addEntry(JText::_('COM_HELLOWORLD_SUBMENU_SMS_NOTIFICATIONS'), 'index.php?option=com_admin&view=profile&layout=edit&id=' . $user->id . '#sms', ($view == 'profile'));
      JHtmlSidebar::addEntry(JText::_('COM_HELLOWORLD_SUBMENU_RENTAL_ACCOMMODATION'), 'index.php?option=com_helloworld', ($view == 'helloworlds'));
      JHtmlSidebar::addEntry(JText::_('COM_HELLOWORLD_SUBMENU_REALESTATE_ACCOMMODATION'), 'index.php?option=com_realestate', ($view == 'realestate'));

      
    }
    
    
    
  }

  /**
   * Method to set up the document properties
   *
   * @return void
   */
  protected function setDocument() {
    $document = JFactory::getDocument();
    $document->setTitle(JText::_('COM_HELLOWORLD_ADMINISTRATION'));
    $document->addScript(JURI::root() . "/administrator/components/com_helloworld/js/submitbutton.js");
    $document->addStyleSheet(JURI::root() . "administrator/components/com_helloworld/css/bootstrap-button.css", 'text/css', "screen");

    JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
  }

}
