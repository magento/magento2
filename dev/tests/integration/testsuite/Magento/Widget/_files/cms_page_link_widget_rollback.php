<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Widget\Model\Widget\InstanceFactory;
use Magento\Widget\Model\Widget\Instance;

$objectManager = Bootstrap::getObjectManager();

/** @var InstanceFactory $widgetModelFactory */
$widgetModelFactory = $objectManager->get(InstanceFactory::class);
/** @var Instance $widgetModel */
$widgetModel = $widgetModelFactory->create();
$widgetModel->load('Test Widget', 'title');

if ($widgetModel->getId()) {
    $widgetModel->delete();
}
