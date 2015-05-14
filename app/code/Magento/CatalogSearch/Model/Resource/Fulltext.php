<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource;

/**
 * CatalogSearch Fulltext Index resource model
 */
class Fulltext extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $resourcePrefix = null
    ) {
        $this->_eventManager = $eventManager;
        parent::__construct($context, $resourcePrefix);
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
        $adapter = $this->_getWriteAdapter();
        $adapter->update($this->getTable('search_query'), ['is_processed' => 0], ['is_processed != 0']);
        $this->_eventManager->dispatch('catalogsearch_reset_search_result');
        return $this;
    }
}
