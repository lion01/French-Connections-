<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$document = JFactory::getDocument();
$direction = $document->direction == 'rtl' ? 'pull-right' : '';
require JModuleHelper::getLayoutPath('mod_menu', 'default_enabled');

$menu->renderMenu('menu', 'nav ' . $direction);
