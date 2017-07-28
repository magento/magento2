<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Category\Action;

/**
 * Factory class for \Magento\Catalog\Model\Indexer\Product\Category\Action\Rows
 * @since 2.0.0
 */
class RowsFactory extends \Magento\Catalog\Model\Indexer\Category\Product\Action\RowsFactory
{
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Catalog\Model\Indexer\Product\Category\Action\Rows::class
    ) {
        parent::__construct($objectManager, $instanceName);
    }
}
