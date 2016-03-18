<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Indexer\Stock\Plugin;

class StoreGroup
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_indexerProcessor;

    /**
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor  $indexerProcessor
     */
    public function __construct(\Magento\CatalogInventory\Model\Indexer\Stock\Processor $indexerProcessor)
    {
        $this->_indexerProcessor = $indexerProcessor;
    }

    /**
     * Before save handler
     *
     * @param \Magento\Store\Model\ResourceModel\Group $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Store\Model\ResourceModel\Group $subject,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        if (!$object->getId() || $object->dataHasChangedFor('website_id')) {
            $this->_indexerProcessor->markIndexerAsInvalid();
        }
    }
}
