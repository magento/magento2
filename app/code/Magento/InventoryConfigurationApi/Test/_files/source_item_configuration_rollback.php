<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryConfigurationApi\Api\DeleteSourceItemConfigurationInterface;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var  DeleteSourceItemConfigurationInterface $configurationDelete */
$configurationDelete = Bootstrap::getObjectManager()->get(DeleteSourceItemConfigurationInterface::class);

$configurationDelete->execute(10, 'SKU-1');
