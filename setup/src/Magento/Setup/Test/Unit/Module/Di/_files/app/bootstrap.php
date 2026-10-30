<?php declare(strict_types=1);
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

$objectManager = new ObjectManager($this);
$bootstrapFactory = $objectManager->getObject(\Magento\Bootstrap\ModelFactory::class);
