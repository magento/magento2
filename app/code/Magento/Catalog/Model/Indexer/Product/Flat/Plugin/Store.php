<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Indexer\Product\Flat\Plugin;

/**
 * Class \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\Store
 *
 * @since 2.0.0
 */
class Store
{
    /**
     * Product flat indexer processor
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     * @since 2.0.0
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor)
    {
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
    }

    /**
     * Before save handler
     *
     * @param \Magento\Store\Model\ResourceModel\Store $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function beforeSave(\Magento\Store\Model\ResourceModel\Store $subject, \Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getId() || $object->dataHasChangedFor('group_id')) {
            $this->_productFlatIndexerProcessor->markIndexerAsInvalid();
        }
    }
}
