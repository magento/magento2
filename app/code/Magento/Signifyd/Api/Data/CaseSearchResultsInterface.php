<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Retrieve and set list of case entities.
 *
 * @api
 */
interface CaseSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Gets collection of case entities.
     *
     * @return \Magento\Signifyd\Api\Data\CaseInterface[]
     */
    public function getItems();

    /**
     * Sets collection of case entities.
     *
     * @param \Magento\Signifyd\Api\Data\CaseInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
