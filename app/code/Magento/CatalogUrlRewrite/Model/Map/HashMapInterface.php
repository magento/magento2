<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use \Magento\Framework\DB\Select;

/**
 * Interface for a hash data map
 * It is used for classes tht would build hash maps and store them into memory
 * The initialization is done transparently whenever getAllData or getData is called
 * The map, upon initialization, might have a dependency on some other DataMapInterfaces
 * The map has to free memory after we're done using it
 * We need to destroy those maps too when calling resetData
 */
interface HashMapInterface
{
    /**
     * Gets all data from a map identified by a category Id
     *
     * @param int $categoryId
     * @return array
     */
    public function getAllData($categoryId);

    /**
     * Gets data by criteria from a map identified by a category Id
     *
     * @param int $categoryId
     * @param string $key
     * @return array
     */
    public function getData($categoryId, $key);

    /**
     * Resets current map by freeing memory and also to its dependencies
     *
     * @param int $categoryId
     * @return void
     */
    public function resetData($categoryId);
}
