<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Class SearchResultIteratorFactory
 */
class SearchResultIteratorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create SearchResultIterator object
     *
     * @param string $className
     * @param array $arguments
     * @return SearchResultIterator
     * @throws \Magento\Framework\Exception
     */
    public function create($className, array $arguments = [])
    {
        $resultIterator = $this->objectManager->create($className, $arguments);
        if (!$resultIterator instanceof \Traversable) {
            throw new \Magento\Framework\Exception(
                $className . ' should be an iterator'
            );
        }
        return $resultIterator;
    }
}
