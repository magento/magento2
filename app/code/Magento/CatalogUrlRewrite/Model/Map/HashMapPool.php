<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\ObjectManagerInterface;

/**
 * Pool for hash maps.
 */
class HashMapPool
{
    /**
     * Array of hash data maps.
     *
     * @var HashMapInterface[]
     */
    private $dataArray = [];

    /**
     * Object manager.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Gets a map by instance and category Id.
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return HashMapInterface
     * @throws \Exception
     */
    public function getDataMap($instanceName, $categoryId)
    {
        $key = $instanceName . '-' . $categoryId;
        if (!isset($this->dataArray[$key])) {
            $instance = $this->objectManager->create(
                $instanceName,
                [
                    'category' => $categoryId
                ]
            );
            if (!$instance instanceof HashMapInterface) {
                throw new \InvalidArgumentException(
                    $instanceName . ' does not implement interface ' . HashMapInterface::class
                );
            }
            $this->dataArray[$key] = $instance;
        }

        return $this->dataArray[$key];
    }

    /**
     * Resets data in a hash map by instance name and category Id.
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return void
     */
    public function resetMap($instanceName, $categoryId)
    {
        $key = $instanceName . '-' . $categoryId;
        if (isset($this->dataArray[$key])) {
            $this->dataArray[$key]->resetData($categoryId);
            unset($this->dataArray[$key]);
        }
    }
}
