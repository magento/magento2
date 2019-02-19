<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $objectManager \Magento\Framework\ObjectManagerInterface */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->get(\Magento\Framework\App\AreaList::class)
    ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
    ->load(\Magento\Framework\App\Area::PART_CONFIG);
/** @var $theme \Magento\Framework\View\Design\ThemeInterface */
$theme = $objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class);
$theme->setThemePath(
    'test/test'
)->setArea(
    'frontend'
)->setThemeTitle(
    'Test Theme'
)->setType(
    \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
)->save();

/** @var $updateNotTemporary \Magento\Widget\Model\Layout\Update */
$updateNotTemporary = $objectManager->create(\Magento\Widget\Model\Layout\Update::class);
$updateNotTemporary->setHandle(
    'test_handle'
)->setXml(
    'not_temporary'
)->setStoreId(
    0
)->setThemeId(
    $theme->getId()
)->save();

/** @var $updateTemporary \Magento\Widget\Model\Layout\Update */
$updateTemporary = $objectManager->create(\Magento\Widget\Model\Layout\Update::class);
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
