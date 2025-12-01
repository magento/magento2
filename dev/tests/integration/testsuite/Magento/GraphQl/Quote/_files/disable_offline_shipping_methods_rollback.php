<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
/**
 * @deprecated use next @magentoConfigFixture instead.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Writer  $configWriter */
$configWriter = $objectManager->create(WriterInterface::class);

$configWriter->delete('carriers/flatrate/active');
$configWriter->delete('carriers/tablerate/active');
$configWriter->delete('carriers/freeshipping/active');
