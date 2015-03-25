<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Checkout\Block;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Webapi\Exception;

/**
 * Onepage checkout block
 */
class Checkout extends \Magento\Checkout\Block\Onepage\AbstractOnepage
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var bool
     */
    protected $_isScopePrivate = false;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepositoryInterface;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $cartItemRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    protected $cartData;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressConfig $addressConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Directory\Model\Resource\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\Resource\Region\CollectionFactory $regionCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressConfig $addressConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement,
        \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $configCacheType,
            $customerSession,
            $resourceSession,
            $countryCollectionFactory,
            $regionCollectionFactory,
            $customerRepository,
            $addressConfig,
            $httpContext,
            $addressMapper,
            $data
        );
        $this->formKey = $formKey;
        $this->_isScopePrivate = true;
        $this->jsLayout = is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->localeCurrency = $localeCurrency;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->cartItemRepository = $cartItemRepository;
    }


    /**
     * @return string
     */
    public function getJsLayout()
    {
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * Get 'one step checkout' step data
     *
     * @return array
     */
    public function getSteps()
    {
        $steps = array();
        $stepCodes = $this->_getStepCodes();

        if ($this->isCustomerLoggedIn()) {
            $stepCodes = array_diff($stepCodes, array('login'));
        }

        foreach ($stepCodes as $step) {
            $steps[$step] = $this->getCheckout()->getStepData($step);
        }

        return $steps;
    }

    /**
     * Get active step
     *
     * @return string
     */
    public function getActiveStep()
    {
        return $this->isCustomerLoggedIn() ? 'billing' : 'login';
    }

    /**
     * Retrieve form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Retrieve current customer data.
     *
     * @return string
     */
    public function getCustomerData()
    {
        if ($this->isCustomerLoggedIn()) {
            return \Zend_Json::encode($this->_getCustomer()->__toArray());
        }
        return \Zend_Json::encode([]);
    }

    /**
     * Retrieve current active quote object.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    protected function getCartData()
    {
        if (!$this->cartData) {
            $quoteId = $this->getQuote()->getId();
            $this->cartData = $this->cartRepositoryInterface->get($quoteId);
        }
        return $this->cartData;
    }
    /**
     * Retrieve current active quote.
     *
     * @return string
     */
    public function getCart()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return \Zend_Json::encode($this->getCartData());
        }
        return '{}';
    }

    /**
     * Cart items as array
     *
     * @return array
     */
    public function getCartItems()
    {
        $itemData = [];
        $itemObjects = $this->cartItemRepository->getList($this->getQuote()->getId());
        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach($itemObjects as $item) {
            $itemData[] = $item->toArray();
        }
        return $itemData;
    }

    /**
     * Retrieve active quote currency code.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $currencyCode = $this->getCartData()->getQuoteCurrencyCode();
            $currency = $this->localeCurrency->getCurrency($currencyCode);
            $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
            return \Zend_Json::encode(['data' => $symbol]);
        }
        return '{}';
    }

    /**
     * Retrieve selected shipping method.
     *
     * @return string|bool
     */
    public function getSelectedShippingMethod()
    {
        $selectedShippingMethod = false;
        $quoteId = $this->getQuote()->getId();
        try {
            $shippingMethod = $this->shippingMethodManagement->get($quoteId);
            if ($shippingMethod) {
                $selectedShippingMethod = $shippingMethod->getMethodCode();
            }
        } catch( \Exception $e) {
            //do nothing
        }
        return \Zend_Json::encode($selectedShippingMethod);
    }

    /**
     *  Retrieve quote store code
     *  @return string
     */
    public function getStoreCode()
    {
        return \Zend_Json::encode($this->getQuote()->getStore()->getCode());
    }
}
