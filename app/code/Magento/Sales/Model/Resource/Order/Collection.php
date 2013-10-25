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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Flat sales order collection
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Resource\Order;

class Collection extends \Magento\Sales\Model\Resource\Collection\AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'sales_order_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject    = 'order_collection';

    /**
     * @var \Magento\Core\Model\Resource\Helper
     */
    protected $_coreResourceHelper;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Core\Model\Resource\Helper $coreResourceHelper
     * @param \Magento\Core\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Core\Model\Resource\Helper $coreResourceHelper,
        \Magento\Core\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($eventManager, $logger, $fetchStrategy, $entityFactory, $resource);
        $this->_coreResourceHelper = $coreResourceHelper;
    }

    /**
     * Model initialization
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Order', 'Magento\Sales\Model\Resource\Order');
        $this->addFilterToMap('entity_id', 'main_table.entity_id')
            ->addFilterToMap('customer_id', 'main_table.customer_id')
            ->addFilterToMap('quote_address_id', 'main_table.quote_address_id');
    }

    /**
     * Add items count expr to collection select, backward capability with eav structure
     *
     * @return \Magento\Sales\Model\Resource\Order\Collection
     */
    public function addItemCountExpr()
    {
        if (is_null($this->_fieldsToSelect)) {
            // If we select all fields from table, we need to add column alias
            $this->getSelect()->columns(array('items_count'=>'total_item_count'));
        } else {
            $this->addFieldToSelect('total_item_count', 'items_count');
        }
        return $this;
    }

    /**
     * Minimize usual count select
     *
     * @return \Magento\DB\Select
     */
    public function getSelectCountSql()
    {
        /* @var $countSelect \Magento\DB\Select */
        $countSelect = parent::getSelectCountSql();
        $countSelect->resetJoinLeft();
        return $countSelect;
    }

    /**
     * Reset left join
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = parent::_getAllIdsSelect($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $idsSelect;
    }

    /**
     * Join table sales_flat_order_address to select for billing and shipping order addresses.
     * Create corillation map
     *
     * @return \Magento\Sales\Model\Resource\Order\Collection
     */
    protected function _addAddressFields()
    {
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';
        $joinTable = $this->getTable('sales_flat_order_address');

        $this
            ->addFilterToMap('billing_firstname', $billingAliasName . '.firstname')
            ->addFilterToMap('billing_lastname', $billingAliasName . '.lastname')
            ->addFilterToMap('billing_telephone', $billingAliasName . '.telephone')
            ->addFilterToMap('billing_postcode', $billingAliasName . '.postcode')

            ->addFilterToMap('shipping_firstname', $shippingAliasName . '.firstname')
            ->addFilterToMap('shipping_lastname', $shippingAliasName . '.lastname')
            ->addFilterToMap('shipping_telephone', $shippingAliasName . '.telephone')
            ->addFilterToMap('shipping_postcode', $shippingAliasName . '.postcode');

        $this
            ->getSelect()
            ->joinLeft(
                array($billingAliasName => $joinTable),
                "(main_table.entity_id = {$billingAliasName}.parent_id"
                    . " AND {$billingAliasName}.address_type = 'billing')",
                array(
                    $billingAliasName . '.firstname',
                    $billingAliasName . '.lastname',
                    $billingAliasName . '.telephone',
                    $billingAliasName . '.postcode'
                )
            )
            ->joinLeft(
                array($shippingAliasName => $joinTable),
                "(main_table.entity_id = {$shippingAliasName}.parent_id"
                    . " AND {$shippingAliasName}.address_type = 'shipping')",
                array(
                    $shippingAliasName . '.firstname',
                    $shippingAliasName . '.lastname',
                    $shippingAliasName . '.telephone',
                    $shippingAliasName . '.postcode'
                )
            );
        $this->_coreResourceHelper->prepareColumnsList($this->getSelect());
        return $this;
    }

    /**
     * Add addresses information to select
     *
     * @return \Magento\Sales\Model\Resource\Collection\AbstractCollection
     */
    public function addAddressFields()
    {
        return $this->_addAddressFields();
    }

    /**
     * Add field search filter to collection as OR condition
     *
     * @see self::_getConditionSql for $condition
     *
     * @param string $field
     * @param null|string|array $condition
     * @return \Magento\Sales\Model\Resource\Order\Collection
     */
    public function addFieldToSearchFilter($field, $condition = null)
    {
        $field = $this->_getMappedField($field);
        $this->_select->orWhere($this->_getConditionSql($field, $condition));
        return $this;
    }

    /**
     * Specify collection select filter by attribute value
     *
     * @param array $attributes
     * @param array|integer|string|null $condition
     * @return \Magento\Sales\Model\Resource\Order\Collection
     */
    public function addAttributeToSearchFilter($attributes, $condition = null)
    {
        if (is_array($attributes) && !empty($attributes)) {
            $this->_addAddressFields();

            foreach ($attributes as $attribute) {
                $this->addFieldToSearchFilter($this->_attributeToField($attribute['attribute']), $attribute);
            }
        } else {
            $this->addAttributeToFilter($attributes, $condition);
        }

        return $this;
    }

    /**
     * Add filter by specified billing agreements
     *
     * @param int|array $agreements
     * @return \Magento\Sales\Model\Resource\Order\Collection
     */
    public function addBillingAgreementsFilter($agreements)
    {
        $agreements = (is_array($agreements)) ? $agreements : array($agreements);
        $this->getSelect()
            ->joinInner(
                array('sbao' => $this->getTable('sales_billing_agreement_order')),
                'main_table.entity_id = sbao.order_id',
                array())
            ->where('sbao.agreement_id IN(?)', $agreements);
        return $this;
    }

    /**
     * Add filter by specified recurring profile id(s)
     *
     * @param array|int $ids
     * @return \Magento\Sales\Model\Resource\Order\Collection
     */
    public function addRecurringProfilesFilter($ids)
    {
        $ids = (is_array($ids)) ? $ids : array($ids);
        $this->getSelect()
            ->joinInner(
                array('srpo' => $this->getTable('sales_recurring_profile_order')),
                'main_table.entity_id = srpo.order_id',
                array())
            ->where('srpo.profile_id IN(?)', $ids);
        return $this;
    }
}
