<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access'); 
?>
<table class="table table-striped">
  <thead>
    <th><?php echo JText::_('COM_ACCOMMODATION_PERIOD'); ?></th>
    <th><?php echo JText::_('COM_ACCOMMODATION_TARIFFS'); ?></th>
  </thead>
  <?php foreach ($this->tariffs as $tariff) : ?>
  <tr>
    <td>
      <?php echo htmlspecialchars($tariff[0]); ?>
      &ndash;
      <?php echo htmlspecialchars($tariff[1]); ?>
    </td> 
    <td>
      <?php echo htmlspecialchars($tariff[2]); ?>
      <?php echo htmlspecialchars($this->item->base_currency); ?>
      <?php echo htmlspecialchars($this->item->tariffs_based_on); ?>  
    </td>
  </tr>
<?php endforeach; ?>
</table>