<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeSetSearchResultsInterface
 * @api
 * @since 2.0.0
 */
interface AttributeSetSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attribute sets list.
     *
     * @return \Magento\Eav\Api\Data\AttributeSetInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set attribute sets list.
     *
     * @param \Magento\Eav\Api\Data\AttributeSetInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
