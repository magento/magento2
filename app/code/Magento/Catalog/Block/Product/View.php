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
namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Tax\Service\V1\TaxCalculationServiceInterface;

/**
 * Product View block
 */
class View extends AbstractProduct implements \Magento\Framework\View\Block\IdentityInterface
{
    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var TaxCalculationServiceInterface
     */
    protected $taxCalculationService;

    /**
     * @param Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param TaxCalculationServiceInterface $taxCalculationService
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        TaxCalculationServiceInterface $taxCalculationService,
        array $data = array()
    ) {
        $this->_productHelper = $productHelper;
        $this->_coreData = $coreData;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_productFactory = $productFactory;
        $this->productTypeConfig = $productTypeConfig;
        $this->string = $string;
        $this->_localeFormat = $localeFormat;
        $this->customerSession = $customerSession;
        $this->taxCalculationService = $taxCalculationService;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Add meta information from product to head block
     *
     * @return \Magento\Catalog\Block\Product\View
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->createBlock('Magento\Catalog\Block\Breadcrumbs');
        $product = $this->getProduct();
        if (!$product) {
            return parent::_prepareLayout();
        }

        $title = $product->getMetaTitle();
        if ($title) {
            $this->pageConfig->setTitle($title);
        }
        $keyword = $product->getMetaKeyword();
        $currentCategory = $this->_coreRegistry->registry('current_category');
        if ($keyword) {
            $this->pageConfig->setKeywords($keyword);
        } elseif ($currentCategory) {
            $this->pageConfig->setKeywords($product->getName());
        }
        $description = $product->getMetaDescription();
        if ($description) {
            $this->pageConfig->setDescription($description);
        } else {
            $this->pageConfig->setDescription($this->string->substr($product->getDescription(), 0, 255));
        }
        if ($this->_productHelper->canUseCanonicalTag()) {
            $this->pageConfig->addRemotePageAsset(
                $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]),
                ['attributes' => array('rel' => 'canonical')]
            );
        }

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($product->getName());
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->_coreRegistry->registry('product') && $this->getProductId()) {
            $product = $this->_productFactory->create()->load($this->getProductId());
            $this->_coreRegistry->register('product', $product);
        }
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Check if product can be emailed to friend
     *
     * @return bool
     */
    public function canEmailToFriend()
    {
        return false;
    }

    /**
     * Retrieve url for direct adding product to cart
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($this->hasCustomAddToCartUrl()) {
            return $this->getCustomAddToCartUrl();
        }

        if ($this->getRequest()->getParam('wishlist_next')) {
            $additional['wishlist_next'] = 1;
        }

        $addUrlKey = \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED;
        $addUrlValue = $this->_urlBuilder->getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
        $additional[$addUrlKey] = $this->_coreData->urlEncode($addUrlValue);

        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $config = array();
        if (!$this->hasOptions()) {
            return $this->_jsonEncoder->encode($config);
        }

        $customerId = $this->getCustomerId();
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $this->getProduct();
        $defaultTax = $this->taxCalculationService->getDefaultCalculatedRate(
            $product->getTaxClassId(),
            $customerId
        );
        $currentTax = $this->taxCalculationService->getCalculatedRate(
            $product->getTaxClassId(),
            $customerId
        );

        $tierPrices = array();

        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();

        foreach ($tierPricesList as $tierPrice) {
            $tierPrices[] = $this->_coreData->currency($tierPrice['price']->getValue(), false, false);
        }
        $config = array(
            'productId' => $product->getId(),
            'priceFormat' => $this->_localeFormat->getPriceFormat(),
            'includeTax' => $this->_taxData->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax' => $this->_taxData->displayPriceIncludingTax(),
            'showBothPrices' => $this->_taxData->displayBothPrices(),
            'productPrice' => $this->_coreData->currency(
                $product->getPriceInfo()->getPrice('final_price')->getValue(),
                false,
                false
            ),
            'productOldPrice' => $this->_coreData->currency(
                $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(),
                false,
                false
            ),
            'inclTaxPrice' => $this->_coreData->currency(
                $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                false,
                false
            ),
            'exclTaxPrice' => $this->_coreData->currency(
                $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount(),
                false,
                false
            ),
            'defaultTax' => $defaultTax,
            'currentTax' => $currentTax,
            'idSuffix' => '_clone',
            'oldPlusDisposition' => 0,
            'plusDisposition' => 0,
            'plusDispositionTax' => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition' => 0,
            'tierPrices' => $tierPrices
        );

        $responseObject = new \Magento\Framework\Object();
        $this->_eventManager->dispatch('catalog_product_view_config', array('response_object' => $responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return $this->_jsonEncoder->encode($config);
    }

    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        if ($this->getProduct()->getTypeInstance()->hasOptions($this->getProduct())) {
            return true;
        }
        return false;
    }

    /**
     * Check if product has required options
     *
     * @return bool
     */
    public function hasRequiredOptions()
    {
        return $this->getProduct()->getTypeInstance()->hasRequiredOptions($this->getProduct());
    }

    /**
     * Define if setting of product options must be shown instantly.
     * Used in case when options are usually hidden and shown only when user
     * presses some button or link. In editing mode we better show these options
     * instantly.
     *
     * @return bool
     */
    public function isStartCustomization()
    {
        return $this->getProduct()->getConfigureMode() || $this->_request->getParam('startcustomization');
    }

    /**
     * Get default qty - either as preconfigured, or as 1.
     * Also restricts it by minimal qty.
     *
     * @param null|\Magento\Catalog\Model\Product $product
     * @return int|float
     */
    public function getProductDefaultQty($product = null)
    {
        if (!$product) {
            $product = $this->getProduct();
        }

        $qty = $this->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }

    /**
     * Get container name, where product options should be displayed
     *
     * @return string
     */
    public function getOptionsContainer()
    {
        return $this->getProduct()->getOptionsContainer() == 'container1' ? 'container1' : 'container2';
    }

    /**
     * Check whether quantity field should be rendered
     *
     * @return bool
     */
    public function shouldRenderQuantity()
    {
        return !$this->productTypeConfig->isProductSet($this->getProduct()->getTypeId());
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = $this->getProduct()->getIdentities();
        $category = $this->_coreRegistry->registry('current_category');
        if ($category) {
            $identities[] = Product::CACHE_PRODUCT_CATEGORY_TAG . '_' . $category->getId();
        }
        return $identities;
    }

    /**
     * Retrieve customer data object
     *
     * @return int
     */
    protected function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }
}
