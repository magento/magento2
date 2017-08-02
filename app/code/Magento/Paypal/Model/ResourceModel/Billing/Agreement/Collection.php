<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\ResourceModel\Billing\Agreement;

/**
 * Billing agreements resource collection
 *
 * @api
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Mapping for fields
     *
     * @var array
     */
    protected $_map = [
        'fields' => [
            'customer_email' => 'ce.email',
            'customer_firstname' => 'ce.firstname',
            'customer_lastname' => 'ce.lastname',
            'agreement_created_at' => 'main_table.created_at',
            'agreement_updated_at' => 'main_table.updated_at',
        ],
    ];

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $_customerResource;

    /**
     * @var \Magento\Eav\Helper\Data
     */
    protected $_eavHelper;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param \Magento\Eav\Helper\Data $eavHelper
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Eav\Helper\Data $eavHelper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_eavHelper = $eavHelper;
        $this->_customerResource = $customerResource;
    }

    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Paypal\Model\Billing\Agreement::class,
            \Magento\Paypal\Model\ResourceModel\Billing\Agreement::class
        );
    }

    /**
     * Add customer details(email, firstname, lastname) to select
     *
     * @return $this
     */
    public function addCustomerDetails()
    {
        $this->getSelect()->joinInner(
            ['ce' => $this->getTable('customer_entity')],
            'ce.entity_id = main_table.customer_id',
            [
                'customer_email' => 'email',
                'customer_firstname' => 'firstname',
                'customer_lastname' => 'lastname',
            ]
        );
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (in_array($field, ['created_at', 'updated_at'], true)) {
            $field = 'main_table.' . $field;
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
