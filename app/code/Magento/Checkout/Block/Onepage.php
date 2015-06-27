<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;

/**
 * Onepage checkout block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Onepage extends \Magento\Checkout\Block\Onepage\AbstractOnepage
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
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var array|Checkout\LayoutProcessorInterface[]
     */
    protected $layoutProcessors;

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
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param LayoutProcessorInterface[] $layoutProcessors
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        array $layoutProcessors = [],
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
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->configProvider = $configProvider;
        $this->layoutProcessors = $layoutProcessors;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        return \Zend_Json::encode($this->jsLayout);
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
     * Retrieve checkout configuration
     *
     * @return array
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Get base url for block.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
