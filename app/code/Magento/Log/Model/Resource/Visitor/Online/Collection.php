<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Resource\Visitor\Online;

/**
 * Log Online visitors collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Joined fields array
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * @var \Magento\Eav\Helper\Data
     */
    protected $_eavHelper;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Helper\Data $eavHelper
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Helper\Data $eavHelper,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_eavHelper = $eavHelper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initialize collection model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Visitor\Online', 'Magento\Log\Model\Resource\Visitor\Online');
    }

    /**
     * Add Customer data to collection
     *
     * @return $this
     */
    public function addCustomerData()
    {
        // alias => attribute_code
        $attributes = [
            'customer_lastname' => 'customer.lastname',
            'customer_firstname' => 'customer.firstname',
            'customer_email' => 'customer.email',
        ];

        $this->getSelect()->joinLeft(
            ['customer' => $this->getResource()->getTable('customer_entity')],
            'customer.entity_id=main_table.customer_id',
            $attributes
        );

        $this->_fields = array_merge($this->_fields, $attributes);

        $this->setFlag('has_customer_data', true);
        return $this;
    }

    /**
     * Filter collection by specified website(s)
     *
     * @param int|int[] $websiteIds
     * @return $this
     * @api
     */
    public function addWebsiteFilter($websiteIds)
    {
        if ($this->getFlag('has_customer_data')) {
            $this->getSelect()->where('customer.website_id IN (?)', $websiteIds);
        }
        return $this;
    }

    /**
     * Add field filter to collection
     * If $attribute is an array will add OR condition with following format:
     * array(
     *     array('attribute'=>'firstname', 'like'=>'test%'),
     *     array('attribute'=>'lastname', 'like'=>'test%'),
     * )
     *
     * @param string $field
     * @param null|string|array $condition
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     *
     * @see self::_getConditionSql for $condition
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (isset($this->_fields[$field])) {
            $field = $this->_fields[$field];
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
