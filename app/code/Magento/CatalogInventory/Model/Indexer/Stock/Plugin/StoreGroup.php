<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Indexer\Stock\Plugin;

/**
 * Class \Magento\CatalogInventory\Model\Indexer\Stock\Plugin\StoreGroup
 *
 * @since 2.0.0
 */
class StoreGroup
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     * @since 2.0.0
     */
    protected $_indexerProcessor;

    /**
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor  $indexerProcessor
     * @since 2.0.0
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
     * @since 2.0.0
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
