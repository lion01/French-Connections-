<?php
/**
 * @version     1.0.0
 * @package     com_invoices
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Adam Rifat <adam@littledonkey.net> - http://
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
?>

<form action="<?php echo JRoute::_('index.php?option=com_invoices'); ?>" method="post" name="adminForm" id="adminForm">
  <?php if (!empty($this->sidebar)): ?>
    <div id="j-sidebar-container" class="span2">
      <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
      <div id="j-main-container">
      <?php endif; ?>
      <p class="bottom">
        <img alt="French Connections Logo" src="/images/general/logo-3.png">
      </p>
      <p class="small">
        VAT Reg. Number: GB 801 2299 61<br>Company Registration Number: 3216862
      </p>
      <p class="large">
        <strong>
          INVOICE <?php echo $this->escape($this->id); ?>
        </strong>
      </p>
      <hr />
      <p><strong>Invoice to:</strong></p>
      <p>
        <?php echo $this->escape($this->items[0]->first_name . ' ' . $this->items[0]->surname); ?><br />
        <?php echo $this->escape($this->items[0]->address); ?><br />
        <?php echo $this->escape($this->items[0]->town); ?><br />
        <?php echo $this->escape($this->items[0]->county); ?><br />
        <?php echo $this->escape($this->items[0]->postcode); ?><br />
      </p>
      <p>
        Invoice No
        <strong>
          <?php echo $this->escape($this->items[0]->id) ?>
        </strong>
      </p>

      <?php if (!empty($this->items[0]->due_date)): ?>
        <p>For Advertising on Internet site French Connections for 1 year commencing <?php echo $this->items[0]->due_date; ?>reference: <strong><?php echo $this->escape($this->items[0]->property_id) ?></strong></p>
      <?php else: ?>
        <p>Sundries reference: <strong><?php echo $this->escape($this->items[0]->property_id) ?></strong></p>
      <?php endif; ?>
      <p>
        Date issued
        <strong>
          <?php echo $this->escape($this->items[0]->date_created) ?>
        </strong>
      </p>

      <table class="table table-striped" id="invoiceList">
        <thead>
          <tr>
            <th>Qty</th>
            <th>Description</th>
            <th align="right">Unit cost</th>
            <th align="right">Total(GBP)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->items as $i => $item) : ?>
            <tr>
              <td><?php echo $this->escape($item->quantity) ?></td>
              <td><?php echo $this->escape($item->item_description) ?></td>
              <td text-align="right"><?php echo $this->escape($item->line_value) ?></td>
              <td text-align="right"><?php echo number_format($this->escape($item->line_value * $item->quantity),2) ?></td>
            </tr>

          <?php endforeach; ?>

          <tr>
            <td colspan="3">
              <strong>VAT</strong>
            </td>
            <td><?php echo $this->escape($item->vat) ?></td>
          </tr>
          <tr>
            <td colspan="3">&nbsp;</td>
            <td align="right">
              <strong>
                <?php echo number_format($this->escape($this->items[0]->total_net) + $this->escape($this->items[0]->vat),2) ?>
              </strong>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4"></td>
          </tr>
        </tfoot>
      </table>

    </div>

    <?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="task" value="" />

</form>



