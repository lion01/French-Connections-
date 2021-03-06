<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 */
class ReviewsControllerReviews extends JControllerForm
{
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	public function submit()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app    = JFactory::getApplication();
		$model  = $this->getModel('reviews');
		$params = JComponentHelper::getParams('com_reviews');
		$stub   = $this->input->get('id','','int');
		$id     = (int) $stub;
    $CurrentUser =& JFactory::getUser();
		// Get the data from POST
		$data  = $this->input->post->get('jform', array(), 'array');
    
    // Set additional data fields 
    $data['published'] = 0; // Default to unpublish, user either publishes, or trashes and then delete the review
    $data['created'] = date('Y-m-d H:i:s');
    $data['created_by'] = $CurrentUser->id;
           
    // Get the property details we are adding a review for.
    $property = $model->getItem($id);
    
          

		// Check for a valid session cookie
		if($params->get('validate_session', 0)) {
      if(JFactory::getSession()->getState() != 'active'){
				JError::raiseWarning(403, JText::_('COM_CONTACT_SESSION_INVALID'));

				// Save the data in the session.
				$app->setUserState('com_reviews.review.data', $data);

				// Redirect back to the contact form.
				$this->setRedirect(JRoute::_('index.php?option=com_reviews&view=reviews&Itemid=167&id='.$stub, false));
				return false;
			}
		}

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}

		$validate = $model->validate($form, $data);

		if ($validate === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();
			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'error');
				} else {
					$app->enqueueMessage($errors[$i], 'error');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_reviews.review.data', $data);

			// Redirect back to the contact form.
			$this->setRedirect(JRoute::_('index.php?option=com_reviews&view=reviews&Itemid=167&id='.$stub, false));
			return false;
		}
    
    // Write the review into the reviews table...
    JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_reviews/tables');
    
    $table = JTable::getInstance('Review', 'ReviewTable');
    
    if (!$table) {
  		JError::raiseWarning(403, JText::_('COM_REVIEWS_REVIEW_TABLE_NOT_FOUND'));

    	// Save the data in the session.
			$app->setUserState('com_reviews.review.data', $data);

			// Redirect back to the contact form.
			$this->setRedirect(JRoute::_('index.php?option=com_reviews&view=reviews&Itemid=167&id='.$stub, false));
     	return false;    
    }
    
    // Set propertyID to same as ID
    $data['property_id'] = $data['id'];
    
    // And unset id incase it gets bound somehow...
    unset($data['id']);
    
    // Check that we can save the data.
    if(!$table->save($data)){
      
      $errors	= $table->getErrors();
      
			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'error');
				} else {
					$app->enqueueMessage($errors[$i], 'error');
				}
			}
      
      // Save the data in the session.
			$app->setUserState('com_reviews.review.data', $data);  	
      $this->setRedirect(JRoute::_('index.php?option=com_reviews&view=reviews&Itemid=167&id='.$stub, false));
  
      return false;
    }
            
		// Send the email
		$sent = false;
		
		$sent = $this->_sendEmail($data, $params, $property);
		

		// Set the success message if it was a success
		if (!($sent instanceof Exception)) {
			$msg = JText::_('COM_REVIEWS_EMAIL_THANKS');
		} else {
			$msg = '';
		}

		// Flush the data from the session
		$app->setUserState('com_reviews.review.data', null);

		// Redirect if it is set in the parameters, otherwise redirect back to where we came from
		if ($params->get('redirect')) {
			$this->setRedirect($params->get('redirect'), $msg);
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_reviews&view=reviews&Itemid=167&id='.$stub, false), $msg,'success');
		}

		return true;
	}

	private function _sendEmail($data, $params, $property)
	{
			$app		= JFactory::getApplication();
			$params 	= JComponentHelper::getParams('com_reviews');
      
      // If there is a valid user for this property then get the email address
			if ($property->created_by != 0) {
				$property_user = JUser::getInstance($property->created_by);
				$property->email = $property_user->get('email');
        $property->name = $property_user->get('name');
			}
          
      $mailfrom	= $app->getCfg('mailfrom');
			$fromname	= $app->getCfg('fromname');
			$sitename	= $app->getCfg('sitename');

			$name		= $data['guest_name'];
			$email		= $data['guest_email'];
			$subject	= $data['title'];
			$body		= $data['review_text'];

			// Prepare email body
			$prefix = JText::sprintf('COM_REVIEWS_SUBMISSION_TEXT', $property->title, JURI::base());
			$body	= $prefix."\n".$name.' <'.$email.'>'."\r\n\r\n".stripslashes($subject)."\r\n\r\n".stripslashes($body);

			$mail = JFactory::getMailer();
			$mail->addRecipient($property->email, $property->name);
			$mail->addReplyTo(array($mailfrom, $fromname));
			$mail->setSender(array($mailfrom, $fromname));
      $mail->addBCC($mailfrom, $fromname);
			$mail->setSubject($sitename.': '.JText::sprintf('COM_REVIEWS_NEW_REVIEW_SUBMITTED',$property->title));
			$mail->setBody($body);
			$sent = $mail->Send();

			return $sent;
	}
}
