<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

/**
 * Interface for factory that creates a client map
 */
interface MapFactoryInterface
{
    /**
     * Creates a client map
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return MapInterface
     */
    public function create($instanceName, $categoryId);
}
