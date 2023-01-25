<?php declare(strict_types=1);
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = new ObjectManager($this);
$bootstrapFactory = $objectManager->getObject(\Magento\Bootstrap\ModelFactory::class);
