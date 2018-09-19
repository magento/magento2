<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

/**
 * Interface for a mysql data type of a map
 *
 * Is used to get data by a unique key from a temporary table in mysql to prevent memory usage
 * It internally holds the knowledge the creation of the actual data and it initializes itself when we call getData
 * We should always call destroyTableAdapter when we don't need anymore the temporary tables
 */
interface DatabaseMapInterface
{
    /**
     * Gets data by key from a map identified by a category Id
     *
     * The key is a unique identifier that matches the values of the index used to build the temporary table
     *
     * Example "1_2" where ids would correspond to store_id entity_id
     *
     * @param int $categoryId
     * @param string $key
     * @return array
     */
    public function getData($categoryId, $key);

    /**
     * Destroys data in the temporary table by categoryId
     * It also destroys the data in other maps that are dependencies used to construct the data
     *
     * @param int $categoryId
     * @return void
     */
    public function destroyTableAdapter($categoryId);
}
