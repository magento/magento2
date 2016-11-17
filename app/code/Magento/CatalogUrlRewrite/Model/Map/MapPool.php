<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Catalog\Model\Category;

/**
 * Class for client map pool
 */
class MapPool implements MapPoolInterface
{
    /**
     * @var MapInterface[]
     */
    private $mapStoragePool = [];

    /**
     * @var MapFactoryInterface
     */
    private $clientMapFactory;

    /**
     * @param MapFactoryInterface $clientMapFactory
     */
    public function __construct(
        MapFactoryInterface $clientMapFactory
    ) {
        $this->clientMapFactory = $clientMapFactory;
    }

    /**
     * Gets map instance identified by category id
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return MapInterface
     */
    public function getMap($instanceName, $categoryId)
    {
        $key = $instanceName . '-' . $categoryId;
        if (!isset($this->mapStoragePool[$key])) {
            $this->mapStoragePool[$key] = $this->clientMapFactory->create($instanceName, $categoryId);
        }
        return $this->mapStoragePool[$key];
    }
}
