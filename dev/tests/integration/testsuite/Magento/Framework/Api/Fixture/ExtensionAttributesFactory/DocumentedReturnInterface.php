<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestFixture;

interface DocumentedReturnInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Retrieve extension attributes.
     *
     * @return \Magento\Framework\Api\TestFixture\DocumentedReturnExtensionInterface
     */
    public function getExtensionAttributes();
}
