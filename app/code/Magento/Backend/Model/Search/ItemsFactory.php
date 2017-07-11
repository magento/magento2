<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Search;

use Magento\Backend\Api\Search\ItemsInterface;
use Magento\Framework\ObjectManagerInterface;

class ItemsFactory
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
     * @param $instanceName
     * @param array $data
     * @return ItemsInterface
     */
    public function create($instanceName, array $data = [])
    {
        $object =  $this->objectManager->create($instanceName, $data);
        if ($object instanceof ItemsInterface) {
            return $object;
        }
        throw new \LogicException(
            "The class '{$instanceName}' does not implement ".ItemsInterface::class
        );
    }
}
