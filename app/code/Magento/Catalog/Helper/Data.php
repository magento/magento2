<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Model\Config;

/**
 * Catalog data helper
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRICE_SCOPE_GLOBAL = 0;

    const PRICE_SCOPE_WEBSITE = 1;

    const XML_PATH_PRICE_SCOPE = 'catalog/price/scope';

    const CONFIG_USE_STATIC_URLS = 'cms/wysiwyg/use_static_urls_in_catalog';

    /**
     * @deprecated
     * @see \Magento\Catalog\Helper\Output::isDirectivesExists
     */
    const CONFIG_PARSE_URL_DIRECTIVES = 'catalog/frontend/parse_url_directives';

    const XML_PATH_DISPLAY_PRODUCT_COUNT = 'catalog/layered_navigation/display_product_count';

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
     * @var array
     */
    protected $_categoryPath;

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
     * @var \Magento\Framework\Stdlib\StringUtils
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
     * Template filter factory
     *
     * @var \Magento\Catalog\Model\Template\Filter\Factory
     */
    protected $_templateFilterFactory;

    /**
     * Tax class key factory
     *
     * @var \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory
     */
    protected $_taxClassKeyFactory;

    /**
     * Tax helper
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * Quote details factory
     *
     * @var \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory
     */
    protected $_quoteDetailsFactory;

    /**
     * Quote details item factory
     *
     * @var \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory
     */
    protected $_quoteDetailsItemFactory;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * Tax calculation service interface
     *
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    protected $_taxCalculationService;

    /**
     * Price currency
     *
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $regionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param Category $catalogCategory
     * @param Product $catalogProduct
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Template\Filter\Factory $templateFilterFactory
     * @param string $templateFilterModel
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyFactory
     * @param Config $taxConfig
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsFactory
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param CustomerSession $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Stdlib\StringUtils $string,
        Category $catalogCategory,
        Product $catalogProduct,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Template\Filter\Factory $templateFilterFactory,
        $templateFilterModel,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        CustomerSession $customerSession,
        PriceCurrencyInterface $priceCurrency,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_templateFilterFactory = $templateFilterFactory;
        $this->string = $string;
        $this->_catalogCategory = $catalogCategory;
        $this->_catalogProduct = $catalogProduct;
        $this->_coreRegistry = $coreRegistry;
        $this->_templateFilterModel = $templateFilterModel;
        $this->_taxClassKeyFactory = $taxClassKeyFactory;
        $this->_taxConfig = $taxConfig;
        $this->_quoteDetailsFactory = $quoteDetailsFactory;
        $this->_quoteDetailsItemFactory = $quoteDetailsItemFactory;
        $this->_taxCalculationService = $taxCalculationService;
        $this->_customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
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
     * @return array
     */
    public function getBreadcrumbPath()
    {
        if (!$this->_categoryPath) {
            $path = [];
            $category = $this->getCategory();
            if ($category) {
                $pathInStore = $category->getPathInStore();
                $pathIds = array_reverse(explode(',', $pathInStore));

                $categories = $category->getParentCategories();

                // add category path breadcrumb
                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $path['category' . $categoryId] = [
                            'label' => $categories[$categoryId]->getName(),
                            'link' => $this->_isCategoryLink($categoryId) ? $categories[$categoryId]->getUrl() : ''
                        ];
                    }
                }
            }

            if ($this->getProduct()) {
                $path['product'] = ['label' => $this->getProduct()->getName()];
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
            try {
                $product = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                return '';
            }
            /* @var $product \Magento\Catalog\Model\Product */
            if ($this->_catalogProduct->canShow($product, 'catalog')) {
                return $product->getProductUrl();
            }
        }
        $categoryId = $this->_catalogSession->getLastViewedCategoryId();
        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $e) {
                return '';
            }
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
            return [];
        }
    }

    /**
     * Retrieve Catalog Price Scope
     *
     * @return int
     */
    public function getPriceScope()
    {
        return $this->scopeConfig->getValue(
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
     * Check if the store is configured to use static URLs for media
     *
     * @return bool
     */
    public function isUsingStaticUrlsAllowed()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_USE_STATIC_URLS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if the parsing of URL directives is allowed for the catalog
     *
     * @return bool
     * @deprecated
     * @see \Magento\Catalog\Helper\Output::isDirectivesExists
     */
    public function isUrlDirectivesParsingAllowed()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PARSE_URL_DIRECTIVES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Retrieve template processor for catalog content
     *
     * @return \Magento\Framework\Filter\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPageTemplateProcessor()
    {
        return $this->_templateFilterFactory->create($this->_templateFilterModel);
    }

    /**
     * Whether to display items count for each filter option
     * @param int $storeId Store view ID
     * @return bool
     */
    public function shouldDisplayProductCountOnLayer($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_PRODUCT_COUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param array $taxAddress
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    private function convertDefaultTaxAddress(array $taxAddress = null)
    {
        if (empty($taxAddress)) {
            return null;
        }
        /** @var \Magento\Customer\Api\Data\AddressInterface $addressDataObject */
        $addressDataObject = $this->addressFactory->create()
            ->setCountryId($taxAddress['country_id'])
            ->setPostcode($taxAddress['postcode']);

        if (isset($taxAddress['region_id'])) {
            $addressDataObject->setRegion($this->regionFactory->create()->setRegionId($taxAddress['region_id']));
        }
        return $addressDataObject;
    }

    /**
     * Get product price with all tax settings processing
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   float $price inputted product price
     * @param   bool $includingTax return price include tax flag
     * @param   null|\Magento\Customer\Model\Address\AbstractAddress $shippingAddress
     * @param   null|\Magento\Customer\Model\Address\AbstractAddress $billingAddress
     * @param   null|int $ctc customer tax class
     * @param   null|string|bool|int|\Magento\Store\Model\Store $store
     * @param   bool $priceIncludesTax flag what price parameter contain tax
     * @param   bool $roundPrice
     * @return  float
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
            if ($priceIncludesTax === null) {
                $priceIncludesTax = $this->_taxConfig->priceIncludesTax($store);
            }

            $shippingAddressDataObject = null;
            if ($shippingAddress === null) {
                $shippingAddressDataObject =
                    $this->convertDefaultTaxAddress($this->_customerSession->getDefaultTaxShippingAddress());
            } elseif ($shippingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                $shippingAddressDataObject = $shippingAddress->getDataModel();
            }

            $billingAddressDataObject = null;
            if ($billingAddress === null) {
                $billingAddressDataObject =
                    $this->convertDefaultTaxAddress($this->_customerSession->getDefaultTaxBillingAddress());
            } elseif ($billingAddress instanceof \Magento\Customer\Model\Address\AbstractAddress) {
                $billingAddressDataObject = $billingAddress->getDataModel();
            }

            $taxClassKey = $this->_taxClassKeyFactory->create();
            $taxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
                ->setValue($product->getTaxClassId());

            if ($ctc === null && $this->_customerSession->getCustomerGroupId() != null) {
                $ctc = $this->customerGroupRepository->getById($this->_customerSession->getCustomerGroupId())
                    ->getTaxClassId();
            }

            $customerTaxClassKey = $this->_taxClassKeyFactory->create();
            $customerTaxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
                ->setValue($ctc);

            $item = $this->_quoteDetailsItemFactory->create();
            $item->setQuantity(1)
                ->setCode($product->getSku())
                ->setShortDescription($product->getShortDescription())
                ->setTaxClassKey($taxClassKey)
                ->setIsTaxIncluded($priceIncludesTax)
                ->setType('product')
                ->setUnitPrice($price);

            $quoteDetails = $this->_quoteDetailsFactory->create();
            $quoteDetails->setShippingAddress($shippingAddressDataObject)
                ->setBillingAddress($billingAddressDataObject)
                ->setCustomerTaxClassKey($customerTaxClassKey)
                ->setItems([$item])
                ->setCustomerId($this->_customerSession->getCustomerId());

            $storeId = null;
            if ($store) {
                $storeId = $store->getId();
            }
            $taxDetails = $this->_taxCalculationService->calculateTax($quoteDetails, $storeId, $roundPrice);
            $items = $taxDetails->getItems();
            $taxDetailsItem = array_shift($items);

            if ($includingTax !== null) {
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
            return $this->priceCurrency->round($price);
        } else {
            return $price;
        }
    }
}
