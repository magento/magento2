<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/**
 * @var $configWriter \Magento\Framework\App\Config\Storage\WriterInterface
 */
$configWriter = $objectManager->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);
$configWriter->save('carriers/freeshipping/active', 1);
