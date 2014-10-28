<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Report Customers Review collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Review\Customer;

class Collection extends \Magento\Review\Model\Resource\Review\Collection
{
    /**
     * @var \Magento\Customer\Model\Resource\Customer
     */
    protected $_customerResource;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Review\Helper\Data $reviewData
     * @param \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Resource\Customer $customerResource
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Review\Helper\Data $reviewData,
        \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Resource\Customer $customerResource,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->_customerResource = $customerResource;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $reviewData,
            $voteFactory,
            $storeManager,
            $connection,
            $resource
        );
    }

    /**
     * Init Select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinCustomers();
        return $this;
    }

    /**
     * Join customers
     *
     * @return $this
     */
    protected function _joinCustomers()
    {
        /** @var $adapter \Magento\Framework\DB\Adapter\AdapterInterface */
        $adapter = $this->getConnection();
        /** @var $firstnameAttr \Magento\Eav\Model\Entity\Attribute */
        $firstnameAttr = $this->_customerResource->getAttribute('firstname');
        /** @var $lastnameAttr \Magento\Eav\Model\Entity\Attribute */
        $lastnameAttr = $this->_customerResource->getAttribute('lastname');

        $firstnameCondition = array('table_customer_firstname.entity_id = detail.customer_id');

        if ($firstnameAttr->getBackend()->isStatic()) {
            $firstnameField = 'firstname';
        } else {
            $firstnameField = 'value';
            $firstnameCondition[] = $adapter->quoteInto(
                'table_customer_firstname.attribute_id = ?',
                (int)$firstnameAttr->getAttributeId()
            );
        }

        $this->getSelect()->joinInner(
            array('table_customer_firstname' => $firstnameAttr->getBackend()->getTable()),
            implode(' AND ', $firstnameCondition),
            array()
        );


        $lastnameCondition = array('table_customer_lastname.entity_id = detail.customer_id');
        if ($lastnameAttr->getBackend()->isStatic()) {
            $lastnameField = 'lastname';
        } else {
            $lastnameField = 'value';
            $lastnameCondition[] = $adapter->quoteInto(
                'table_customer_lastname.attribute_id = ?',
                (int)$lastnameAttr->getAttributeId()
            );
        }

        //Prepare fullname field result
        $customerFullname = $adapter->getConcatSql(
            array("table_customer_firstname.{$firstnameField}", "table_customer_lastname.{$lastnameField}"),
            ' '
        );
        $this->getSelect()->reset(
            \Zend_Db_Select::COLUMNS
        )->joinInner(
            array('table_customer_lastname' => $lastnameAttr->getBackend()->getTable()),
            implode(' AND ', $lastnameCondition),
            array()
        )->columns(
            array(
                'customer_id' => 'detail.customer_id',
                'customer_name' => $customerFullname,
                'review_cnt' => 'COUNT(main_table.review_id)'
            )
        )->group(
            'detail.customer_id'
        );

        return $this;
    }

    /**
     * Get select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->_select;
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::GROUP);
        $countSelect->reset(\Zend_Db_Select::HAVING);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);

        $countSelect->columns(new \Zend_Db_Expr('COUNT(DISTINCT detail.customer_id)'));

        return $countSelect;
    }
}
