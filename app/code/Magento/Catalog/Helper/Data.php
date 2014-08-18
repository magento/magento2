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
namespace Magento\Catalog\Helper;

use Magento\Catalog\Model\Product\Attribute\Source\Msrp\Type;
use Magento\Tax\Service\V1\Data\QuoteDetailsBuilder;
use Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder as QuoteDetailsItemBuilder;
use Magento\Tax\Service\V1\Data\TaxClassKey;
use Magento\Tax\Service\V1\Data\TaxClassKeyBuilder;
use Magento\Tax\Service\V1\TaxCalculationServiceInterface;
use Magento\Customer\Model\Address\Converter as AddressConverter;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Tax\Model\Config;

/**
 * Catalog data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRICE_SCOPE_GLOBAL = 0;

    const PRICE_SCOPE_WEBSITE = 1;

    const XML_PATH_PRICE_SCOPE = 'catalog/price/scope';

    const XML_PATH_SEO_SAVE_HISTORY = 'catalog/seo/save_rewrites_history';

    const CONFIG_USE_STATIC_URLS = 'cms/wysiwyg/use_static_urls_in_catalog';

    const CONFIG_PARSE_URL_DIRECTIVES = 'catalog/frontend/parse_url_directives';

    const XML_PATH_DISPLAY_PRODUCT_COUNT = 'catalog/layered_navigation/display_product_count';

    /**
     * Minimum advertise price constants
     */
    const XML_PATH_MSRP_ENABLED = 'sales/msrp/enabled';

    const XML_PATH_MSRP_DISPLAY_ACTUAL_PRICE_TYPE = 'sales/msrp/display_price_type';

    const XML_PATH_MSRP_APPLY_TO_ALL = 'sales/msrp/apply_for_all';

    const XML_PATH_MSRP_EXPLANATION_MESSAGE = 'sales/msrp/explanation_message';

    const XML_PATH_MSRP_EXPLANATION_MESSAGE_WHATS_THIS = 'sales/msrp/explanation_message_whats_this';

    /**
     * Cache context
     */
    const CONTEXT_CATALOG_SORT_DIRECTION = 'catalog_sort_direction';

    const CONTEXT_CATALOG_SORT_ORDER = 'catalog_sort_order';

    const CONTEXT_CATALOG_DISPLAY_MODE = 'catalog_mode';

    const CONTEXT_CATALOG_LIMIT = 'catalog_limit';

    /**
     * Breadcrumb Path cache
     *
     * @var string
     */
    protected $_categoryPath;

    /**
     * Array of product types that MAP enabled
     *
     * @var array
     */
    protected $_mapApplyToProductType = null;

    /**
     * Currently selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Catalog product
     *
     * @var Product
     */
    protected $_catalogProduct;

    /**
     * Catalog category
     *
     * @var Category
     */
    protected $_catalogCategory;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @var string
     */
    protected $_templateFilterModel;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Eav attribute factory
     *
     * @var \Magento\Catalog\Model\Resource\Eav\AttributeFactory
     */
    protected $_eavAttributeFactory;

    /**
     * Template filter factory
     *
     * @var \Magento\Catalog\Model\Template\Filter\Factory
     */
    protected $_templateFilterFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Tax class key builder
     *
     * @var \Magento\Tax\Service\V1\Data\TaxClassKeyBuilder
     */
    protected $_taxClassKeyBuilder;

    /**
     * Tax helper
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * Quote details builder
     *
     * @var QuoteDetailsBuilder
     */
    protected $_quoteDetailsBuilder;

    /**
     * Quote details item builder
     *
     * @var QuoteDetailsItemBuilder
     */
    protected $_quoteDetailsItemBuilder;

    /**
     * Address converter
     *
     * @var AddressConverter
     */
    protected $_addressConverter;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * Tax calculation service interface
     *
     * @var \Magento\Tax\Service\V1\TaxCalculationServiceInterface
     */
    protected $_taxCalculationService;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Resource\Eav\AttributeFactory $eavAttributeFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Stdlib\String $string
     * @param Category $catalogCategory
     * @param Product $catalogProduct
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Template\Filter\Factory $templateFilterFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param string $templateFilterModel
     * @param \Magento\Tax\Service\V1\Data\TaxClassKeyBuilder $taxClassKeyBuilder
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param QuoteDetailsBuilder $quoteDetailsBuilder
     * @param QuoteDetailsItemBuilder $quoteDetailsItemBuilder
     * @param \Magento\Tax\Service\V1\TaxCalculationServiceInterface $taxCalculationService
     * @param CustomerSession $customerSession
     * @param AddressConverter $addressConverter
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Resource\Eav\AttributeFactory $eavAttributeFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Stdlib\String $string,
        Category $catalogCategory,
        Product $catalogProduct,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Template\Filter\Factory $templateFilterFactory,
        \Magento\Framework\Escaper $escaper,
        $templateFilterModel,
        \Magento\Tax\Service\V1\Data\TaxClassKeyBuilder $taxClassKeyBuilder,
        \Magento\Tax\Model\Config $taxConfig,
        QuoteDetailsBuilder $quoteDetailsBuilder,
        QuoteDetailsItemBuilder $quoteDetailsItemBuilder,
        \Magento\Tax\Service\V1\TaxCalculationServiceInterface $taxCalculationService,
        CustomerSession $customerSession,
        AddressConverter $addressConverter
    ) {
        $this->_eavAttributeFactory = $eavAttributeFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_templateFilterFactory = $templateFilterFactory;
        $this->string = $string;
        $this->_catalogCategory = $catalogCategory;
        $this->_catalogProduct = $catalogProduct;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $coreRegistry;
        $this->_templateFilterModel = $templateFilterModel;
        $this->_escaper = $escaper;
        $this->_taxClassKeyBuilder = $taxClassKeyBuilder;
        $this->_taxConfig = $taxConfig;
        $this->_quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->_quoteDetailsItemBuilder = $quoteDetailsItemBuilder;
        $this->_taxCalculationService = $taxCalculationService;
        $this->_customerSession = $customerSession;
        $this->_addressConverter = $addressConverter;
        parent::__construct($context);
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    /**
     * Return current category path or get it from current category
     * and creating array of categories|product paths for breadcrumbs
     *
     * @return string
     */
    public function getBreadcrumbPath()
    {
        if (!$this->_categoryPath) {

            $path = array();
            $category = $this->getCategory();
            if ($category) {
                $pathInStore = $category->getPathInStore();
                $pathIds = array_reverse(explode(',', $pathInStore));

                $categories = $category->getParentCategories();

                // add category path breadcrumb
                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $path['category' . $categoryId] = array(
                            'label' => $categories[$categoryId]->getName(),
                            'link' => $this->_isCategoryLink($categoryId) ? $categories[$categoryId]->getUrl() : ''
                        );
                    }
                }
            }

            if ($this->getProduct()) {
                $path['product'] = array('label' => $this->getProduct()->getName());
            }

            $this->_categoryPath = $path;
        }
        return $this->_categoryPath;
    }

    /**
     * Check is category link
     *
     * @param int $categoryId
     * @return bool
     */
    protected function _isCategoryLink($categoryId)
    {
        if ($this->getProduct()) {
            return true;
        }
        if ($categoryId != $this->getCategory()->getId()) {
            return true;
        }
        return false;
    }

    /**
     * Return current category object
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('current_category');
    }

    /**
     * Retrieve current Product object
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve Visitor/Customer Last Viewed URL
     *
     * @return string
     */
    public function getLastViewedUrl()
    {
        $productId = $this->_catalogSession->getLastViewedProductId();
        if ($productId) {
            $product = $this->_productFactory->create()->load($productId);
            /* @var $product \Magento\Catalog\Model\Product */
            if ($this->_catalogProduct->canShow($product, 'catalog')) {
                return $product->getProductUrl();
            }
        }
        $categoryId = $this->_catalogSession->getLastViewedCategoryId();
        if ($categoryId) {
            $category = $this->_categoryFactory->create()->load($categoryId);
            /* @var $category \Magento\Catalog\Model\Category */
            if (!$this->_catalogCategory->canShow($category)) {
                return '';
            }
            return $category->getCategoryUrl();
        }
        return '';
    }

    /**
     * Split SKU of an item by dashes and spaces
     * Words will not be broken, unless this length is greater than $length
     *
     * @param string $sku
     * @param int $length
     * @return string[]
     */
    public function splitSku($sku, $length = 30)
    {
        return $this->string->split($sku, $length, true, false, '[\-\s]');
    }

    /**
     * Retrieve attribute hidden fields
     *
     * @return array
     */
    public function getAttributeHiddenFields()
    {
        if ($this->_coreRegistry->registry('attribute_type_hidden_fields')) {
            return $this->_coreRegistry->registry('attribute_type_hidden_fields');
        } else {
            return array();
        }
    }

    /**
     * Retrieve Catalog Price Scope
     *
     * @return int
     */
    public function getPriceScope()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is Global Price
     *
     * @return bool
     */
    public function isPriceGlobal()
    {
        return $this->getPriceScope() == self::PRICE_SCOPE_GLOBAL;
    }

    /**
     * Indicate whether to save URL Rewrite History or not (create redirects to old URLs)
     *
     * @param int $storeId Store View
     * @return bool
     */
    public function shouldSaveUrlRewritesHistory($storeId = null)
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_SEO_SAVE_HISTORY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if the store is configured to use static URLs for media
     *
     * @return bool
     */
    public function isUsingStaticUrlsAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            self::CONFIG_USE_STATIC_URLS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Check if the parsing of URL directives is allowed for the catalog
     *
     * @return bool
     */
    public function isUrlDirectivesParsingAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            self::CONFIG_PARSE_URL_DIRECTIVES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Retrieve template processor for catalog content
     *
     * @return \Magento\Framework\Filter\Template
     */
    public function getPageTemplateProcessor()
    {
        return $this->_templateFilterFactory->create($this->_templateFilterModel);
    }

    /**
     * Check if Minimum Advertised Price is enabled
     *
     * @return bool
     */
    public function isMsrpEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_MSRP_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Return MAP display actual type
     *
     * @return null|string
     */
    public function getMsrpDisplayActualPriceType()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_MSRP_DISPLAY_ACTUAL_PRICE_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Check if MAP apply to all products
     *
     * @return bool
     */
    public function isMsrpApplyToAll()
    {
        return (bool) $this->_scopeConfig->getValue(
            self::XML_PATH_MSRP_APPLY_TO_ALL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Return MAP explanation message
     *
     * @return string
     */
    public function getMsrpExplanationMessage()
    {
        return $this->_escaper->escapeHtml(
            $this->_scopeConfig->getValue(
                self::XML_PATH_MSRP_EXPLANATION_MESSAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeId
            ),
            array('b', 'br', 'strong', 'i', 'u', 'p', 'span')
        );
    }

    /**
     * Return MAP explanation message for "Whats This" window
     *
     * @return string
     */
    public function getMsrpExplanationMessageWhatsThis()
    {
        return $this->_escaper->escapeHtml(
            $this->_scopeConfig->getValue(
                self::XML_PATH_MSRP_EXPLANATION_MESSAGE_WHATS_THIS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeId
            ),
            array('b', 'br', 'strong', 'i', 'u', 'p', 'span')
        );
    }

    /**
     * Check if can apply Minimum Advertise price to product
     * in specific visibility
     *
     * @param int|\Magento\Catalog\Model\Product $product
     * @param int $visibility Check displaying price in concrete place (by default generally)
     * @param bool $checkAssociatedItems
     * @return bool
     */
    public function canApplyMsrp($product, $visibility = null, $checkAssociatedItems = true)
    {
        if (!$this->isMsrpEnabled()) {
            return false;
        }

        if (is_numeric($product)) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->_productFactory->create()->setStoreId(
                $this->_storeManager->getStore()->getId()
            )->load(
                $product
            );
        }

        if (!$this->canApplyMsrpToProductType($product)) {
            return false;
        }

        $result = $product->getMsrpEnabled();
        if ($result == Type\Enabled::MSRP_ENABLE_USE_CONFIG) {
            $result = $this->isMsrpApplyToAll();
        }

        if (!$product->hasMsrpEnabled() && $this->isMsrpApplyToAll()) {
            $result = true;
        }

        if ($result && $visibility !== null) {
            $productVisibility = $product->getMsrpDisplayActualPriceType();
            if ($productVisibility == Type\Price::TYPE_USE_CONFIG) {
                $productVisibility = $this->getMsrpDisplayActualPriceType();
            }
            $result = $productVisibility == $visibility;
        }

        if ($product->getTypeInstance()->isComposite($product)
            && $checkAssociatedItems
            && (!$result || $visibility !== null)
        ) {
            $resultInOptions = $product->getTypeInstance()->isMapEnabledInOptions($product, $visibility);
            if ($resultInOptions !== null) {
                $result = $resultInOptions;
            }
        }

        return $result;
    }

    /**
     * Check whether MAP applied to product Product Type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function canApplyMsrpToProductType($product)
    {
        if ($this->_mapApplyToProductType === null) {
            /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            $attribute = $this->_eavAttributeFactory->create()->loadByCode(
                \Magento\Catalog\Model\Product::ENTITY,
                'msrp_enabled'
            );
            $this->_mapApplyToProductType = $attribute->getApplyTo();
        }
        return empty($this->_mapApplyToProductType) || in_array($product->getTypeId(), $this->_mapApplyToProductType);
    }

    /**
     * Get MAP message for price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getMsrpPriceMessage($product)
    {
        $message = "";
        if ($this->canApplyMsrp($product, Type::TYPE_IN_CART)) {
            $message = __('To see product price, add this item to your cart. You can always remove it later.');
        } elseif ($this->canApplyMsrp($product, Type::TYPE_BEFORE_ORDER_CONFIRM)) {
            $message = __('See price before order confirmation.');
        }
        return $message;
    }

    /**
     * Check is product need gesture to show price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isShowPriceOnGesture($product)
    {
        return $this->canApplyMsrp($product, Type::TYPE_ON_GESTURE);
    }

    /**
     * Whether to display items count for each filter option
     * @param int $storeId Store view ID
     * @return bool
     */
    public function shouldDisplayProductCountOnLayer($storeId = null)
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_PRODUCT_COUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product price with all tax settings processing
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   float $price inputted product price
     * @param   bool $includingTax return price include tax flag
     * @param   null|Address $shippingAddress
     * @param   null|Address $billingAddress
     * @param   null|int $ctc customer tax class
     * @param   null|string|bool|int|Store $store
     * @param   bool $priceIncludesTax flag what price parameter contain tax
     * @param   bool $roundPrice
     * @return  float
     */
    public function getTaxPrice(
        $product,
        $price,
        $includingTax = null,
        $shippingAddress = null,
        $billingAddress = null,
        $ctc = null,
        $store = null,
        $priceIncludesTax = null,
        $roundPrice = true
    ) {
        if (!$price) {
            return $price;
        }

        $store = $this->_storeManager->getStore($store);
        if ($this->_taxConfig->needPriceConversion($store)) {
            if (is_null($priceIncludesTax)) {
                $priceIncludesTax = $this->_taxConfig->priceIncludesTax($store);
            }

            $shippingAddressDataObject = null;
            if ($shippingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                $shippingAddressDataObject = $this->_addressConverter->createAddressFromModel(
                    $shippingAddress,
                    null,
                    null
                );
            }

            $billingAddressDataObject = null;
            if ($billingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                $billingAddressDataObject = $this->_addressConverter->createAddressFromModel(
                    $billingAddress,
                    null,
                    null
                );
            }

            $item = $this->_quoteDetailsItemBuilder->setQuantity(1)
                ->setCode($product->getSku())
                ->setShortDescription($product->getShortDescription())
                ->setTaxClassKey(
                    $this->_taxClassKeyBuilder->setType(TaxClassKey::TYPE_ID)
                        ->setValue($product->getTaxClassId())->create()
                )->setTaxIncluded($priceIncludesTax)
                ->setType('product')
                ->setUnitPrice($price)
                ->create();
            $quoteDetails = $this->_quoteDetailsBuilder
                ->setShippingAddress($shippingAddressDataObject)
                ->setBillingAddress($billingAddressDataObject)
                ->setCustomerTaxClassKey(
                    $this->_taxClassKeyBuilder->setType(TaxClassKey::TYPE_ID)
                        ->setValue($ctc)->create()
                )->setItems([$item])
                ->setCustomerId($this->_customerSession->getCustomerId())
                ->create();

            $storeId = null;
            if ($store) {
                $storeId = $store->getId();
            }
            $taxDetails = $this->_taxCalculationService->calculateTax($quoteDetails, $storeId);
            $items = $taxDetails->getItems();
            $taxDetailsItem = array_shift($items);

            if (!is_null($includingTax)) {
                if ($includingTax) {
                    $price = $taxDetailsItem->getPriceInclTax();
                } else {
                    $price = $taxDetailsItem->getPrice();
                }
            } else {
                switch ($this->_taxConfig->getPriceDisplayType($store)) {
                    case Config::DISPLAY_TYPE_EXCLUDING_TAX:
                    case Config::DISPLAY_TYPE_BOTH:
                        $price = $taxDetailsItem->getPrice();
                        break;
                    case Config::DISPLAY_TYPE_INCLUDING_TAX:
                        $price = $taxDetailsItem->getPriceInclTax();
                        break;
                    default:
                        break;
                }
            }
        }

        if ($roundPrice) {
            return $store->roundPrice($price);
        } else {
            return $price;
        }
    }
}
