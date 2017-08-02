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
 * @since 2.0.0
 */
interface AttributeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
