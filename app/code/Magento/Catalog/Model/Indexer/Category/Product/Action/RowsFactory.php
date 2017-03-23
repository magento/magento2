<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

/**
 * Factory class for \Magento\Catalog\Model\Indexer\Category\Product\Action\Rows
 */
class RowsFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Catalog\Model\Indexer\Category\Product\Action\Rows::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @throws \InvalidArgumentException
     * @return \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
     */
    public function create(array $data = [])
    {
        /** @var \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction $instance */
        $instance = $this->objectManager->create($this->instanceName, $data);
        if (!$instance instanceof \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction) {
            throw new \InvalidArgumentException(
                $this->instanceName .
                ' is not instance of \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction'
            );
        }
        return $instance;
    }
}
