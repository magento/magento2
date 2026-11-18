<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestFixture;

interface EmptyDocReturnInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return
     */
    public function getExtensionAttributes(): EmptyDocReturnExtensionInterface;
}
