<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeSearchResultsInterface
 * @api
 * @since 100.0.2
 */
interface AttributeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
