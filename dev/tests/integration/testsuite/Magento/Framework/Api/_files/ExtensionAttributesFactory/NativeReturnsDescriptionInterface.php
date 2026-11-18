<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestFixture;

interface NativeReturnsDescriptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * The @returns wording describes what this getter returns.
     */
    public function getExtensionAttributes(): NativeReturnsDescriptionExtensionInterface;
}
