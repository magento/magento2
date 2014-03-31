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
namespace Magento\Checkout\Block\Onepage\Shipping\Method;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface as CustomerAccountService;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface as CustomerAddressService;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Sales\Model\Quote\Address;

/**
 * One page checkout status
 */
class Available extends \Magento\Checkout\Block\Onepage\AbstractOnepage
{
    /**
     * @var array
     */
    protected $_rates;

    /**
     * @var Address
     */
    protected $_address;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData = null;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\App\Cache\Type\Config $configCacheType
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory
     * @param CustomerAccountService $customerAccountService
     * @param CustomerAddressService $customerAddressService
     * @param AddressConfig $addressConfig
     * @param \Magento\App\Http\Context $httpContext
     * @param \Magento\Tax\Helper\Data $taxData
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\App\Cache\Type\Config $configCacheType,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory,
        CustomerAccountService $customerAccountService,
        CustomerAddressService $customerAddressService,
        AddressConfig $addressConfig,
        \Magento\App\Http\Context $httpContext,
        \Magento\Tax\Helper\Data $taxData,
        array $data = array()
    ) {
        $this->_taxData = $taxData;
        parent::__construct(
            $context,
            $coreData,
            $configCacheType,
            $customerSession,
            $resourceSession,
            $countryCollectionFactory,
            $regionCollectionFactory,
            $customerAccountService,
            $customerAddressService,
            $addressConfig,
            $httpContext,
            $data
        );
        $this->_isScopePrivate = true;
    }

    /**
     * @return array
     */
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();
            $this->_rates = $this->getAddress()->getGroupedAllShippingRates();
        }

        return $this->_rates;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    /**
     * @param string $carrierCode
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_storeConfig->getConfig('carriers/' . $carrierCode . '/title')) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * @return string
     */
    public function getAddressShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }

    /**
     * @param float $price
     * @param bool|null $flag
     * @return float
     */
    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(
            $this->_taxData->getShippingPrice($price, $flag, $this->getAddress()),
            true
        );
    }
}
