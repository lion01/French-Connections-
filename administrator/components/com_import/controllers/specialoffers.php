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
class ImportControllerSpecialOffers extends JControllerForm {

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

        // First need to determine if this review is against a parent or a unit
        $query->select('id');
        $query->from('#__helloworld');
        $query->where('id=' . $line[3] . ' and parent_id=' . $line[2]);

        // Execute
        $db->setQuery($query);
        $result = $db->loadRow();

        if (count($result > 0) && isset($result)) {
          // Review is against a unit
          $property_id = $line[3];
        } else {
          $property_id = $line[2];
        }
        
    

        // Reset the query, ready for insert
        $query->clear();
        $query = $db->getQuery(true);


        $query->insert('#__special_offers');
        $query->columns(array('id','published','property_id','start_date','end_date','title','description','status','date_created','approved_date','approved_by','created_by'));

        $insert_string = '';

        $insert_string = $line[0].','.$line[1].','.$property_id.','.$db->quote($line[4]).','. $db->quote($line[5]).','. $db->quote($line[6]) . ',' . $db->quote($line[7]) . ','.$db->quote($line[8]).','.$db->quote($line[9]).','.$db->quote($line[10]).','.$line[11].','.$line[12];
        
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
    $this->setMessage('Special offers imported, hooray!');
    $this->setRedirect('index.php?option=com_import&view=specialoffers');
  }

}
