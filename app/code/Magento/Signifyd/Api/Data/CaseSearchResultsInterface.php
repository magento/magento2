<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Retrieve and set list of case entities.
 *
 * @api
 * @since 100.2.0
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
interface CaseSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Gets collection of case entities.
     *
     * @return \Magento\Signifyd\Api\Data\CaseInterface[]
     * @since 100.2.0
     */
    public function getItems();

    /**
     * Sets collection of case entities.
     *
     * @param \Magento\Signifyd\Api\Data\CaseInterface[] $items
     * @return $this
     * @since 100.2.0
     */
    public function setItems(array $items);
}
