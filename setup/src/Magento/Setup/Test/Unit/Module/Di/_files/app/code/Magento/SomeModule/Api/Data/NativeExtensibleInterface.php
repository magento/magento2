<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SomeModule\Api\Data;

interface NativeExtensibleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public function getExtensionAttributes(): ?\Magento\SomeModule\Api\Data\NativeExtensibleExtensionInterface;
}
