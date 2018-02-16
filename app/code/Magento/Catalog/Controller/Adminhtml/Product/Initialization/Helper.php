<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\Backend\Helper\Js;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory as ProductLinkFactory;
use Magento\Catalog\Api\ProductRepositoryInterface\Proxy as ProductRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\Link\Resolver as LinkResolver;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Magento\Framework\Stdlib\DateTime\Filter\DateTime;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     *
     * @deprecated
     */
    protected $dateFilter;

    /**
     * @var CustomOptionFactory
     */
    protected $customOptionFactory;

    /**
     * @var ProductLinkFactory
     */
    protected $productLinkFactory;

    /**
     * @var ProductRepository
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
     * @var DateTime
     */
    private $dateTimeFilter;
    
    /**
     * @var LinkTypeProvider
     */
    private $linkTypeProvider;

    /**
     * Helper constructor.
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param StockDataFilter $stockFilter
     * @param ProductLinks $productLinks
     * @param Js $jsHelper
     * @param DateFilter $dateFilter
     * @param LinkTypeProvider|null $linkTypeProvider
     * @param CustomOptionFactory|null $customOptionFactory
     * @param ProductLinkFactory|null $productLinkFactory
     * @param ProductRepository|null $productRepository
     * @param DateTime|null $dateTimeFilter
     * @param LinkResolver|null $linkResolver
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        StockDataFilter $stockFilter,
        ProductLinks $productLinks,
        Js $jsHelper,
        DateFilter $dateFilter,
        LinkTypeProvider $linkTypeProvider = null,
        CustomOptionFactory $customOptionFactory = null,
        ProductLinkFactory $productLinkFactory = null,
        ProductRepository $productRepository = null,
        DateTime $dateTimeFilter = null,
        LinkResolver $linkResolver = null
    ) {
        if (null === $linkTypeProvider) {
            $linkTypeProvider = ObjectManager::getInstance()->get(LinkTypeProvider::class);
        }
        if (null === $customOptionFactory) {
            $customOptionFactory = ObjectManager::getInstance()->get(CustomOptionFactory::class);
        }
        if (null === $productLinkFactory) {
            $productLinkFactory = ObjectManager::getInstance()->get(ProductLinkFactory::class);
        }
        if (null === $productRepository) {
            $productRepository = ObjectManager::getInstance()->get(ProductRepository::class);
        }
        if (null === $dateTimeFilter) {
            $dateTimeFilter = ObjectManager::getInstance()->get(DateTime::class);
        }
        if (null === $linkResolver) {
            $linkResolver = ObjectManager::getInstance()->get(LinkResolver::class);
        }
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->stockFilter = $stockFilter;
        $this->productLinks = $productLinks;
        $this->jsHelper = $jsHelper;
        $this->dateFilter = $dateFilter;
        $this->linkTypeProvider = $linkTypeProvider;
        $this->customOptionFactory = $customOptionFactory;
        $this->productLinkFactory = $productLinkFactory;
        $this->productRepository = $productRepository;
        $this->dateTimeFilter = $dateTimeFilter;
        $this->linkResolver = $linkResolver;
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
     */
    public function initializeFromData(Product $product, array $productData)
    {
        unset($productData['custom_attributes']);
        unset($productData['extension_attributes']);

        if ($productData) {
            $stockData = isset($productData['stock_data']) ? $productData['stock_data'] : [];
            $productData['stock_data'] = $this->stockFilter->filter($stockData);
        }

        $productData = $this->normalize($productData);

        if (!empty($productData['is_downloadable'])) {
            $productData['product_has_weight'] = 0;
        }

        foreach (['category_ids', 'website_ids'] as $field) {
            if (!isset($productData[$field])) {
                $productData[$field] = [];
            }
        }

        foreach ($productData['website_ids'] as $websiteId => $checkboxValue) {
            if (!$checkboxValue) {
                unset($productData['website_ids'][$websiteId]);
            }
        }

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

        $inputFilter = new \Zend_Filter_Input($dateFieldFilters, [], $productData);
        $productData = $inputFilter->getUnescaped();

        if (isset($productData['options'])) {
            $productOptions = $productData['options'];
            unset($productData['options']);
        } else {
            $productOptions = [];
        }

        $product->addData($productData);

        if ($wasLockedMedia) {
            $product->lockAttribute('media');
        }

        /**
         * Check "Use Default Value" checkboxes values
         */
        $useDefaults = (array)$this->request->getPost('use_default', []);

        foreach ($useDefaults as $attributeCode => $useDefaultState) {
            if ($useDefaultState) {
                $product->setData($attributeCode, null);
                // UI component sends value even if field is disabled, so 'Use Config Settings' must be reset to false
                if ($product->hasData('use_config_' . $attributeCode)) {
                    $product->setData('use_config_' . $attributeCode, false);
                }
            }
        }

        $product = $this->setProductLinks($product);
        $product = $this->fillProductOptions($product, $productOptions);

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
        return $this->initializeFromData($product, $productData);
    }

    /**
     * Setting product links
     *
     * @param Product $product
     * @return Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setProductLinks(Product $product)
    {
        $links = $this->linkResolver->getLinks();

        $product->setProductLinks([]);

        $product = $this->productLinks->initializeLinks($product, $links);
        $productLinks = $product->getProductLinks();
        $linkTypes = [];
        
        /** @var \Magento\Catalog\Api\Data\ProductLinkTypeInterface $linkTypeObject */
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
                        ->setPosition(isset($linkData['position']) ? (int)$linkData['position'] : 0);
                    $productLinks[] = $link;
                }
            }
        }

        return $product->setProductLinks($productLinks);
    }

    /**
     * Internal normalization
     * TODO: Remove this method
     *
     * @param array $productData
     * @return array
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
            $option = $this->overwriteValue(
                $optionId,
                $option,
                $overwriteOptions
            );

            if (isset($option['values']) && isset($overwriteOptions[$optionId]['values'])) {
                foreach ($option['values'] as $valueIndex => $value) {
                    if (isset($value['option_type_id'])) {
                        $valueId = $value['option_type_id'];
                        $value = $this->overwriteValue(
                            $valueId,
                            $value,
                            $overwriteOptions[$optionId]['values']
                        );

                        $option['values'][$valueIndex] = $value;
                    }
                }
            }

            $productOptions[$optionIndex] = $option;
        }

        return $productOptions;
    }

    /**
     * Overwrite values of fields to default, if there are option id and field name in array overwriteOptions.
     *
     * @param int   $optionId
     * @param array $option
     * @param array $overwriteOptions
     *
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
            $customOption = $this->customOptionFactory->create(
                ['data' => $customOptionData]
            );
            $customOption->setProductSku($product->getSku());
            $customOptions[] = $customOption;
        }

        return $product->setOptions($customOptions);
    }
}
