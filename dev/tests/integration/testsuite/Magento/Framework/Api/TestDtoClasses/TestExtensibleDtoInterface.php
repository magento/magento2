<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TestExtensibleDtoInterface extends ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getParamOne(): string;

    /**
     * @return string
     */
    public function getParamTwo(): string;

    /**
     * @return TestExtensibleDtoExtensionInterface
     */
    public function getExtensionAttributes(): TestExtensibleDtoExtensionInterface;
}
