<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

/**
 * Interface for data map pool
 */
interface DataMapPoolInterface
{
    /**
     * Gets a map by instance and category Id
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return DataMapInterface
     */
    public function getDataMap($instanceName, $categoryId);

    /**
     * Resets a map by instance and category Id
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return $this
     */
    public function resetDataMap($instanceName, $categoryId);
}
