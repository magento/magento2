<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Class SearchResultIteratorFactory
 * @since 2.0.0
 */
class SearchResultIteratorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function create($className, array $arguments = [])
    {
        $resultIterator = $this->objectManager->create($className, $arguments);
        if (!$resultIterator instanceof \Traversable) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('%1 should be an iterator', [$className])
            );
        }
        return $resultIterator;
    }
}
