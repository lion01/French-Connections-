<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');

$listDirn = $this->escape($this->state->get('list.direction'));
$listOrder = $this->escape($this->state->get('list.ordering'));
$user = JFactory::getUser();
$userId = $user->get('id');
$saveOrder = $listOrder == 'r.ordering';
$disableClassName = '';
$disabledLabel = '';

// Check relevant permissions for this user
$canChangeState = $user->authorise('core.edit.state', 'com_specialoffers');
$canEditOwn = $user->authorise('core.edit.own', 'com_specialoffers');
$canEdit = $user->authorise('core.edit', 'com_specialoffers');
?>
<form action="<?php echo JRoute::_('index.php?option=com_specialoffers'); ?>" method="post" name="adminForm" id="adminForm">

  <?php if (!empty($this->sidebar)): ?>
    <div id="j-sidebar-container" class="span2">
      <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
      <div id="j-main-container">
      <?php endif; ?>
      <div id="filter-bar" class="btn-toolbar">
        <div class="filter-search btn-group pull-left">
          <label class="element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
          <input type="text" name="filter_search" 
                 id="filter_search" 
                 value="<?php echo $this->escape($this->state->get('filter.search')); ?>" 
                 title="<?php echo JText::_('COM_SPECIALOFFERS_ITEMS_SEARCH_FILTER'); ?>" 
                 placeholder="<?php echo JText::_('COM_SPECIALOFFERS_ITEMS_SEARCH_FILTER'); ?>" />        
        </div>
        <div class="btn-group pull-left hidden-phone">
          <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
          <button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value = '';
              this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
        </div>
        <div class="btn-group pull-right hidden-phone">
          <label for="limit" class="element-invisible">
            <?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
          </label>
          <?php echo $this->pagination->getLimitBox(); ?>
        </div>	
      </div>
      <?php if (count($this->items) == 0) : ?>


        <div class="alert alert-notice">
          <?php echo JText::_('COM_SPECIALOFFERS_NO_OFFERS_FOUND'); ?>
        </div>

      <?php else: ?>
        <table class="table table-striped" id="articleList">
          <thead>
            <tr>
              <th width="1%" class="hidden-phone">
                <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
              </th>
              <?php if ($canChangeState) : // If user can change state just show them un/publish buttons (e.g. admin)  ?>

                <th width="10%">
                  <?php echo JText::_('JPUBLISHED'); ?>
                </th>    
              <?php endif; ?>
              <th>
                <?php echo JText::_('JSTATUS'); ?>
              </th>
              <th>
                <?php echo JHtml::_('grid.sort', 'COM_SPECIALOFFERS_OFFER_TITLE', 'so.title', $listDirn, $listOrder); ?>

              </th>
              <th width="10%">
                <?php echo JHtml::_('grid.sort', 'COM_SPECIALOFFERS_PROPERTY_ID', 'c.unit_title', $listDirn, $listOrder); ?>
              </th>
              <th>
                <?php echo JHtml::_('grid.sort', 'COM_SPECIALOFFERS_OFFER_DATE_CREATED', 'a.date_created', $listDirn, $listOrder); ?>
              </th>
              <th width="10%">
                <?php echo JHtml::_('grid.sort', 'COM_SPECIALOFFERS_OFFER_START_DATE', 'a.start_date', $listDirn, $listOrder); ?>

              </th>
              <th width="10%">
                <?php echo JText::_('COM_SPECIALOFFERS_OFFER_END_DATE'); ?>
              </th>
              <th width="1%">
                <?php echo JText::_('JGRID_HEADING_ID'); ?>
              </th>
            </tr>		
          </thead> 
          <tbody>

            <?php foreach ($this->items as $i => $item): ?>
              <tr class="row<?php echo $i % 2; ?>" sortable-group-id>
                <td class="hidden-phone">
                  <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <?php if ($canChangeState) : // If user can change state just show them un/publish buttons (e.g. admin)  ?>
                  <td>
                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'specialoffers.', $canChangeState, 'cb'); ?>
                  </td>
                <?php endif; ?>

                <td>
                  <?php if ($item->published && strtotime($item->start_date) < time() && strtotime($item->end_date) > time()) : // Offer is current?>
                    <?php echo JText::_('COM_SPECIALOFFERS_OFFER_STATUS_ACTIVE'); ?>
                  <?php elseif ($item->published && strtotime($item->end_date) < time()) : // Offer is published but expired?>  
                    <?php echo JText::_('COM_SPECIALOFFERS_OFFER_STATUS_EXPIRED'); ?>
                  <?php elseif ($item->published && strtotime($item->start_date) > time()) : // Offer is published but scheduled for future date ?>  
                    <?php echo JText::_('COM_SPECIALOFFERS_OFFER_STATUS_SCHEDULED'); ?>
                  <?php elseif (!$item->published) : // Offer is awaiting moderation ?>
                    <?php echo JText::_('COM_SPECIALOFFERS_OFFER_STATUS_AWAITING_APPROVAL'); ?>
                  <?php endif; ?>
                </td>                
                <td>
                  <?php if ($canEdit) : ?>
                    <a href="<?php echo JRoute::_('index.php?option=com_specialoffers&task=specialoffer.edit&id=' . (int) $item->id); ?>">
                      <strong><?php echo JHtml::_('string.truncate', $this->escape(strip_tags($item->title)), 150); ?></strong>
                    </a><br />
                    <span class="small">
                      <?php echo $this->escape($item->description); ?>
                    </span>
                  <?php else: ?>
                    <strong>
                      <?php echo JHtml::_('string.truncate', $this->escape(strip_tags($item->title)), 150); ?>
                    </strong>
                    <br />
                    <span class="small">
                      <?php echo $this->escape($item->description); ?>
                    </span> 
                  <?php endif; ?>
                </td>              
                <td width="15%">
                  <?php echo $this->escape($item->unit_title); ?> <span class="small">(<?php echo $item->listing_id; ?>)</span>
                </td>

                <td width="10%">
                  <?php echo $item->date_created; ?>
                </td>
                <td width="10%">
                  <?php echo $item->start_date; ?>
                </td>
                <td width="10%">
                  <?php echo $item->end_date; ?>
                </td>




                <td>
                  <?php echo $item->id; ?>
                </td>
              </tr>					
            <?php endforeach; ?>
          <input type="hidden" name="extension" value="<?php echo 'com_reviews'; ?>" />
          </tbody>
        </table>   
        <?php echo $this->pagination->getListFooter(); ?>

      <?php endif; ?>


      <input type="hidden" name="task" value="" />
      <input type="hidden" name="boxchecked" value="0" />
      <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

      <?php echo JHtml::_('form.token'); ?>

    </div>
</form>