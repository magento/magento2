<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $instanceName = 'Magento\Catalog\Model\Indexer\Product\Category\Action\Rows'
    ) {
        parent::__construct($objectManager, $instanceName);
    }
}
