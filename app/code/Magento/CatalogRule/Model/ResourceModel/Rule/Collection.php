<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\ResourceModel\Rule;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap;


    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        array $associatedEntitiesMap = []
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_associatedEntitiesMap = $associatedEntitiesMap;
    }

    /**
     * Set resource model
     *
     * @param array $associatedEntitiesMap
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct(

    ) {
        $this->_init('Magento\CatalogRule\Model\Rule', 'Magento\CatalogRule\Model\ResourceModel\Rule');

    }

    /**
     * Find product attribute in conditions or actions
     *
     * @param string $attributeCode
     * @return $this
     * @api
     */
    public function addAttributeInConditionFilter($attributeCode)
    {
        $match = sprintf('%%%s%%', substr(serialize(['attribute' => $attributeCode]), 5, -1));
        $this->addFieldToFilter('conditions_serialized', ['like' => $match]);

        return $this;
    }

    /**
     * Limit rules collection by specific customer group
     *
     * @param int $customerGroupId
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroupId)
    {
        $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
        if (!$this->getFlag('is_customer_group_joined')) {
            $this->setFlag('is_customer_group_joined', true);
            $this->getSelect()->join(
                ['customer_group' => $this->getTable($entityInfo['associations_table'])],
                $this->getConnection()
                    ->quoteInto('customer_group.' . $entityInfo['entity_id_field'] . ' = ?', $customerGroupId)
                    . ' AND main_table.' . $entityInfo['rule_id_field'] . ' = customer_group.'
                    . $entityInfo['rule_id_field'],
                []
            );
        }
        return $this;
    }
}
