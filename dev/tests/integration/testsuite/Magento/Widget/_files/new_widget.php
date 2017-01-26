<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Widget\Model\ResourceModel\Widget\Instance $resourceModel */
$resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Widget\Model\ResourceModel\Widget\Instance::class);

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Widget\Model\Widget\Instance::class);

$model->setData(
    [
        'instance_type' => 'Magento\\Widget\\NewSampleWidget',
        'theme_id' => '4',
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