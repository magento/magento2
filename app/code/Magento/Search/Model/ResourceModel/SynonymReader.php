<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Search\Model\SynonymReader as SynReaderModel;
use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Synonym Reader resource model
 */
class SynonymReader extends AbstractDb
{
    /**
     * @var Fulltext $fullTextSelect
     */
    private $fullTextSelect;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Fulltext $fulltext
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Fulltext $fulltext,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->fullTextSelect = $fulltext;
        $this->storeManager = $storeManager;
    }

    /**
     * Custom load model: Get data by user query phrase
     *
     * @param SynReaderModel $object
     * @param string $phrase
     * @return $this
     */
    public function loadByPhrase(SynReaderModel $object, $phrase)
    {
        $rows = $this->queryByPhrase(strtolower($phrase));

        $storeViewId = $this->storeManager->getStore()->getId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $synRowsForStoreView = [];
        $synRowsForWebsite = [];
        $synRowsForDefault = [];

        // The synonyms configured for current store view gets highest priority. Second highest is current website
        // scope. If there were no store view and website specific synonyms then at last 'default' (All store views)
        // will be considered.

        foreach ($rows as $index => $row) {
            // Check for current store view
            if ($row['scope_id'] === $storeViewId && $row['scope_type'] === \Magento\Store\Model\ScopeInterface::SCOPE_STORES) {
                $synRowsForStoreView[] = $row;
            }
            // Check for current website
            else if (empty($synRowsForStoreView) &&
                ($row['scope_id'] === $websiteId && $row['scope_type'] === \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES)) {
                    $synRowsForWebsite[] = $row;
            }
            // Check for all store views (i.e. default)
            elseif (empty($synRowsForStoreView) && empty($synRowsForWebsite)) {
                $synRowsForDefault[] = $row;
            }
        }

        if (!empty($synRowsForStoreView)) {
            $object->setData($synRowsForStoreView);
        }
        elseif (!empty($synRowsForWebsite)) {
            $object->setData($synRowsForWebsite);
        }
        else {
            $object->setData($synRowsForDefault);
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
     * @param string $value
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
}