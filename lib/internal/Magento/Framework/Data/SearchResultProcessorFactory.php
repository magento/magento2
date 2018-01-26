<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Class SearchResultProcessorFactory
 */
class SearchResultProcessorFactory
{
    const DEFAULT_INSTANCE_NAME = 'Magento\Framework\Data\SearchResultProcessor';

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param AbstractSearchResult $collection
     * @return SearchResultProcessor
     */
    public function create(AbstractSearchResult $collection)
    {
        return $this->objectManager->create(static::DEFAULT_INSTANCE_NAME, ['searchResult' => $collection]);
    }
}
