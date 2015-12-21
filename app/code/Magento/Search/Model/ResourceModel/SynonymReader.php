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
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Fulltext $fulltext
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Fulltext $fulltext,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->fullTextSelect = $fulltext;
    }

    /**
     * Custom load model: Get data by store view id
     *
     * @param AbstractModel $object
     * @param int $value
     * @return $this
     */
    public function loadByStoreViewId(AbstractModel $object, $value)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'store_id = ?',
            $value
        );
        $data = $this->getConnection()->fetchAll($select);
        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }
        return $this;
    }

    /**
     * Custom load model: Get data by user query phrase and store view id
     *
     * @param SynReaderModel $object
     * @param string $value
     * @return $this
     */
    public function loadByPhrase(SynReaderModel $object, $value)
    {
        $phrase = strtolower($value);
        $matchQuery = $this->fullTextSelect->getMatchQuery(
            ['synonyms' => 'synonyms'],
            $phrase,
            Fulltext::FULLTEXT_MODE_BOOLEAN
        );
        $query = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'store_id = ?',
            $object->getStoreViewId()
        )->where($matchQuery);

        $rows = $this->getConnection()->fetchAll($query);
        $object->setData($rows);
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
}
