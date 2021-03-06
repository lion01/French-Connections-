<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
jimport('joomla.user.user');
jimport('joomla.user.helper');

/**
 * HelloWorld Controller
 */
class ImportControllerReviews extends JControllerForm {

  public function import() {

    // Check that this is a valid call from a logged in user.
    JSession::checkToken('POST') or die('Invalid Token');

    // The file we are importing from
    $userfile = JRequest::getVar('import_file', null, 'files', 'array');

    // Open a handle to the import file
    $handle = fopen($userfile['tmp_name'], "r");

    // Get a db instance
    $db = JFactory::getDBO();

    while (($line = fgetcsv($handle)) !== FALSE) {

      if (!empty($line[2]) && $line[2] !='NULL') {

        // Start building a new query to insert any attributes...
        $query = $db->getQuery(true);
        $query->clear();

        // Reset the query, ready for insert
        $query->clear();
        $query = $db->getQuery(true);

        $query->insert('#__reviews');
        $query->columns(array('unit_id', 'review_text', 'date', 'rating', 'guest_name', 'guest_email','state','published','created','created_by'));

        $insert_string = '';
        $date = new DateTime($line[3]);

        $review_date = $date->format('Y-m-d H:i:s');

        $insert_string = $line[0] . ',' . $db->quote(mysql_escape_string($line[2])) . ',' . $db->quote($review_date) . ',' . $db->quote($line[4]) . ',' . $db->quote($line[5]) . ',' . $db->quote($line[6]).',0,1,'.$db->quote($review_date).',1';
        $query->values($insert_string);

        // Set and execute the query
        $db->setQuery($query);

        if (!$db->execute()) {
          $e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED_UPDATE_ASSET_ID', $db->getErrorMsg()));
          print_r($db->getErrorMsg());
          print_r($insert_string);
          die;
        }
      }
    }

    fclose($handle);
    $this->setMessage('Reviews imported, hooray!');
    $this->setRedirect('index.php?option=com_import&view=reviews');
  }

}
