<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$language = JFactory::getLanguage();
$language->load('plg_user_profile_fc', JPATH_ADMINISTRATOR, 'en-GB', true);

$total = '';
$total_vat = '';
?>
<div class="row-fluid">
  <?php if (!empty($this->sidebar)): ?>
    <div id="j-sidebar-container" class="span2">
      <?php echo $this->sidebar; ?>
    </div>
    <div id="" class="span8">
    <?php else : ?>
      <div lass="span10">
      <?php endif; ?>
      <h2>
        <?php echo JText::_('COM_HELLOWORLD_HELLOWORLD_RENEWAL_PAYMENT_SUMMARY_TITLE'); ?>
      </h2>
      <p>
        <?php echo JText::_('COM_HELLOWORLD_HELLOWORLD_RENEWAL_PAYMENT_SUMMARY_BLURB'); ?>
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
          <?php foreach ($this->summary as $i => $item) : ?>
            <?php $total = $total + $item->line_value; ?>
            <?php $total_vat = $total_vat + $item->vat; ?>
            <tr>
              <td><?php echo $this->escape($item->quantity) ?></td>
              <td><?php echo $this->escape($item->item_description) ?></td>
              <td text-align="right"><?php echo number_format($this->escape($item->cost), 2) ?></td>
              <td text-align="right"><?php echo number_format($this->escape($item->line_value), 2) ?></td>
            </tr>

          <?php endforeach; ?>

          <tr>
            <td colspan="3">
              <strong>VAT</strong>
            </td>
            <td><?php echo number_format($total_vat, 2) ?></td>
          </tr>
          <tr>
            <td colspan="3">&nbsp;</td>
            <td align="right">
              <strong>
                <?php echo number_format($total + $total_vat, 2) ?>
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
      <a href="<?php echo JRoute::_('index.php?option=com_helloworld&view=renewal&layout=payment&id=' . (int) $this->id) ?>" class="btn btn-primary btn-large">
        Pay now using our secure sever
        <i class="icon icon-white icon-next"> </i>
      </a>
      <hr />
      <?php echo JText::_('COM_HELLOWORLD_HELLOWORLD_RENEWAL_PAYMENT_SUMMARY_HELP'); ?>

      <p>
        <img src="/images/general/sage_pay_logo.gif" alt="Sage pay logo" />
        <img src="/images/general/mcsc_logo.gif" alt="Sage pay logo" />
        <img src="/images/general/vbv_logo_small.gif" alt="Sage pay logo" />
      </p>
    </div>

    <div class="span2">

    </div>
