<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory that creates a client map
 */
class MapFactory implements MapFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Builder constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates a client map
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return MapInterface
     */
    public function create($instanceName, $categoryId)
    {
        return $this->objectManager->create(
            $instanceName,
            ['categoryId' => $categoryId]
        );
    }
}
