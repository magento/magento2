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
namespace Magento\Sales\Model\Order\Shipment;

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
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Track extends \Magento\Sales\Model\AbstractModel
{
    /**
     * Code of custom carrier
     */
    const CUSTOM_CARRIER_CODE = 'custom';

    /**
     * @var \Magento\Sales\Model\Order\Shipment|null
     */
    protected $_shipment = null;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_track';

    /**
     * @var string
     */
    protected $_eventObject = 'track';

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $_shipmentFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $localeDate, $dateTime, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
        $this->_shipmentFactory = $shipmentFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Shipment\Track');
    }

    /**
     * Tracking number getter
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->getData('track_number');
    }

    /**
     * Tracking number setter
     *
     * @param string $number
     * @return \Magento\Framework\Object
     */
    public function setNumber($number)
    {
        return $this->setData('track_number', $number);
    }

    /**
     * Declare Shipment instance
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    public function setShipment(\Magento\Sales\Model\Order\Shipment $shipment)
    {
        $this->_shipment = $shipment;
        return $this;
    }

    /**
     * Retrieve Shipment instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        if (!$this->_shipment instanceof \Magento\Sales\Model\Order\Shipment) {
            $this->_shipment = $this->_shipmentFactory->create()->load($this->getParentId());
        }

        return $this->_shipment;
    }

    /**
     * Check whether custom carrier was used for this track
     *
     * @return bool
     */
    public function isCustom()
    {
        return $this->getCarrierCode() == self::CUSTOM_CARRIER_CODE;
    }

    /**
     * Retrieve hash code of current order
     *
     * @return string
     */
    public function getProtectCode()
    {
        return (string)$this->getShipment()->getProtectCode();
    }

    /**
     * Get store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if ($this->getShipment()) {
            return $this->getShipment()->getStore();
        }
        return $this->_storeManager->getStore();
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Before object save
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getShipment()) {
            $this->setParentId($this->getShipment()->getId());
        }

        return $this;
    }

    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $data
     * @return $this
     */
    public function addData(array $data)
    {
        if (array_key_exists('number', $data)) {
            $this->setNumber($data['number']);
            unset($data['number']);
        }
        return parent::addData($data);
    }
}
