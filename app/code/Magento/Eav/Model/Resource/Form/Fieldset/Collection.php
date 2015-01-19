<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */


/**
 * Eav Form Fieldset Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Resource\Form\Fieldset;

use Magento\Core\Model\EntityFactory;
use Magento\Eav\Model\Form\Type;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Model\Resource\Db\AbstractDb;
use Psr\Log\LoggerInterface as Logger;
use Magento\Store\Model\StoreManagerInterface;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Store scope ID
     *
     * @var int
     */
    protected $_storeId;

    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initialize collection model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Form\Fieldset', 'Magento\Eav\Model\Resource\Form\Fieldset');
    }

    /**
     * Add Form Type filter to collection
     *
     * @param Type|int $type
     * @return $this
     */
    public function addTypeFilter($type)
    {
        if ($type instanceof Type) {
            $type = $type->getId();
        }

        return $this->addFieldToFilter('type_id', $type);
    }

    /**
     * Set order by fieldset sort order
     *
     * @return $this
     */
    public function setSortOrder()
    {
        $this->setOrder('sort_order', self::SORT_ORDER_ASC);
        return $this;
    }

    /**
     * Retrieve label store scope
     *
     * @return int
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Set store scope ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $select->join(
            ['default_label' => $this->getTable('eav_form_fieldset_label')],
            'main_table.fieldset_id = default_label.fieldset_id AND default_label.store_id = 0',
            []
        );
        if ($this->getStoreId() == 0) {
            $select->columns('label', 'default_label');
        } else {
            $labelExpr = $select->getAdapter()->getIfNullSql('store_label.label', 'default_label.label');
            $joinCondition = $this->getConnection()->quoteInto(
                'main_table.fieldset_id = store_label.fieldset_id AND store_label.store_id = ?',
                (int)$this->getStoreId()
            );
            $select->joinLeft(
                ['store_label' => $this->getTable('eav_form_fieldset_label')],
                $joinCondition,
                ['label' => $labelExpr]
            );
        }

        return $this;
    }
}
