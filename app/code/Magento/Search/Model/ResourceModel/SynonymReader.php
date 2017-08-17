<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\ResourceModel;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Synonym Reader resource model
 */
class SynonymReader extends AbstractDb
{
    /**
     * @var \Magento\Framework\DB\Helper\Mysql\Fulltext $fullTextSelect
     */
    private $fullTextSelect;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Helper\Mysql\Fulltext $fulltext
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Helper\Mysql\Fulltext $fulltext,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->fullTextSelect = $fulltext;
        $this->storeManager = $storeManager;
    }

    /**
     * Custom load model: Get data by user query phrase
     *
     * @param \Magento\Search\Model\SynonymReader $object
     * @param string $phrase
     * @return $this
     */
    public function loadByPhrase(\Magento\Search\Model\SynonymReader $object, $phrase)
    {
        $rows = $this->queryByPhrase(strtolower($phrase));
        $synsPerScope = $this->getSynRowsPerScope($rows);

        if (!empty($synsPerScope[\Magento\Store\Model\ScopeInterface::SCOPE_STORES])) {
            $object->setData($synsPerScope[\Magento\Store\Model\ScopeInterface::SCOPE_STORES]);
        } elseif (!empty($synsPerScope[\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES])) {
            $object->setData($synsPerScope[\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES]);
        } else {
            $object->setData($synsPerScope[\Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT]);
        }
        $this->_afterLoad($object);
        return $this;
    }

    /**
     * Init resource data
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('search_synonyms', 'group_id');
    }

    /**
     * A helper function to query by phrase and get results
     *
     * @param string $phrase
     * @return array
     */
    private function queryByPhrase($phrase)
    {
        $matchQuery = $this->fullTextSelect->getMatchQuery(
            ['synonyms' => 'synonyms'],
            $phrase,
            Fulltext::FULLTEXT_MODE_BOOLEAN
        );
        $query = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where($matchQuery);

        return $this->getConnection()->fetchAll($query);
    }

    /**
     * A private helper function to retrieve matching synonym groups per scope
     *
     * @param array $rows
     * @return array
     */
    private function getSynRowsPerScope($rows)
    {
        $synRowsForStoreView = [];
        $synRowsForWebsite = [];
        $synRowsForDefault = [];

        // The synonyms configured for current store view gets highest priority. Second highest is current website
        // scope. If there were no store view and website specific synonyms then at last 'default' (All store views)
        // will be considered.
        foreach ($rows as $row) {
            if ($this->isSynRowForStoreView($row)) {
                // Check for current store view
                $synRowsForStoreView[] = $row;
            } elseif (empty($synRowsForStoreView) && $this->isSynRowForWebsite($row)) {
                // Check for current website
                $synRowsForWebsite[] = $row;
            } elseif (empty($synRowsForStoreView)
                && empty($synRowsForWebsite)
                && $this->isSynRowForDefaultScope($row)) {
                // Check for all store views (i.e. global/default config)
                $synRowsForDefault[] = $row;
            }
        }
        $synsPerScope[\Magento\Store\Model\ScopeInterface::SCOPE_STORES] = $synRowsForStoreView;
        $synsPerScope[\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES] = $synRowsForWebsite;
        $synsPerScope[\Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT] = $synRowsForDefault;
        return $synsPerScope;
    }

    /**
     * A helper method to check if the synonym group row is for the current store view
     *
     * @param array $row
     * @return bool
     */
    private function isSynRowForStoreView($row)
    {
        $storeViewId = $this->storeManager->getStore()->getId();
        return ($row['store_id'] === $storeViewId);
    }

    /**
     * A helper method to check if the synonym group row is for the current website
     *
     * @param array $row
     * @return bool
     */
    private function isSynRowForWebsite($row)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        return (($row['website_id'] === $websiteId) && ($row['store_id'] == 0));
    }

    /**
     * A helper method to check if the synonym group row is for all store views (default or global scope)
     *
     * @param array $row
     * @return bool
     */
    private function isSynRowForDefaultScope($row)
    {
        return (($row['website_id'] == 0) && ($row['store_id'] == 0));
    }
}
