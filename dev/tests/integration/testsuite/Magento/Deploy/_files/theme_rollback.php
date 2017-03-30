<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Theme\Model\Theme $theme */
$theme = $objectManager->create(\Magento\Theme\Model\Theme::class);
$theme->load('Magento/zoom1', 'theme_path');
$theme->delete();

$theme = $objectManager->create(\Magento\Theme\Model\Theme::class);
$theme->load('Magento/zoom2', 'theme_path');
$theme->delete();

$theme = $objectManager->create(\Magento\Theme\Model\Theme::class);
$theme->load('Magento/zoom3', 'theme_path');
$theme->delete();
