<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Indexer frontend resource model.
 *
 * This model can be used in client code to correctly identify the indexer table
 * that is used on fronted for read operations.
 * Please note that the table name is provided at runtime based on indexer's state
 * so this resource should be only used for table name resolving purposes.
 */
class FrontendResource extends AbstractDb
{
    /**
     * @var string
     */
    private $indexerId;

    /**
     * @var string
     */
    private $indexerBaseTable;

    /**
     * @var \Magento\Indexer\Model\Indexer\StateFactory
     */
    private $indexerStateFactory;

    /**
     * @param Context $context
     * @param string $indexerId
     * @param string $indexerBaseTable
     * @param string $idFieldName
     * @param \Magento\Indexer\Model\Indexer\StateFactory $indexerStateFactory
     * @param null|string $connectionName
     */
    public function __construct(
        Context $context,
        $indexerId,
        $indexerBaseTable,
        $idFieldName,
        \Magento\Indexer\Model\Indexer\StateFactory $indexerStateFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->indexerId = $indexerId;
        $this->indexerBaseTable = $indexerBaseTable;
        $this->indexerStateFactory = $indexerStateFactory;
        $this->_idFieldName = $idFieldName;
    }

    /**
     * Retrieve indexer frontend table name.
     * The table that is used for read operations only.
     *
     * @return string
     */
    public function getMainTable()
    {
        $indexerState = $this->indexerStateFactory->create()->loadByIndexer($this->indexerId);

        return $this->getTable($this->indexerBaseTable . $indexerState->getTableSuffix());
    }

    /**
     * No resource initialization is required for this model.
     * @return void
     */
    protected function _construct()
    {
        // nothing to initialize
    }
}
