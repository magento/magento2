<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\ObjectManagerInterface;

/**
 * Pool for database maps
 */
class DatabaseMapPool
{
    /**
     * @var DatabaseMapInterface[]
     */
    private $dataArray = [];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
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
     * @return DatabaseMapInterface
     */
    public function getDataMap($instanceName, $categoryId)
    {
        $key = $instanceName . '-' . $categoryId;
        if (!isset($this->dataArray[$key])) {
            $this->dataArray[$key] = $this->objectManager->create(
                $instanceName,
                [
                    'category' => $categoryId
                ]
            );
        }
        return $this->dataArray[$key];
    }

    /**
     * Resets a database map by instance and category Id
     *
     * @param string $instanceName
     * @param int $categoryId
     * @return void
     */
    public function resetMap($instanceName, $categoryId)
    {
        $key = $instanceName . '-' . $categoryId;
        if (isset($this->dataArray[$key])) {
            $this->dataArray[$key]->destroyTableAdapter($categoryId);
            unset($this->dataArray[$key]);
        }
    }
}
