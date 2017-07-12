<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Search;

use Magento\Backend\Api\Search\ItemsInterface;
use Magento\Framework\ObjectManagerInterface;

class ItemFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new search items provider instance
     *
     * @param string $instanceName
     * @param array $data
     * @return ItemsInterface
     */
    public function create($instanceName, array $data = [])
    {
        $implements = class_implements($instanceName);
        if (!isset($implements[ItemsInterface::class])) {
            throw new \LogicException(
                "The class '{$instanceName}' does not implement " . ItemsInterface::class
            );
        }
        $object =  $this->objectManager->get($instanceName, $data);
        return $object;
    }
}
