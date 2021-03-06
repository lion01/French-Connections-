<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

$input = JFactory::getApplication()->input;

$layout = $input->get('layout', '', 'string');
$listing_id = $input->get('property_id', '', 'int');
$unit_id = $input->get('unit_id', '', 'int');

$property_status_icon = (!$this->units[0]->property_review) ? 'publish' : 'warning';
?>
<form name='adminForm' id='adminForm' action='index.php?option=com_helloworld' class='form-validate' method='post'>
  <ul class="nav nav-tabs">
    <li <?php echo ($layout == 'property') ? 'class="active"' : '' ?>>
      <a href="<?php echo JRoute::_('index.php?option=com_helloworld&view=listingreview&layout=property&property_id=' . (int) $listing_id) ?>">
        <i class="icon <?php echo 'icon-' . $property_status_icon ?>"></i>
        Property Detail
      </a>
    </li>
    <?php foreach ($this->units as $unit) : ?>
      <li <?php echo ($layout == 'unit' && $unit->unit_id == $unit_id) ? 'class="active"' : '' ?>>
        <?php $review = ($unit->unit_review == 1) ? 'warning' : 'publish'; ?>

        <a href="<?php echo JRoute::_('index.php?option=com_helloworld&view=listingreview&layout=unit&property_id=' . (int) $unit->property_id . '&unit_id=' . (int) $unit->unit_id) ?>">
          <?php if ($review) : ?>
            <i class="icon <?php echo 'icon-' . $review ?>"></i>
          <?php endif; ?>
          <?php echo $unit->unit_title; ?><br />
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="row-fluid">
    <?php if (!empty($this->sidebar)): ?>
      <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
      </div>
      <div id="" class="span10">
      <?php else : ?>
        <div class="span12">
        <?php endif; ?>
        <table class="table table-bordered table-hover table-striped">
          <thead>
          <th>Field</th>
          <th>Current</th>
          <th>Draft</th>
          </thead>
          <tbody>
            <?php foreach ($this->versions[$layout] as $version) : ?>
              <?php foreach ($version as $key => $value) : ?>
                <tr>
                  <td width="20%">
                    <?php echo $key; ?>
                    <?php if (count($this->versions[$layout][1])) : ?>
                      <?php if (strcmp(trim($this->versions[$layout][1][$key]), trim($this->versions[$layout][0][$key])) != 0) : ?>
                        <span class="label label-important">*</span>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                  <td width="40%">
                    <?php echo strip_tags($this->versions[$layout][0][$key]) ?>
                  </td>
                  <td width="40%">
                    <?php if (array_key_exists($key, $this->versions[$layout][1])) : ?>
                      <?php echo $this->versions[$layout][1][$key] ?>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (array_key_exists('images', $this->versions)) : ?>
          <table class="table table-bordered table-striped">
            <tr>
              <td width="20%">Images</td>
              <?php foreach ($this->versions[$layout] as $version) : ?>
                <?php if (array_key_exists($version['id'], $this->versions['images'])) : ?>
                  <td width="40%">
                    <?php foreach ($this->versions['images'][$version['id']] as $images) : ?>
                      <p>
                        <img src=<?php echo '/images/property/' . $unit_id . '/thumbs/' . $images['image_file_name']; ?> /> 
                        <span><?php echo $images['caption'] ?></span>
                      </p>
                    <?php endforeach; ?>
                  </td>
                <?php endif; ?> 
              <?php endforeach; ?>
            </tr>
          </table>         
        <?php endif; ?>

      </div>

    </div>
  </div>
  <input type='hidden' name='id' value='<?php echo (int) $listing_id ?>' />
  <input type='hidden' name='task' value='' />
  <?php echo JHtml::_('form.token'); ?>
</form>