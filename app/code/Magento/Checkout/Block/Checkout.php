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
     * Retrieve current active quote.
     *
     * @return string
     */
    public function getCart()
    {
        $quoteId = $this->getQuote()->getId();
        return \Zend_Json::encode($this->cartRepositoryInterface->get($quoteId));
    }
}
