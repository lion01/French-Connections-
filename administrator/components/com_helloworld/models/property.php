<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modeladmin');

/**
 * HelloWorldList Model
 */
class HelloWorldModelProperty extends JModelAdmin {
  /*
   * A listing property to store the listing against
   *
   */

  public $listing_id = '';

  /*
   * The owner ID for the user that is renewing...taken from the listing not the session scope
   */
  public $owner_id = '';

  /*
   * Whether this is a renewal or not - determined via the expiry date
   */
  public $renewal = '';

  /**
   * Returns a reference to the a Table object, always creating it.
   *
   * @param	type	The table type to instantiate
   * @param	string	A prefix for the table class name. Optional.
   * @param	array	Configuration array for model. Optional.
   * @return	JTable	A database object
   * @since	1.6
   */
  public function getTable($type = 'Property', $prefix = 'HelloWorldTable', $config = array()) {
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
  public function getForm($data = array(), $loadData = true, $form = '') {

    // Get the form.
    $form = $this->loadForm('com_helloworld.property', 'property', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) {
      return false;
    }

    return $form;
  }

  /*
   * Returns a list of units for a given listing id (using the units model)
   *
   * @param int The id of the parent property listing
   * return array An array of units along with image counts...
   *
   */

  public function getRenewalSummary() {

    // The call to getState also calls populateState (if it hasn't been called already)
    $id = $this->getState($this->getName() . '.id', '');

    if (empty($id)) {
      // No ID
      return false;
    }

    // Get an instance of the units model
    $model = JModelLegacy::getInstance('listing', 'HelloWorldModel');

    $listing = $model->getItems();

    if (!$listing) {
      return false;
    }

    // Get the user details based on the owner of the property
    $this->owner_id = ($listing[0]->created_by) ? $listing[0]->created_by : '';

    // Set the renewal status
    $this->renewal = (!empty($listing[0]->expiry_date)) ? true : false;

    // Get the user details
    $user = $this->getUser($this->owner_id);

    // Get the order summary, consists of item codes and quantities
    $order_summary = $this->summary($listing);

    // Get the item cost details based on the summary
    $item_costs = $this->getItemCosts($order_summary);

    // Add the VAT status to the order summary
    $vat_status = ($user->vat_status) ? $user->vat_status : 'Z';

    // Calculate the value of each line of the order...
    $summary = $this->getOrderTotals($order_summary, $item_costs, $vat_status);

    // Get vouchers, need to pick up any vouchers that are added against a property here
    // Detect the inclusion into the French site network

    $summary = JArrayHelper::toObject($summary);

    return $summary;
  }

  /*
   * Calculate the line costs for the pro forma order
   *
   */

  public function getOrderTotals($order_summary = array(), $item_costs = array(), $vat_status) {

    // Get the vat rate from the item costs config params setting
    $vat = JComponentHelper::getParams('com_itemcosts')->get('vat');

    // Loop over each order line and merge the item cost in
    foreach ($order_summary as $order => &$line) {
      if (array_key_exists($order, $item_costs)) {

        $line = array_merge($order_summary[$order], $item_costs[$order]);
      }

      if ($vat_status == 'S2' || $vat_status == 'S2A') {
        $line['vat'] = $line['quantity'] * $line['cost'] * $vat;
      } else {
        $line['vat'] = 0;
      }
      $line['line_value'] = $line['quantity'] * $line['cost'];
    }

    return $order_summary;
  }

  /*
   * Get the item costs for the renewal
   *
   */

  public function getItemCosts($order_summary = array()) {

    if (empty($order_summary)) {
      return array();
    }

    $items = array_keys($order_summary);

    // Required objects
    $db = JFactory::getDbo();

    foreach ($items as $key => &$item) {
      $item = $db->quote($item);
    }

    $item_codes = implode(',', $items);

    $query = $db->getQuery(true);
    $query->select('code, description as item_description, cost, catid');
    $query->from('#__item_costs');

    $query->where('code in (' . $item_codes . ')');


    $db->setQuery($query);

    $result = $db->loadAssocList($key = 'code');


    return $result;
  }

  /*
   * Method to get the payment form bound with the details of the
   * user renewing the property...
   *
   */

  public function getUserFormDetails() {

    // Get a copy of the form we are using to collect the user invoice address and vat status
    $form = $this->loadForm('com_helloworld.helloworld', 'ordersummary', array('control' => 'jform', 'load_data' => false));

    if (empty($form)) {
      return false;
    }

    $item = $this->getUser($this->owner_id);

    $form->bind($item);

    return $form;
  }

  /*
   * Method to get the payment form
   *
   */

  public function getPaymentForm() {

    $form = $this->loadForm('com_helloworld.helloworld', 'payment', array('control' => 'jform', 'load_data' => false));

    if (empty($form)) {
      return false;
    }

    $data = JFactory::getApplication()->getUserState('com_helloworld.renewal.data', array());
    $data['id'] = $id = $this->getState($this->getName() . '.id', '');

    $form->bind($data);



    return $form;
  }

  /*
   * Method to get the user details...
   *
   */

  protected function getUser($user_id = '') {

    // Now get the user details
    $table = $this->getTable('UserProfileFc', 'HelloWorldTable');

    // Attempt to load the row.
    $return = $table->load($user_id);

    // Check for a table object error.
    if ($return === false && $table->getError()) {
      $this->setError($table->getError());
      return false;
    }

    $properties = $table->getProperties(1);

    $item = JArrayHelper::toObject($properties, 'JObject');
    // We need to do the following so we can group the vat fields into a JForm XML field definition
    // Group vat fields
    $vat = array();
    $vat['vat_status'] = $item->vat_status;
    $vat['company_number'] = $item->company_number;
    $vat['vat_number'] = $item->vat_number;

    $item->vat = $vat;

    // Group invoice address fields...
    $invoice_address = array();
    $invoice_address['address1'] = $item->address1;
    $invoice_address['address2'] = $item->address2;
    $invoice_address['city'] = $item->city;
    $invoice_address['region'] = $item->region;
    $invoice_address['postal_code'] = $item->postal_code;
    $invoice_address['country'] = $item->country;

    $item->invoice_address = $invoice_address;

    return $item;
  }

  /*
   * Method to process and generate a proforma order...
   *
   *
   */

  protected function summary($units = array()) {

    // $units contains the listing including all the units and so on.
    // From this we can generate our pro forma order
    // Need to know the user invoice address and VAT status for this user
    // If we don't know the VAT status then intially apply it.
    // If the property is a B&B, then only charge for one unit, regardless of how many units are listed.
    // If the expiry date is set then this is a renewal. Regardless of whether it has actually expired or not
    // Item codes will be dependent on whether it is a renewal or not
    // Flag to indicate whether we've alrady counted a b&B unit

    $bed_and_breakfast = false;
    $count_additional_units = false;

    // A unit counter
    $unit_count = 0;


    // Total images holder
    $image_count = 0;

    // Item costs line holder
    $item_costs = array();

    // Loop over all the units found
    // Only record image count for one unit as the tariff will be set max images per unit
    // not as a listing as a whole.
    foreach ($units as $unit) {

      if ($unit->accommodation_type == 25) {
        ($count_additional_units) ? $unit_count++ : '';

        $count_additional_units = true;
      } else {

        (!$bed_and_breakfast) ? $unit_count++ : '';

        $bed_and_breakfast = true;
      }

      // If image count less than number of images on this unit, update image count
      ($image_count < $unit->images) ? $image_count = $unit->images : '';
    }

    // Below covers most cases
    // Need to also consider
    // Site network, e.g. French Translations
    // Video
    // Booking form, although not counted at renewal
    // Link to personal site
    // Special offers - figure out how to deal with

    if ($this->renewal) {

      // Determine the item costs
      if ($image_count >= 8) { // Renewal
        $item_costs['1004-009']['quantity'] = 1;

        if ($unit_count > 1) {

          $item_costs['1004-006']['quantity'] = $unit_count;
        }
      } else { // Image count must be less than 8 but still a renewal
        if ($unit_count > 1) {
          $item_costs['1004-006']['quantity'] = $unit_count;
        }

        $item_costs['1004-002']['quantity'] = 1;

        if ($image_count > 4 && $image_count <= 7) {

          // Additional images
          $additional_images = $image_count - 4;

          $item_costs['1004-005']['quantity'] = $additional_images;
        }
      }
    } else { // New property being published for first time
      // Determine the item costs
      if ($image_count >= 8) {
        $item_costs['1004-009']['quantity'] = 1;

        if ($unit_count > 1) {
          $item_costs['1004-006']['quantity'] = $unit_count;
        }
      } else { // Image count must be less than 8
        // Add the base item price
        $item_costs['1005-002']['quantity'] = 1;
        if ($unit_count > 1) {

          $item_costs['1005-006']['quantity'] = $unit_count;
        }

        if ($image_count > 4 && $image_count <= 7) {

          // Additional images
          $additional_images = $image_count - 4;

          $item_costs['1005-005']['quantity'] = $additional_images;
        }
      }
    }
    return $item_costs;
  }

  public function processRenewal($data) {

    // Set the state for this model so it knows which listing to get the summary of...
    $this->setState($this->getName() . '.id', $data['id']);

    // Set the property id
    $this->property_id = $data['id'];

    // Get the order summary details
    $order = $this->getRenewalSummary();

    // Get the invoice component parameters which hold the protx settings
    $protx_settings = JComponentHelper::getParams('com_itemcosts');


    $sngTotal = 0.0;
    $strBasket = "";
    $iBasketItems = 0;
    $VendorTxCode = '';

    // Okay, so we have validated the form and we have the order summary
    // First off, generate a VendorTxCode and stash what we have in the db
    $VendorTxCode = $this->owner_id . ' - ' . $data['id'] . ' - ' . date("ymdHis", time()) . rand(0, 32000) * rand(0, 32000);

    // Loop over the order lines and make the basket
    foreach ($order as $item => $line) {
      $iBasketItems = $iBasketItems + 1;
      $strBasket = $strBasket . ':' . 'PRN[' . $data['id'] . '] ' . 'OWNER[' . $this->owner_id . '] ' . '[' . $line->code . '] [' . $line->item_description . ']:' . $line->quantity;
      $strBasket = $strBasket . ":" . number_format($line->cost, 2);/** Price ex-Vat * */
      $strBasket = $strBasket . ":" . number_format($line->vat, 2);/** VAT component * */
      $strBasket = $strBasket . ":" . number_format($line->cost, 2);/** Item price * */
      $strBasket = $strBasket . ":" . number_format($line->line_value, 2);/** Line total * */
      $sngTotal = $sngTotal + $line->line_value;
    }



    // Update the data array with a few more bits and pieces
    $data['Amount'] = $sngTotal;
    $data['VendorTxCode'] = $VendorTxCode;
    $data['user_id'] = $this->owner_id;
    $data['property_id'] = $data['id'];
    $data['DateCreated'] = JFactory::getDate()->toSql();
    $data['id'] = '';
    $data['CardLastFourDigits'] = substr($data['CardNumber'], -4, 4);

    // Add the total number of items to the basket string
    $strBasket = $iBasketItems . $strBasket;

    if (!$this->saveProtxTransaction($data)) {
      // Error is set in the function
      return false;
    }


    // So let's put the transaction into the database
    $table = JTable::getInstance('protxtransactionlines', 'HelloWorldTable');

    // Add each of the order lines to the transaction lines table
    foreach ($order as $line) {
      $line->VendorTxCode = $VendorTxCode;
      $line->id = '';
      // Bind the data.
      if (!$table->bind($line)) {
        $this->setError($table->getError());
        return false;
      }

      // Store the data.
      if (!$table->store()) {
        $this->setError($table->getError());
        return false;
      }
    }

    $strProtocol = $protx_settings->get('VPSProtocol');
    $strTransactionType = $protx_settings->get('TransactionType');
    $strVendorName = $protx_settings->get('VendorName');
    $strCurrency = $protx_settings->get('Currency');
    $strPurchaseURL = $protx_settings->get('PurchaseURL');

    /* Now to build the Sage Pay Direct POST.  For more details see the Sage Pay Direct Protocol 2.23
     * * NB: Fields potentially containing non ASCII characters are URLEncoded when included in the POST */
    $strPost = "VPSProtocol=" . $strProtocol;
    $strPost = $strPost . "&TxType=" . $strTransactionType; //PAYMENT by default.  You can change this in the includes file
    $strPost = $strPost . "&Vendor=" . $strVendorName;
    $strPost = $strPost . "&VendorTxCode=" . $VendorTxCode; //As generated above
    $strPost = $strPost . "&Amount=" . number_format($sngTotal, 2); //Formatted to 2 decimal places with leading digit but no commas or currency symbols **
    $strPost = $strPost . "&Currency=" . $strCurrency;
    $strPost = $strPost . "&CardType=" . $data['CardType'];

    $strPost = $strPost . "&CardHolder=" . $data['CardHolder'];
    $strPost = $strPost . "&CardNumber=" . $data['CardNumber'];
    if (strlen($data['CardStartDate']) > 0)
      $strPost = $strPost . "&StartDate=" . $data['CardStartDate'];
    $strPost = $strPost . "&ExpiryDate=" . $data['CardExpiryDate'];
    if (strlen($data['IssueNumber']) > 0)
      $strPost = $strPost . "&IssueNumber=" . $data['IssueNumber'];
    $strPost = $strPost . "&CV2=" . $data['CV2'];

    // Send the IP address of the person entering the card details
    $strPost = $strPost . "&ClientIPAddress=" . $_SERVER['REMOTE_ADDR'];
    /* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default **
     * * It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
    $strPost = $strPost . "&Apply3DSecure=0";

    /** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
    if ($strTransactionType !== "AUTHENTICATE")
      $strPost = $strPost . "&ApplyAVSCV2=2";
    // Add the basket
    $strPost = $strPost . "&Basket=" . urlencode($strBasket); //As created above

    /* Billing Details
     * This section is optional in its entirety but if one field of the address is provided then all non-optional fields must be provided
     * If AVS/CV2 is ON for your account, or, if paypal cardtype is specified and its not via PayPal Express then this section is compulsory */
    $strPost = $strPost . "&BillingFirstnames=" . urlencode($data["BillingFirstnames"]);
    $strPost = $strPost . "&BillingSurname=" . urlencode($data["BillingSurname"]);
    $strPost = $strPost . "&BillingAddress1=" . urlencode($data["BillingAddress1"]);
    if (strlen($data["BillingAddress2"]) > 0)
      $strPost = $strPost . "&BillingAddress2=" . urlencode($data["BillingAddress2"]);
    $strPost = $strPost . "&BillingCity=" . urlencode($data["BillingCity"]);
    $strPost = $strPost . "&BillingPostCode=" . urlencode($data["BillingPostCode"]);
    $strPost = $strPost . "&BillingCountry=" . urlencode($data["BillingCountry"]);

    /* Delivery Details
     * * This section is optional in its entirety but if one field of the address is provided then all non-optional fields must be provided
     * * If paypal cardtype is specified then this section is compulsory */
    $strPost = $strPost . "&DeliveryFirstnames=" . urlencode($data["BillingFirstnames"]);
    $strPost = $strPost . "&DeliverySurname=" . urlencode($data["BillingSurname"]);
    $strPost = $strPost . "&DeliveryAddress1=" . urlencode($data["BillingAddress1"]);
    $strPost = $strPost . "&DeliveryCity=" . urlencode($data["BillingCity"]);
    $strPost = $strPost . "&DeliveryPostCode=" . urlencode($data["BillingPostCode"]);
    $strPost = $strPost . "&DeliveryCountry=" . urlencode($data["BillingCountry"]);


    /* Send the account type to be used for this transaction.  Web sites should us E for e-commerce **
     * * If you are developing back-office applications for Mail Order/Telephone order, use M **
     * * If your back office application is a subscription system with recurring transactions, use C **
     * * Your Sage Pay account MUST be set up for the account type you choose.  If in doubt, use E * */
    $strPost = $strPost . "&AccountType=E";

    $arrResponse = $this->requestPost($strPurchaseURL, $strPost);
    /* Analyse the response from Sage Pay Direct to check that everything is okay
     * * Registration results come back in the Status and StatusDetail fields */

    $arrResponse['VendorTxCode'] = $VendorTxCode;

    $strStatus = $arrResponse["Status"];
    $strStatusDetail = $arrResponse["StatusDetail"];
    // Card details and address details have been checked. Can now process accordingly...

    /* If this isn't 3D-Auth, then this is an authorisation result (either successful or otherwise) **
     * * Get the results form the POST if they are there */
    $strVPSTxId = $arrResponse["VPSTxId"];
    $strSecurityKey = $arrResponse["SecurityKey"];
    $strTxAuthNo = $arrResponse["TxAuthNo"];
    $strAVSCV2 = $arrResponse["AVSCV2"];
    $strAddressResult = $arrResponse["AddressResult"];
    $strPostCodeResult = $arrResponse["PostCodeResult"];
    $strCV2Result = $arrResponse["CV2Result"];
    $str3DSecureStatus = $arrResponse["3DSecureStatus"];
    $strCAVV = $arrResponse["CAVV"];

    // Update the database and redirect the user appropriately
    if ($strStatus == "OK")
      $strDBStatus = "AUTHORISED - The transaction was successfully authorised with the bank.";
    elseif ($strStatus == "MALFORMED")
      $strDBStatus = "MALFORMED - The StatusDetail was:" . mysql_real_escape_string(substr($strStatusDetail, 0, 255));
    elseif ($strStatus == "INVALID")
      $strDBStatus = "INVALID - The StatusDetail was:" . mysql_real_escape_string(substr($strStatusDetail, 0, 255));
    elseif ($strStatus == "NOTAUTHED")
      $strDBStatus = "DECLINED - The transaction was not authorised by the bank.";
    elseif ($strStatus == "REJECTED")
      $strDBStatus = "REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.";
    elseif ($strStatus == "AUTHENTICATED")
      $strDBStatus = "AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.";
    elseif ($strStatus == "REGISTERED")
      $strDBStatus = "REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.";
    elseif ($strStatus == "ERROR")
      $strDBStatus = "ERROR - There was an error during the payment process.  The error details are: " . mysql_real_escape_string($strStatusDetail);
    else
      $strDBStatus = "UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: " . mysql_real_escape_string($strStatus) . ", with StatusDetail:" . mysql_real_escape_string($strStatusDetail);
    echo $strDBStatus;

    $this->saveProtxTransaction($arrResponse, 'VendorTxCode');

    // Okay now we have processed the transaction and update it in the db.
    switch ($strStatus) {
      case 'OK':
        // Payment authorised so update the expiry date
        // Clear down any vouchers?
        // Work out the special offers
        // etc
        $this->updateExpiryDate();
        //$this->setMessage("AUTHORISED - The transaction was successfully authorised with the bank.");
        return true;
        break;
      case 'MALFORMED':
        $this->setError("MALFORMED - The StatusDetail was:" . mysql_real_escape_string(substr($strStatusDetail, 0, 255)));
        return false;
        break;

      case 'INVALID':
        $this->setError("INVALID - The StatusDetail was:" . mysql_real_escape_string(substr($strStatusDetail, 0, 255)));
        return false;
        break;

      case 'NOTAUTHED':
        $this->setError("DECLINED - The transaction was not authorised by the bank.");
        return false;
        break;

      case 'DECLINED':
        $this->setError("DECLINED - The transaction was not authorised by the bank.");
        return false;
        break;

      case 'REJECTED':
        $this->setError("REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.");
        return false;
        break;

      case 'ERROR':
        $this->setError("ERROR - There was an error during the payment process.  The error details are: " . mysql_real_escape_string($strStatusDetail));
        return false;
        break;

      default:
        $this->setError("UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: " . mysql_real_escape_string($strStatus) . ", with StatusDetail:" . mysql_real_escape_string($strStatusDetail));
        return false;
        break;
    }



    // Figure out what to reuturn to our controller an

  }

  public function updateExpiryDate() {

  }

  /*   * ***********************************************************
    Send a post request with cURL
    $url = URL to send request to
    $data = POST data to send (in URL encoded Key=value pairs)
   * *********************************************************** */

  public function requestPost($url, $data) {
    // Set a one-minute timeout for this script
    set_time_limit(60);

    // Initialise output variable
    $output = array();

    // Open the cURL session
    $curlSession = curl_init();

    // Set the URL
    curl_setopt($curlSession, CURLOPT_URL, $url);
    // No headers, please
    curl_setopt($curlSession, CURLOPT_HEADER, 0);
    // It's a POST request
    curl_setopt($curlSession, CURLOPT_POST, 1);
    // Set the fields for the POST
    curl_setopt($curlSession, CURLOPT_POSTFIELDS, $data);
    // Return it direct, don't print it out
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
    // This connection will timeout in 30 seconds
    curl_setopt($curlSession, CURLOPT_TIMEOUT, 30);
    //The next two lines must be present for the kit to work with newer version of cURL
    //You should remove them if you have any problems in earlier versions of cURL
    curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 1);

    //Send the request and store the result in an array

    $rawresponse = curl_exec($curlSession);
    //Store the raw response for later as it's useful to see for integration and understanding
    $_SESSION["rawresponse"] = $rawresponse;
    //Split response into name=value pairs
    $response = split(chr(10), $rawresponse);
    // Check that a connection was made
    if (curl_error($curlSession)) {
      // If it wasn't...
      $output['Status'] = "FAIL";
      $output['StatusDetail'] = curl_error($curlSession);
    }

    // Close the cURL session
    curl_close($curlSession);

    // Tokenise the response
    for ($i = 0; $i < count($response); $i++) {
      // Find position of first "=" character
      $splitAt = strpos($response[$i], "=");
      // Create an associative (hash) array with key/value pairs ('trim' strips excess whitespace)
      $output[trim(substr($response[$i], 0, $splitAt))] = trim(substr($response[$i], ($splitAt + 1)));
    } // END for ($i=0; $i<count($response); $i++)
    // Return the output
    return $output;
  }

// END function requestPost()

  /*
   * Function to save a transaction out to the protx transaction table
   */

  public function saveProtxTransaction($data = array(), $key = '') {
    // So let's put the transaction into the database

    $table = JTable::getInstance('protxtransactions', 'HelloWorldTable');

    if (!empty($key)) {
      $table->set('_tbl_key', 'VendorTxCode');
    }

    // Bind the data.
    if (!$table->bind($data)) {
      $this->setError($table->getError());
      return false;
    }

    // Store the data.
    if (!$table->store()) {
      $this->setError($table->getError());
      return false;
    }

    return true;
  }

}
