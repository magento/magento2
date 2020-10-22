<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Entity attribute option collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection
{
    /**
     * Option value table
     *
     * @var string
     */
    protected $_optionValueTable;

    /**
     * @var ResourceConnection
     */
    protected $_coreResource;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param ResourceConnection $coreResource
     * @param StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param AbstractDb $resource
     * @codeCoverageIgnore
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ResourceConnection $coreResource,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
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
            \Magento\Eav\Model\Entity\Attribute\Option::class,
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option::class
        );
        $this->_optionValueTable = $this->_coreResource->getTableName('eav_attribute_option_value');
    }

    /**
     * Set attribute filter
     *
     * @param int $setId
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAttributeFilter($setId)
    {
        return $this->addFieldToFilter('main_table.attribute_id', $setId);
    }

    /**
     * Set value filter
     *
     * @param int|array $valueId
     * @return $this
     */
    public function setValueFilter($valueId)
    {
        $connection = $this->getConnection();

        $this->getSelect()
            ->join(
                ['tdv' => $this->_optionValueTable],
                'tdv.option_id = main_table.option_id',
                [
                    'value' => 'tdv.value',
                    'value_id' => 'tdv.value_id'
                ]
            )->join(
                ['ea' => $connection->getTableName('eav_attribute')],
                'main_table.attribute_id = ea.attribute_id',
                ['attribute_code' => 'ea.attribute_code']
            )->join(
                ['eat' => $connection->getTableName('eav_entity_type')],
                'ea.entity_type_id = eat.entity_type_id',
                ['entity_type_code' => 'eat.entity_type_code']
            )
            ->where('tdv.value_id IN (?)', $valueId);

        return $this;
    }

    /**
     * Add store filter to collection
     *
     * @param int $storeId
     * @param bool $useDefaultValue
     * @return $this
     * @throws NoSuchEntityException
     */
    public function setStoreFilter($storeId = null, $useDefaultValue = true)
    {
        if ($storeId === null) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $connection = $this->getConnection();

        $joinCondition = $connection->quoteInto(
            'tsv.option_id = main_table.option_id AND tsv.store_id = ?',
            $storeId
        );

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
                    'value' => $connection->getCheckSql(
                        'tsv.value_id > 0',
                        'tsv.value',
                        'tdv.value'
                    ),
                    'value_id' => $connection->getCheckSql(
                        'tsv.value_id > 0',
                        'tsv.value_id',
                        'tdv.value_id'
                    )
                ]
            )->where(
                'tdv.store_id = ?',
                0
            );
        } else {
            $this->getSelect()->joinLeft(
                ['tsv' => $this->_optionValueTable],
                $joinCondition,
                'value, value_id'
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
     * @codeCoverageIgnore
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
        return $this->_toOptionArray(
            'option_id',
            $valueKey,
            [
                'option_id' => 'option_id',
                'value_id' => 'value_id'
            ]
        );
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
