<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents sales channels (which are a linkage between stocks and websites, customer groups, etc.)
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SalesChannelInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const TYPE = 'type';
    const CODE = 'code';
    /**#@-*/

    /**
     * Default sales channel type
     */
    const TYPE_WEBSITE = 'website';

    /**
     * Get sales channel type
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Set sales channel type
     *
     * @param string $type
     * @return void
     */
    public function setType(string $type): void;

    /**
     * Get sales channel code
     *
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * Set sales channel code
     *
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void;

    /**
     * Retrieve existing extension attributes object
     *
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventorySalesApi\Api\Data\SalesChannelExtensionInterface|null
     */
    public function getExtensionAttributes(): ?SalesChannelExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(SalesChannelExtensionInterface $extensionAttributes): void;
}
