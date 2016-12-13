<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for case search results
 *
 * @api
 */
interface CaseSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Signifyd\Api\Data\CaseInterface[]
     */
    public function getItems();

    /**
     * Sets collection items.
     *
     * @param \Magento\Signifyd\Api\Data\CaseInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
