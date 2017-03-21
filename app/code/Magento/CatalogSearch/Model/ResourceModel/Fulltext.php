<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\ResourceModel;

/**
 * CatalogSearch Fulltext Index resource model
 */
class Fulltext extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connectionName = null
    ) {
        $this->_eventManager = $eventManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_fulltext', 'product_id');
    }

    /**
     * Reset search results
     *
     * @return $this
     */
    public function resetSearchResults()
    {
        $connection = $this->getConnection();
        $connection->update($this->getTable('search_query'), ['is_processed' => 0], ['is_processed != 0']);
        $this->_eventManager->dispatch('catalogsearch_reset_search_result');
        return $this;
    }
}
