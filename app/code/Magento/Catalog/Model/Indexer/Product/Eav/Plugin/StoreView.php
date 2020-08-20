<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product\Eav\Plugin;

use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Store;

class StoreView
{
    /**
     * Product attribute indexer processor
     *
     * @var Processor
     */
    protected $_indexerEavProcessor;

    /**
     * @param Processor $indexerEavProcessor
     */
    public function __construct(Processor $indexerEavProcessor)
    {
        $this->_indexerEavProcessor = $indexerEavProcessor;
    }

    /**
     * Before save handler
     *
     * @param Store $subject
     * @param Store $result
     * @param AbstractModel $object
     *
     * @return Store
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Store $subject, Store $result, AbstractModel $object)
    {
        if (($object->isObjectNew() || $object->dataHasChangedFor('group_id')) && $object->getIsActive()) {
            $this->_indexerEavProcessor->markIndexerAsInvalid();
        }

        return $result;
    }
}
