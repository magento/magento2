<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\ObjectManagerInterface;

/**
 * Pool for hash maps
 * @since 2.2.0
 */
class HashMapPool
{
    /**
     * @var HashMapInterface[]
     * @since 2.2.0
     */
    private $dataArray = [];

    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.2.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Gets a map by instance and category Id
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return HashMapInterface
     * @throws \Exception
     * @since 2.2.0
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
     * Resets data in a hash map by instance name and category Id
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return void
     * @since 2.2.0
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
