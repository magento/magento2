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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $objectManager \Magento\Framework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->get('Magento\Framework\App\AreaList')
    ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
    ->load(\Magento\Framework\App\Area::PART_CONFIG);
/** @var $theme \Magento\Framework\View\Design\ThemeInterface */
$theme = $objectManager->create('Magento\Framework\View\Design\ThemeInterface');
$theme->setThemePath(
    'test/test'
)->setThemeVersion(
    '0.1.0'
)->setArea(
    'frontend'
)->setThemeTitle(
    'Test Theme'
)->setType(
    \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
)->save();

/** @var $updateNotTemporary \Magento\Core\Model\Layout\Update */
$updateNotTemporary = $objectManager->create('Magento\Core\Model\Layout\Update');
$updateNotTemporary->setHandle(
    'test_handle'
)->setXml(
    'not_temporary'
)->setStoreId(
    0
)->setThemeId(
    $theme->getId()
)->save();

/** @var $updateTemporary \Magento\Core\Model\Layout\Update */
$updateTemporary = $objectManager->create('Magento\Core\Model\Layout\Update');
$updateTemporary->setHandle(
    'test_handle'
)->setIsTemporary(
    1
)->setXml(
    'temporary'
)->setStoreId(
    0
)->setThemeId(
    $theme->getId()
)->save();
