<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Resource\Entity\Attribute\Option;

/**
 * Entity attribute option collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Option value table
     *
     * @var string
     */
    protected $_optionValueTable;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_coreResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Resource $coreResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Resource $coreResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_coreResource = $coreResource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\Eav\Model\Entity\Attribute\Option',
            'Magento\Eav\Model\Resource\Entity\Attribute\Option'
        );
        $this->_optionValueTable = $this->_coreResource->getTableName('eav_attribute_option_value');
    }

    /**
     * Set attribute filter
     *
     * @param int $setId
     * @return $this
     */
    public function setAttributeFilter($setId)
    {
        return $this->addFieldToFilter('attribute_id', $setId);
    }

    /**
     * Add store filter to collection
     *
     * @param int $storeId
     * @param bool $useDefaultValue
     * @return $this
     */
    public function setStoreFilter($storeId = null, $useDefaultValue = true)
    {
        if (is_null($storeId)) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $adapter = $this->getConnection();

        $joinCondition = $adapter->quoteInto('tsv.option_id = main_table.option_id AND tsv.store_id = ?', $storeId);

        if ($useDefaultValue) {
            $this->getSelect()->join(
                ['tdv' => $this->_optionValueTable],
                'tdv.option_id = main_table.option_id',
                ['default_value' => 'value']
            )->joinLeft(
                ['tsv' => $this->_optionValueTable],
                $joinCondition,
                [
                    'store_default_value' => 'value',
                    'value' => $adapter->getCheckSql('tsv.value_id > 0', 'tsv.value', 'tdv.value')
                ]
            )->where(
                'tdv.store_id = ?',
                0
            );
        } else {
            $this->getSelect()->joinLeft(
                ['tsv' => $this->_optionValueTable],
                $joinCondition,
                'value'
            )->where(
                'tsv.store_id = ?',
                $storeId
            );
        }

        $this->setOrder('value', self::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * Add option id(s) filter to collection
     *
     * @param int|array $optionId
     * @return $this
     */
    public function setIdFilter($optionId)
    {
        return $this->addFieldToFilter('main_table.option_id', ['in' => $optionId]);
    }

    /**
     * Convert collection items to select options array
     *
     * @param string $valueKey
     * @return array
     */
    public function toOptionArray($valueKey = 'value')
    {
        return $this->_toOptionArray('option_id', $valueKey);
    }

    /**
     * Set order by position or alphabetically by values in admin
     *
     * @param string $dir direction
     * @param bool $sortAlpha sort alphabetically by values in admin
     * @return $this
     */
    public function setPositionOrder($dir = self::SORT_ORDER_ASC, $sortAlpha = false)
    {
        $this->setOrder('main_table.sort_order', $dir);
        // sort alphabetically by values in admin
        if ($sortAlpha) {
            $this->getSelect()->joinLeft(
                ['sort_alpha_value' => $this->_optionValueTable],
                'sort_alpha_value.option_id = main_table.option_id AND sort_alpha_value.store_id = 0',
                ['value']
            );
            $this->setOrder('sort_alpha_value.value', $dir);
        }

        return $this;
    }
}
