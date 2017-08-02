<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Class \Magento\Framework\Api\Search\SearchResultFactory
 *
 * @since 2.0.0
 */
class SearchResultFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.0.0
     */
    protected $_instanceName = null;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\Api\Search\SearchResult::class
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return SearchResultInterface
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
