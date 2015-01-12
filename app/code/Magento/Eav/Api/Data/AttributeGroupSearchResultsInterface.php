<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

interface AttributeGroupSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attribute sets list.
     *
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface[]
     */
    public function getItems();
}
