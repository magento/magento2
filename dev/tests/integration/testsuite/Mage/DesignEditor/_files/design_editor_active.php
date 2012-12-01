<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $session Mage_DesignEditor_Model_Session */
$session = Mage::getModel('Mage_DesignEditor_Model_Session');
/** @var $auth Mage_Backend_Model_Auth */
$auth = Mage::getModel('Mage_Backend_Model_Auth');
$auth->setAuthStorage($session);
$auth->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
$session->activateDesignEditor();

/** @var $theme Mage_Core_Model_Theme */
$theme = Mage::getModel('Mage_Core_Model_Theme');
$theme->setData(array(
    'theme_code'           => 'blank',
    'area'                 => 'frontend',
    'parent_id'            => null,
    'theme_path'           => 'default/blank',
    'theme_version'        => '2.0.0.0',
    'theme_title'          => 'Default',
    'preview_image'        => 'media/preview_image.jpg',
    'magento_version_from' => '2.0.0.0-dev1',
    'magento_version_to'   => '*',
    'is_featured'          => '0'
));
$theme->save();
$session->setThemeId($theme->getId());
