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
 * @method \Magento\Sales\Model\Resource\Order\Shipment\Track _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Shipment\Track getResource()
 * @method int getParentId()
 * @method \Magento\Sales\Model\Order\Shipment\Track setParentId(int $value)
 * @method float getWeight()
 * @method \Magento\Sales\Model\Order\Shipment\Track setWeight(float $value)
 * @method float getQty()
 * @method \Magento\Sales\Model\Order\Shipment\Track setQty(float $value)
 * @method int getOrderId()
 * @method \Magento\Sales\Model\Order\Shipment\Track setOrderId(int $value)
 * @method string getDescription()
 * @method \Magento\Sales\Model\Order\Shipment\Track setDescription(string $value)
 * @method string getTitle()
 * @method \Magento\Sales\Model\Order\Shipment\Track setTitle(string $value)
 * @method string getCarrierCode()
 * @method \Magento\Sales\Model\Order\Shipment\Track setCarrierCode(string $value)
 * @method string getCreatedAt()
 * @method \Magento\Sales\Model\Order\Shipment\Track setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Sales\Model\Order\Shipment\Track setUpdatedAt(string $value)
 *
 */
namespace Magento\Shipping\Model\Order;

class Track extends \Magento\Sales\Model\Order\Shipment\Track
{
    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $localeDate,
            $dateTime,
            $storeManager,
            $shipmentFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_carrierFactory = $carrierFactory;
    }

    /**
     * Retrieve detail for shipment track
     *
     * @return string
     */
    public function getNumberDetail()
    {
        $carrierInstance = $this->_carrierFactory->create($this->getCarrierCode());
        if (!$carrierInstance) {
            $custom = array();
            $custom['title'] = $this->getTitle();
            $custom['number'] = $this->getTrackNumber();
            return $custom;
        } else {
            $carrierInstance->setStore($this->getStore());
        }

        $trackingInfo = $carrierInstance->getTrackingInfo($this->getNumber());
        if (!$trackingInfo) {
            return __('No detail for number "%1"', $this->getNumber());
        }

        return $trackingInfo;
    }
}
