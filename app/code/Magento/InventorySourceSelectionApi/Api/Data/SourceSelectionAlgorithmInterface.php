<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Data Interface representing particular Source Selection Algorithm
 *
 * @api
 */
interface SourceSelectionAlgorithmInterface extends ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * Retrieve existing extension attributes object
     *
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionAlgorithmExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionAlgorithmExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(SourceSelectionAlgorithmExtensionInterface $extensionAttributes);
}
