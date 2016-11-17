<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

/**
 * Interface for a data map
 *
 */
interface DataMapInterface
{
    /**
     * Gets all data from a map identified by a category Id
     *
     * @param int $categoryId
     * @return array
     */
    public function getData($categoryId);

    /**
     * Resets current map and it's dependencies
     *
     * @param int $categoryId
     * @return $this
     */
    public function resetData($categoryId);
}
