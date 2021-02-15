<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\View\Design\ThemeInterfaceFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Widget\Model\Widget\InstanceFactory;
use Magento\Widget\Model\Widget\Instance;

$objectManager = Bootstrap::getObjectManager();
/** @var InstanceFactory $widgetModelFactory */
$widgetModelFactory = $objectManager->get(InstanceFactory::class);
/** @var Instance $widgetModel */
$widgetModel = $widgetModelFactory->create();

/** @var ThemeInterfaceFactory $themeFactory */
$themeFactory = $objectManager->get(ThemeInterfaceFactory::class);
/** @var ThemeInterface $theme */
$theme = $themeFactory->create();
$theme->load('Magento/luma', 'theme_path');

$widgetModel->setData(
    [
        'instance_type' => \Magento\Cms\Block\Widget\Page\Link::class,
        'theme_id' => $theme->getId(),
        'title' => 'Test Widget',
        'store_ids' => [
            0 => '0',
        ],
        'widget_parameters' => [
            'block_id' => '2',
        ],
        'sort_order' => '0',
        'page_groups' => [],
        'instance_code' => 'cms_page_link',
    ]
);

$widgetModel->save();
