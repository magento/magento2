<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\ObjectManagerInterface;

/**
 * Pool for all data maps
 */
class DataMapPool implements DataMapPoolInterface
{
    /**
     * @var DataMapInterface[]
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function resetDataMap($instanceName, $categoryId)
    {
        $key = $instanceName . '-' . $categoryId;
        if (isset($this->dataArray[$key])) {
            $this->dataArray[$key]->resetData($categoryId);
            unset($this->dataArray[$key]);
        }
    }
}
