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
 * Sales order address model
 *
 * @method \Magento\Sales\Model\Resource\Order\Address _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Address getResource()
 * @method int getParentId()
 * @method \Magento\Sales\Model\Order\Address setParentId(int $value)
 * @method int getCustomerAddressId()
 * @method \Magento\Sales\Model\Order\Address setCustomerAddressId(int $value)
 * @method int getQuoteAddressId()
 * @method \Magento\Sales\Model\Order\Address setQuoteAddressId(int $value)
 * @method \Magento\Sales\Model\Order\Address setRegionId(int $value)
 * @method int getCustomerId()
 * @method \Magento\Sales\Model\Order\Address setCustomerId(int $value)
 * @method string getFax()
 * @method \Magento\Sales\Model\Order\Address setFax(string $value)
 * @method \Magento\Sales\Model\Order\Address setRegion(string $value)
 * @method string getPostcode()
 * @method \Magento\Sales\Model\Order\Address setPostcode(string $value)
 * @method string getLastname()
 * @method \Magento\Sales\Model\Order\Address setLastname(string $value)
 * @method string getCity()
 * @method \Magento\Sales\Model\Order\Address setCity(string $value)
 * @method string getEmail()
 * @method \Magento\Sales\Model\Order\Address setEmail(string $value)
 * @method string getTelephone()
 * @method \Magento\Sales\Model\Order\Address setTelephone(string $value)
 * @method string getCountryId()
 * @method \Magento\Sales\Model\Order\Address setCountryId(string $value)
 * @method string getFirstname()
 * @method \Magento\Sales\Model\Order\Address setFirstname(string $value)
 * @method string getAddressType()
 * @method \Magento\Sales\Model\Order\Address setAddressType(string $value)
 * @method string getPrefix()
 * @method \Magento\Sales\Model\Order\Address setPrefix(string $value)
 * @method string getMiddlename()
 * @method \Magento\Sales\Model\Order\Address setMiddlename(string $value)
 * @method string getSuffix()
 * @method \Magento\Sales\Model\Order\Address setSuffix(string $value)
 * @method string getCompany()
 * @method \Magento\Sales\Model\Order\Address setCompany(string $value)
 */
namespace Magento\Sales\Model\Order;

class Address extends \Magento\Customer\Model\Address\AbstractAddress
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_address';

    /**
     * @var string
     */
    protected $_eventObject = 'address';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct(
            $eventManager,
            $directoryData,
            $context,
            $registry,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Address');
    }

    /**
     * Set order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Model\Order\Address
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this->_orderFactory->create()->load($this->getParentId());
        }
        return $this->_order;
    }

    /**
     * Before object save manipulations
     *
     * @return \Magento\Sales\Model\Order\Address
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getOrder()) {
            $this->setParentId($this->getOrder()->getId());
        }

        // Init customer address id if customer address is assigned
        if ($this->getCustomerAddress()) {
            $this->setCustomerAddressId($this->getCustomerAddress()->getId());
        }

        return $this;
    }
}
