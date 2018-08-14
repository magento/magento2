<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Widget\Model\ResourceModel\Widget\Instance $resourceModel */
$resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Widget\Model\ResourceModel\Widget\Instance::class);

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Widget\Model\Widget\Instance::class);

// Set default theme as work ground for MAGETWO-63643
/** @var \Magento\Framework\View\Design\ThemeInterface $theme */
$theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Framework\View\Design\ThemeInterface::class
);
$theme->load('Magento/luma', 'theme_path');

$model->setData(
    [
        'instance_type' => 'Magento\\Widget\\NewSampleWidget',
        'theme_id' => $theme->getId(),
        'title' => 'New Sample widget title',
        'store_ids' => [
            0 => '0',
        ],
        'widget_parameters' => [
            'block_id' => '2',
        ],
        'sort_order' => '0',
        'page_groups' => [],
        'instance_code' => 'new_sample_widget',
    ]
);

$resourceModel->save($model);
