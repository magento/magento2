<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer;

/**
 * Calculates difference between current config data and new data for import.
 */
interface DataDifferenceInterface
{
    /**
     * Retrieves items to delete.
     *
     * @param array $newData
     * @return array
     */
    public function getItemsToDelete(array $newData);

    /**
     * Retrieves items to create.
     *
     * @param array $newData
     * @return array
     */
    public function getItemsToCreate(array $newData);

    /**
     * Retrieves items to update.
     *
     * @param array $newData
     * @return array
     */
    public function getItemsToUpdate(array $newData);
}
