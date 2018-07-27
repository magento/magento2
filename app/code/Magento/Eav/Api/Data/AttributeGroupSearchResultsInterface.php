<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeGroupSearchResultsInterface
 * @api
 */
interface AttributeGroupSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attribute sets list.
     *
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface[]
     */
    public function getItems();

    /**
     * Set attribute sets list.
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
