<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Indexer\Product\Category\Action;

/**
 * Factory class for \Magento\Catalog\Model\Indexer\Product\Category\Action\Rows
 */
class RowsFactory extends \Magento\Catalog\Model\Indexer\Category\Product\Action\RowsFactory
{
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Catalog\Model\Indexer\Product\Category\Action\Rows::class
    ) {
        parent::__construct($objectManager, $instanceName);
    }
}
