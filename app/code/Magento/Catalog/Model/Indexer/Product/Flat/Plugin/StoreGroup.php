<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Group;

class StoreGroup
{
    /**
     * Product flat indexer processor
     *
     * @var Processor
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @param Processor $productFlatIndexerProcessor
     */
    public function __construct(Processor $productFlatIndexerProcessor)
    {
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
    }

    /**
     * Before save handler
     *
     * @param Group $subject
     * @param Group $result
     * @param AbstractModel $object
     *
     * @return Group
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Group $subject, Group $result, AbstractModel $object)
    {
        if ($object->isObjectNew() || $object->dataHasChangedFor('root_category_id')) {
            $this->_productFlatIndexerProcessor->markIndexerAsInvalid();
        }

        return $result;
    }
}
