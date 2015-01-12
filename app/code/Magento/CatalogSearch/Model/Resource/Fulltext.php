<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource;

use Magento\Search\Model\Resource\Helper;

/**
 * CatalogSearch Fulltext Index resource model
 */
class Fulltext extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Core string
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * CatalogSearch resource helper
     *
     * @var \Magento\Search\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param Helper $resourceHelper
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Search\Model\Resource\Helper $resourceHelper
    ) {
        $this->_eventManager = $eventManager;
        $this->filter = $filter;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($resource);
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
        $adapter->update($this->getTable('search_query'), ['is_processed' => 0]);
        $this->_eventManager->dispatch('catalogsearch_reset_search_result');
        return $this;
    }
}
