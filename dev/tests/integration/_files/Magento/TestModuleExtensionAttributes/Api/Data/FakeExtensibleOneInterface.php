<?php
/**
 *
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleExtensionAttributes\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Fake interface
 * to test exception if the method 'getExtensionAttributes' is not overridden
 */
interface FakeExtensibleOneInterface extends ExtensibleDataInterface
{
}
