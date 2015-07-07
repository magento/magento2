<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Resource\Billing\Agreement;

use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Billing agreements resource collection
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
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
     * @var \Magento\Customer\Model\Resource\Customer
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
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param \Magento\Eav\Helper\Data $eavHelper
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Resource\Customer $customerResource,
        \Magento\Eav\Helper\Data $eavHelper,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
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
        $this->_init('Magento\Paypal\Model\Billing\Agreement', 'Magento\Paypal\Model\Resource\Billing\Agreement');
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
}
