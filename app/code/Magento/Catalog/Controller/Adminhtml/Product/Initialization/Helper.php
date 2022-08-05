<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\Backend\Helper\Js;
use Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory as ProductLinkFactory;
use Magento\Catalog\Api\Data\ProductLinkTypeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface\Proxy as ProductRepository;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\AttributeFilter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Authorization as ProductAuthorization;
use Magento\Catalog\Model\Product\Filter\DateTime as DateTimeFilter;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\Link\Resolver as LinkResolver;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Filter_Input;

/**
 * Product helper
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Helper
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StockDataFilter
     */
    protected $stockFilter;

    /**
     * @var Js
     */
    protected $jsHelper;

    /**
     * @var Date
     * @deprecated 101.0.0
     */
    protected $dateFilter;

    /**
     * @var CustomOptionFactory
     * @since 101.0.0
     */
    protected $customOptionFactory;

    /**
     * @var ProductLinkFactory
     * @since 101.0.0
     */
    protected $productLinkFactory;

    /**
     * @var ProductRepository
     * @since 101.0.0
     */
    protected $productRepository;

    /**
     * @var ProductLinks
     */
    protected $productLinks;

    /**
     * @var LinkResolver
     */
    private $linkResolver;

    /**
     * @var LinkTypeProvider
     */
    private $linkTypeProvider;

    /**
     * @var AttributeFilter
     */
    private $attributeFilter;

    /**
     * @var ProductAuthorization
     */
    private $productAuthorization;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var DateTimeFilter
     */
    private $dateTimeFilter;

    /**
     * @var CategoryLinkInterfaceFactory
     */
    private $categoryLinkFactory;

    /**
     * @var array
     */
    private $productDataKeys = [
        'weight',
        'special_price',
        'cost',
        'country_of_manufacture',
        'description',
        'short_description',
        'meta_description',
        'meta_keyword',
        'meta_title',
        'page_layout',
        'custom_design',
        'gift_wrapping_price'
    ];

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param StockDataFilter $stockFilter
     * @param ProductLinks $productLinks
     * @param Js $jsHelper
     * @param Date $dateFilter
     * @param CustomOptionFactory|null $customOptionFactory
     * @param ProductLinkFactory|null $productLinkFactory
     * @param ProductRepositoryInterface|null $productRepository
     * @param LinkTypeProvider|null $linkTypeProvider
     * @param AttributeFilter|null $attributeFilter
     * @param FormatInterface|null $localeFormat
     * @param ProductAuthorization|null $productAuthorization
     * @param DateTimeFilter|null $dateTimeFilter
     * @param CategoryLinkInterfaceFactory|null $categoryLinkFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        StockDataFilter $stockFilter,
        ProductLinks $productLinks,
        Js $jsHelper,
        Date $dateFilter,
        CustomOptionFactory $customOptionFactory = null,
        ProductLinkFactory $productLinkFactory = null,
        ProductRepositoryInterface $productRepository = null,
        LinkTypeProvider $linkTypeProvider = null,
        AttributeFilter $attributeFilter = null,
        FormatInterface $localeFormat = null,
        ?ProductAuthorization $productAuthorization = null,
        ?DateTimeFilter $dateTimeFilter = null,
        ?CategoryLinkInterfaceFactory $categoryLinkFactory = null
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->stockFilter = $stockFilter;
        $this->productLinks = $productLinks;
        $this->jsHelper = $jsHelper;
        $this->dateFilter = $dateFilter;

        $objectManager = ObjectManager::getInstance();
        $this->customOptionFactory = $customOptionFactory ?: $objectManager->get(CustomOptionFactory::class);
        $this->productLinkFactory = $productLinkFactory ?: $objectManager->get(ProductLinkFactory::class);
        $this->productRepository = $productRepository ?: $objectManager->get(ProductRepositoryInterface::class);
        $this->linkTypeProvider = $linkTypeProvider ?: $objectManager->get(LinkTypeProvider::class);
        $this->attributeFilter = $attributeFilter ?: $objectManager->get(AttributeFilter::class);
        $this->localeFormat = $localeFormat ?: $objectManager->get(FormatInterface::class);
        $this->productAuthorization = $productAuthorization ?? $objectManager->get(ProductAuthorization::class);
        $this->dateTimeFilter = $dateTimeFilter ?? $objectManager->get(DateTimeFilter::class);
        $this->categoryLinkFactory = $categoryLinkFactory ?? $objectManager->get(CategoryLinkInterfaceFactory::class);
    }

    /**
     * Initialize product from data
     *
     * @param Product $product
     * @param array $productData
     * @return Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 101.0.0
     */
    public function initializeFromData(Product $product, array $productData)
    {
        unset($productData['custom_attributes'], $productData['extension_attributes']);

        if ($productData) {
            $stockData = isset($productData['stock_data']) ? $productData['stock_data'] : [];
            $productData['stock_data'] = $this->stockFilter->filter($stockData);
        }

        $productData = $this->normalize($productData);

        if (!empty($productData['is_downloadable'])) {
            $productData['product_has_weight'] = 0;
        }

        foreach ($productData as $key => $value) {
            if (in_array($key, $this->productDataKeys) && $value === '') {
                $productData[$key] = null;
            }
        }

        foreach (['category_ids', 'website_ids'] as $field) {
            if (!isset($productData[$field])) {
                $productData[$field] = [];
            }
        }
        $productData['website_ids'] = $this->filterWebsiteIds($productData['website_ids']);

        $wasLockedMedia = false;
        if ($product->isLockedAttribute('media')) {
            $product->unlockAttribute('media');
            $wasLockedMedia = true;
        }

        $dateFieldFilters = [];
        $attributes = $product->getAttributes();
        foreach ($attributes as $attrKey => $attribute) {
            if ($attribute->getBackend()->getType() == 'datetime') {
                if (array_key_exists($attrKey, $productData) && $productData[$attrKey] != '') {
                    $dateFieldFilters[$attrKey] = $this->dateTimeFilter;
                }
            }
        }

        $inputFilter = new Zend_Filter_Input($dateFieldFilters, [], $productData);
        $productData = $inputFilter->getUnescaped();

        if (isset($productData['options'])) {
            $productOptions = $productData['options'];
            unset($productData['options']);
        } else {
            $productOptions = [];
        }
        $productData['tier_price'] = isset($productData['tier_price']) ? $productData['tier_price'] : [];

        $useDefaults = (array) $this->request->getPost('use_default', []);
        $productData = $this->attributeFilter->prepareProductAttributes($product, $productData, $useDefaults);
        $product->addData($productData);

        if ($wasLockedMedia) {
            $product->lockAttribute('media');
        }

        $product = $this->setProductLinks($product);
        $product = $this->fillProductOptions($product, $productOptions);
        $this->setCategoryLinks($product);

        $product->setCanSaveCustomOptions(
            !empty($productData['affect_product_custom_options']) && !$product->getOptionsReadonly()
        );

        return $product;
    }

    /**
     * Initialize product before saving
     *
     * @param Product $product
     * @return Product
     */
    public function initialize(Product $product)
    {
        $productData = $this->request->getPost('product', []);
        $product = $this->initializeFromData($product, $productData);
        $this->productAuthorization->authorizeSavingOf($product);

        return $product;
    }

    /**
     * Setting product links
     *
     * @param Product $product
     * @return Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 101.0.0
     */
    protected function setProductLinks(Product $product)
    {
        $links = $this->getLinkResolver()->getLinks();

        $product->setProductLinks([]);

        $product = $this->productLinks->initializeLinks($product, $links);
        $productLinks = $product->getProductLinks();
        $linkTypes = [];

        /** @var ProductLinkTypeInterface $linkTypeObject */
        foreach ($this->linkTypeProvider->getItems() as $linkTypeObject) {
            $linkTypes[$linkTypeObject->getName()] = $product->getData($linkTypeObject->getName() . '_readonly');
        }

        // skip linkTypes that were already processed on initializeLinks plugins
        foreach ($productLinks as $productLink) {
            unset($linkTypes[$productLink->getLinkType()]);
        }

        foreach ($linkTypes as $linkType => $readonly) {
            if (isset($links[$linkType]) && !$readonly) {
                foreach ((array) $links[$linkType] as $linkData) {
                    if (empty($linkData['id'])) {
                        continue;
                    }

                    $linkProduct = $this->productRepository->getById($linkData['id']);
                    $link = $this->productLinkFactory->create();
                    $link->setSku($product->getSku())
                        ->setLinkedProductSku($linkProduct->getSku())
                        ->setLinkType($linkType)
                        ->setPosition(isset($linkData['position']) ? (int) $linkData['position'] : 0);
                    $productLinks[] = $link;
                }
            }
        }

        return $product->setProductLinks($productLinks);
    }

    /**
     * Internal normalization
     *
     * @param array $productData
     * @return array
     * @todo Remove this method
     * @since 101.0.0
     */
    protected function normalize(array $productData)
    {
        foreach ($productData as $key => $value) {
            if (is_scalar($value)) {
                if ($value === 'true') {
                    $productData[$key] = '1';
                } elseif ($value === 'false') {
                    $productData[$key] = '0';
                }
            } elseif (is_array($value)) {
                $productData[$key] = $this->normalize($value);
            }
        }

        return $productData;
    }

    /**
     * Merge product and default options for product
     *
     * @param array $productOptions product options
     * @param array $overwriteOptions default value options
     * @return array
     */
    public function mergeProductOptions($productOptions, $overwriteOptions)
    {
        if (!is_array($productOptions)) {
            return [];
        }

        if (!is_array($overwriteOptions)) {
            return $productOptions;
        }

        foreach ($productOptions as $optionIndex => $option) {
            $optionId = $option['option_id'];
            $option = $this->overwriteValue($optionId, $option, $overwriteOptions);

            if (isset($option['values']) && isset($overwriteOptions[$optionId]['values'])) {
                foreach ($option['values'] as $valueIndex => $value) {
                    if (isset($value['option_type_id'])) {
                        $valueId = $value['option_type_id'];
                        $value = $this->overwriteValue($valueId, $value, $overwriteOptions[$optionId]['values']);
                        $option['values'][$valueIndex] = $value;
                    }
                }
            }

            $productOptions[$optionIndex] = $option;
        }

        return $productOptions;
    }

    /**
     * Overwrite values of fields to default, if there are option id and field name in array overwriteOptions
     *
     * @param int $optionId
     * @param array $option
     * @param array $overwriteOptions
     * @return array
     */
    private function overwriteValue($optionId, $option, $overwriteOptions)
    {
        if (isset($overwriteOptions[$optionId])) {
            foreach ($overwriteOptions[$optionId] as $fieldName => $overwrite) {
                if ($overwrite && isset($option[$fieldName]) && isset($option['default_' . $fieldName])) {
                    $option[$fieldName] = $option['default_' . $fieldName];
                    if ('title' == $fieldName) {
                        $option['is_delete_store_title'] = 1;
                    }
                }
            }
        }

        return $option;
    }

    /**
     * Get link resolver instance
     *
     * @return LinkResolver
     * @deprecated 102.0.0
     */
    private function getLinkResolver()
    {
        if (!is_object($this->linkResolver)) {
            $this->linkResolver = ObjectManager::getInstance()->get(LinkResolver::class);
        }

        return $this->linkResolver;
    }

    /**
     * Remove ids of non selected websites from $websiteIds array and return filtered data
     *
     * $websiteIds parameter expects array with website ids as keys and 1 (selected) or 0 (non selected) as values
     * Only one id (default website ID) will be set to $websiteIds array when the single store mode is turned on
     *
     * @param array $websiteIds
     * @return array
     */
    private function filterWebsiteIds($websiteIds)
    {
        if (!$this->storeManager->isSingleStoreMode()) {
            $websiteIds = array_filter((array) $websiteIds);
        } else {
            $websiteIds[$this->storeManager->getWebsite(true)->getId()] = 1;
        }

        return $websiteIds;
    }

    /**
     * Fills $product with options from $productOptions array
     *
     * @param Product $product
     * @param array $productOptions
     * @return Product
     */
    private function fillProductOptions(Product $product, array $productOptions)
    {
        if ($product->getOptionsReadonly()) {
            return $product;
        }

        if (empty($productOptions)) {
            return $product->setOptions([]);
        }

        // mark custom options that should to fall back to default value
        $options = $this->mergeProductOptions(
            $productOptions,
            $this->request->getPost('options_use_default')
        );
        $customOptions = [];
        foreach ($options as $customOptionData) {
            if (!empty($customOptionData['is_delete'])) {
                continue;
            }

            if (empty($customOptionData['option_id'])) {
                $customOptionData['option_id'] = null;
            }

            if (isset($customOptionData['values'])) {
                $customOptionData['values'] = array_filter(
                    $customOptionData['values'],
                    function ($valueData) {
                        return empty($valueData['is_delete']);
                    }
                );
            }

            if (isset($customOptionData['price'])) {
                // Make sure we're working with a number here and no localized value.
                $customOptionData['price'] = $this->localeFormat->getNumber($customOptionData['price']);
            }

            $customOption = $this->customOptionFactory->create(['data' => $customOptionData]);
            $customOption->setProductSku($product->getSku());
            $customOptions[] = $customOption;
        }

        return $product->setOptions($customOptions);
    }

    /**
     * Set category links based on initialized category ids
     *
     * @param Product $product
     */
    private function setCategoryLinks(Product $product): void
    {
        $extensionAttributes = $product->getExtensionAttributes();
        $categoryLinks = [];
        foreach ((array) $extensionAttributes->getCategoryLinks() as $categoryLink) {
            $categoryLinks[$categoryLink->getCategoryId()] = $categoryLink;
        }

        $newCategoryLinks = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            $categoryLink = $categoryLinks[$categoryId] ??
                $this->categoryLinkFactory->create()
                    ->setCategoryId($categoryId)
                    ->setPosition(0);
            $newCategoryLinks[] = $categoryLink;
        }

        $extensionAttributes->setCategoryLinks(!empty($newCategoryLinks) ? $newCategoryLinks : null);
        $product->setExtensionAttributes($extensionAttributes);
    }
}
