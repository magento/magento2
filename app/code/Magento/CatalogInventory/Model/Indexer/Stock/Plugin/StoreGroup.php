<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock\Plugin;

use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Group;

class StoreGroup
{
    /**
     * @var Processor
     */
    protected $_indexerProcessor;

    /**
     * @param Processor $indexerProcessor
     */
    public function __construct(Processor $indexerProcessor)
    {
        $this->_indexerProcessor = $indexerProcessor;
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
        if ($object->isObjectNew() || $object->dataHasChangedFor('website_id')) {
            $this->_indexerProcessor->markIndexerAsInvalid();
        }

        return $result;
    }
}
