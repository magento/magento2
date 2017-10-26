<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySalesApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents product aggregation among some different physical storages (in technical words, it is an index)
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
    const ID = 'id';
    const TYPE = 'type';
    const CODE = 'code';
    /**#@-*/

    const TYPE_WEBSITE = 'website';

    /**
     * Get sales channel id
     *
     * @return int|null
     */
    public function getSalesChannellId();

    /**
     * Set sales channel id
     *
     * @param int $salesChannelId
     * @return void
     */
    public function setSalesChannelId(int $salesChannelId);

    /**
     * Get sales channel type
     *
     * @return string|null
     */
    public function getType();

    /**
     * Set sales channel type
     *
     * @param string $type
     * @return void
     */
    public function setType(string $type);

    /**
     * Get sales channel code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Set sales channel code
     *
     * @param string $code
     * @return void
     */
    public function setCode(string $code);

}
