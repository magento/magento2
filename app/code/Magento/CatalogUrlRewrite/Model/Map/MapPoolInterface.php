<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Catalog\Model\Category;

/**
 * Interface for client map pool
 */
interface MapPoolInterface
{
    /**
     * Gets map instance identified by category id
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return MapInterface
     */
    public function getMap($instanceName, $categoryId);
}
