<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Api\Data;

/**
 * Gateway vault payment token search result interface.
 *
 * @api
 * @since 2.1.0
 */
interface PaymentTokenSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface[] Array of collection items.
     * @since 2.1.0
     */
    public function getItems();

    /**
     * Sets collection items.
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface[] $items
     * @return $this
     * @since 2.1.0
     */
    public function setItems(array $items);
}
