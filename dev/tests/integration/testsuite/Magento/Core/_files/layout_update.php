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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $objectManager \Magento\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->get('Magento\Core\Model\App')
    ->loadAreaPart(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE, \Magento\Core\Model\App\Area::PART_CONFIG);
/** @var $theme \Magento\View\Design\ThemeInterface */
$theme = $objectManager->create('Magento\View\Design\ThemeInterface');
$theme->setThemePath('test/test')
    ->setThemeVersion('2.0.0.0')
    ->setArea('frontend')
    ->setThemeTitle('Test Theme')
    ->setType(\Magento\View\Design\ThemeInterface::TYPE_VIRTUAL)
    ->save();

/** @var $updateNotTemporary \Magento\Core\Model\Layout\Update */
$updateNotTemporary = $objectManager->create('Magento\Core\Model\Layout\Update');
$updateNotTemporary->setHandle('test_handle')
    ->setXml('not_temporary')
    ->setStoreId(0)
    ->setThemeId($theme->getId())
    ->save();

/** @var $updateTemporary \Magento\Core\Model\Layout\Update */
$updateTemporary = $objectManager->create('Magento\Core\Model\Layout\Update');
$updateTemporary->setHandle('test_handle')
    ->setIsTemporary(1)
    ->setXml('temporary')
    ->setStoreId(0)
    ->setThemeId($theme->getId())
    ->save();
