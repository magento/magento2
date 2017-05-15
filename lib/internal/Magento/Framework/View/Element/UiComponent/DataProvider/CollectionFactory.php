<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CollectionPool
 */
class CollectionFactory
{
    /**
     * @var Collection[]
     */
    protected $collections;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManagerInterface
     * @param array $collections
     */
    public function __construct(
        ObjectManagerInterface $objectManagerInterface,
        array $collections = []
    ) {
        $this->collections = $collections;
        $this->objectManager = $objectManagerInterface;
    }

    /**
     * Get report collection
     *
     * @param string $requestName
     * @return Collection
     * @throws \Exception
     */
    public function getReport($requestName)
    {
        if (!isset($this->collections[$requestName])) {
            throw new \Exception(sprintf('Not registered handle %s', $requestName));
        }
        $collection = $this->objectManager->create($this->collections[$requestName]);
        if (!$collection instanceof Collection) {
            throw new \Exception(sprintf('%s is not of Collection type.', $requestName));
        }
        return $collection;
    }
}
