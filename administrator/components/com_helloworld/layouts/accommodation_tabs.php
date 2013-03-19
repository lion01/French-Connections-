<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Need to take the data from the object passed into the template...
$data = $displayData;

$app = JFactory::getApplication();

// Get the input data
$input = $app->input;

// Get the user object
$user = JFactory::getUser();

// Get the option
$option = $input->get('option', '', 'string');

// Get the view
$view = $input->get('view', '', 'string');

// Retrieve the listing details from the session
$listing = $data;

// Convert the listing detail to an array for easier processing
$listing_details = $listing->getProperties();

// Get the id of the item the user is editing
$id = $input->get('id', '', 'int');

// Get properties needs to be passed false to get non-public properties...
$units = $listing->units;

$property_details = ($listing_details['id'] && $listing_details['latitude'] && $listing_details['longitude'] && $listing_details['title']) ? true : false;

// Assign a 'default' unit ID 
$units = (!$units) ? array() : $units;
$default_unit = (count($units) > 0) ? key($units) : '';

?>

<?php if ($property_details) : ?>
  <div class="alert alert-info">
    <?php echo JText::_('COM_HELLOWORLD_PLEASE_COMPLETE_ACCOMMODATION_DETAILS'); ?>  
  </div>
<?php elseif (($property_details && $view == 'unit' && !empty($units))) : ?>
  <div class="alert alert-info">
    <?php echo JText::_('COM_HELLOWORLD_PLEASE_COMPLETE_IMAGE_DETAILS'); ?>  
  </div>
<?php endif; ?>


<?php if (count($units) > 1) : ?>
  <div class="clearfix">
    <p>You have the following units:&nbsp;</p>
    <div class="btn-group">
      <a class="btn dropdown-toggle btn-small" data-toggle="dropdown" href="#">
        - Please choose unit to edit -
        <span class="caret"></span>
      </a>
      <ul class="dropdown-menu">
        <?php foreach ($listing_details['units'] as $value) : ?>
          <li>
            <a href="<?php echo JText::_('index.php?option=com_helloworld&task=unit.edit&id=' . $value->id) ?>">
              <?php echo $value->unit_title; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<hr />
<?php endif; ?>
<ul class="nav nav-tabs">
  <li <?php echo ($view == 'property') ? 'class=\'active\'' : '' ?>>
    <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=property.edit&id=' . (int) $listing_details['id']) ?>">
      <?php echo Jtext::_('COM_HELLOWORLD_HELLOWORLD_LISTING_DETAILS') ?>
      <?php if ($property_details) : ?>
        <i class="icon icon-ok"></i>
      <?php else: ?>
        <i class="icon icon-warning"></i>
      <?php endif; ?>
    </a>
  </li>
  <li <?php echo ($view == 'unit') ? 'class=\'active\'' : '' ?>>
    <?php if (!empty($units)) : // This listing has one or more units already    ?> 
      <?php if (count($units) == 1) : // There is only one unit for this listing...so far...  ?>
        <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=unit.edit&id=' . $units[$default_unit]->id) ?>">
          <?php echo JText::_($units[$default_unit]->unit_title); ?>
          <i class='icon icon-ok'></i>
        </a>
      <?php elseif (count($units) > 1) : ?>
        <?php if (array_key_exists($id, $units)) : ?>
          <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=unit.edit&id=' . $id) ?>">
            <?php echo JText::_($units[$id]->unit_title); ?>
            <i class='icon icon-ok'></i>
          </a>
        <?php else: ?>
          <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=unit.edit&id=' . $units[$default_unit]->id) ?>">
            <?php echo JText::_($units[$default_unit]->unit_title); ?>
            <i class='icon icon-ok'></i>
          </a>     
        <?php endif; ?>
      <?php endif; ?>
    <?php elseif (empty($id) && $view == 'unit') : // View is unit, ID is empty, must be a new unit  ?>
      <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=unit.edit') ?>">
        <?php echo JText::_('Accommodation'); ?>
        <i class='icon icon-warning'></i>
      </a>     
    <?php elseif ($property_details) : // No units supplied, property details complete  ?>
      <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=unit.edit') ?>">
        <?php echo JText::_('Accommodation'); ?>
        <i class='icon icon-warning'></i>
      </a>
    <?php else: // Brand new property  ?>
      <span class="muted">
        <?php echo JText::_('Accommodation'); ?>
      </span>
    <?php endif; ?>
  </li>
  <li>
    <?php if (!empty($units)) : ?>
      <?php if (count($units) > 0) : ?>
        <?php if (array_key_exists($id, $units)) : ?>
          <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=images.edit&id=' . $id) ?>">
            <?php echo JText::_('IMAGE_GALLERY'); ?>
            <?php echo ($units[$id]->images) ? '<i class=\'icon icon-ok\'></i>' : '<i class=\'icon icon-warning\'></i>';     ?>
          </a>
        <?php else: ?>
          <a href="<?php echo JRoute::_('index.php?option=com_helloworld&view=images&id=' . $units[$default_unit]->id) ?>">
            <?php echo JText::_('IMAGE_GALLERY'); ?>
            <?php echo ($units[$default_unit]->images) ? '<i class=\'icon icon-ok\'></i>' : '<i class=\'icon icon-warning\'></i>';     ?>
          </a>     
        <?php endif; ?>
      <?php endif; ?> 
    <?php else: // There are no units so we don't want the user editing this tab ?>
      <span class="muted">
        <?php echo JText::_('IMAGE_GALLERY'); ?>
      </span>
    <?php endif; ?>
  </li>
  <li>
    <?php if (!empty($units)) : ?>
      <?php if (count($units) > 0) : ?>
        <?php if (array_key_exists($id, $units)) : ?>
          <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=availability.edit&id=' . $id) ?>">
            <?php echo JText::_('Availability'); ?>
            <?php echo ($units[$id]->availability) ? '<i class=\'icon icon-ok\'></i>' : '<i class=\'icon icon-warning\'></i>';     ?>
          </a>
        <?php else: ?>
          <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=availability.edit&id=' . $units[$default_unit]->id) ?>">
            <?php echo JText::_('Availability'); ?>
            <?php echo ($units[$default_unit]->images) ? '<i class=\'icon icon-ok\'></i>' : '<i class=\'icon icon-warning\'></i>';     ?>
          </a>     
        <?php endif; ?>
      <?php endif; ?> 
    <?php else: // There are no units so we don't want the user editing this tab ?>
      <span class="muted">
        <?php echo JText::_('Availability'); ?>
      </span>
    <?php endif; ?>
  </li>
  <li>
    <?php if (!empty($units)) : ?>
      <?php if (count($units) > 0) : ?>
        <?php if (array_key_exists($id, $units)) : ?>
          <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=tariffs.edit&id=' . $id) ?>">
            <?php echo JText::_('Tariffs'); ?>
            <?php echo ($units[$id]->availability) ? '<i class=\'icon icon-ok\'></i>' : '<i class=\'icon icon-warning\'></i>';     ?>
          </a>
        <?php else: ?>
          <a href="<?php echo JRoute::_('index.php?option=com_helloworld&task=tariffs.edit&id=' . $units[$default_unit]->id) ?>">
            <?php echo JText::_('Tariffs'); ?>
            <?php echo ($units[$default_unit]->tariffs) ? '<i class=\'icon icon-ok\'></i>' : '<i class=\'icon icon-warning\'></i>';     ?>
          </a>     
        <?php endif; ?>
      <?php endif; ?> 
    <?php else: // There are no units so we don't want the user editing this tab ?>
      <span class="muted">
        <?php echo JText::_('Tariffs'); ?>
      </span>
    <?php endif; ?>
  </li>
</ul>