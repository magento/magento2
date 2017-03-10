<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer;

interface DataDifferenceInterface
{
    /**
     * @param array $newData
     * @return array
     */
    public function getItemsToDelete(array $newData);

    /**
     * @param array $newData
     * @return array
     */
    public function getItemsToCreate(array $newData);

    /**
     * @param array $newData
     * @return array
     */
    public function getItemsToUpdate(array $newData);
}