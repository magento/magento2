<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestFixture;

interface WrongDocReturnInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    public function getExtensionAttributes(): WrongDocReturnExtensionInterface;
}
