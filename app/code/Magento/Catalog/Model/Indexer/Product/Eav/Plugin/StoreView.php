<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Plugin;

/**
 * Class \Magento\Catalog\Model\Indexer\Product\Eav\Plugin\StoreView
 *
 * @since 2.0.0
 */
class StoreView
{
    /**
     * Product attribute indexer processor
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     * @since 2.0.0
     */
    protected $_indexerEavProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Eav\Processor $indexerEavProcessor
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Product\Eav\Processor $indexerEavProcessor)
    {
        $this->_indexerEavProcessor = $indexerEavProcessor;
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
    public function beforeSave(
        \Magento\Store\Model\ResourceModel\Store $subject,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        if ((!$object->getId() || $object->dataHasChangedFor('group_id')) && $object->getIsActive()) {
            $this->_indexerEavProcessor->markIndexerAsInvalid();
        }
    }
}
