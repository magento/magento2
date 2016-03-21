<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Api\Data;

/**
 * Gateway vault payment token search result interface.
 *
 * @api
 */
interface PaymentTokenSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Sets collection items.
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
