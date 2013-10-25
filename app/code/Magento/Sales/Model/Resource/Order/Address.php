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
 * Flat sales order address resource
 */
namespace Magento\Sales\Model\Resource\Order;

class Address extends \Magento\Sales\Model\Resource\Order\AbstractOrder
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'sales_order_address_resource';

    /**
     * @var \Magento\Sales\Model\Resource\Factory
     */
    protected $_salesResourceFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavEntityTypeFactory
     * @param \Magento\Sales\Model\Resource\Factory $salesResourceFactory
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Resource $resource,
        \Magento\Eav\Model\Entity\TypeFactory $eavEntityTypeFactory,
        \Magento\Sales\Model\Resource\Factory $salesResourceFactory
    ) {
        parent::__construct($eventManager, $resource, $eavEntityTypeFactory);
        $this->_salesResourceFactory = $salesResourceFactory;
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('sales_flat_order_address', 'entity_id');
    }

    /**
     * Return configuration for all attributes
     *
     * @return array
     */
    public function getAllAttributes()
    {
        $attributes = array(
            'city'       => __('City'),
            'company'    => __('Company'),
            'country_id' => __('Country'),
            'email'      => __('Email'),
            'firstname'  => __('First Name'),
            'lastname'   => __('Last Name'),
            'region_id'  => __('State/Province'),
            'street'     => __('Street Address'),
            'telephone'  => __('Telephone'),
            'postcode'   => __('Zip/Postal Code')
        );
        asort($attributes);
        return $attributes;
    }

    /**
     * Update related grid table after object save
     *
     * @param \Magento\Core\Model\AbstractModel|\Magento\Object $object
     * @return \Magento\Core\Model\Resource\Db\AbstractDb
     */
    protected function _afterSave(\Magento\Core\Model\AbstractModel $object)
    {
        $resource = parent::_afterSave($object);
        if ($object->hasDataChanges() && $object->getOrder()) {
            $gridList = array(
                'Magento\Sales\Model\Resource\Order' => 'entity_id',
                'Magento\Sales\Model\Resource\Order\Invoice' => 'order_id',
                'Magento\Sales\Model\Resource\Order\Shipment' => 'order_id',
                'Magento\Sales\Model\Resource\Order\Creditmemo' => 'order_id'
            );

            // update grid table after grid update
            foreach ($gridList as $gridResource => $field) {
                $this->_salesResourceFactory->create($gridResource)->updateOnRelatedRecordChanged(
                    $field,
                    $object->getParentId()
                );
            }
        }

        return $resource;
    }
}
