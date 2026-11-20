<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SomeModule\Api\Data;

interface DocumentedExtensibleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return \Magento\SomeModule\Api\Data\DocumentedExtensibleExtensionInterface|null
     */
    public function getExtensionAttributes();
}
