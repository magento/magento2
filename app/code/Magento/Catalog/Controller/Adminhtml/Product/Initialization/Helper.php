<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory as ProductLinkFactory;
use Magento\Catalog\Api\ProductRepositoryInterface\Proxy as ProductRepository;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\Link\Resolver as LinkResolver;
use Magento\Framework\App\ObjectManager;

/**
 * Class Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Helper
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StockDataFilter
     */
    protected $stockFilter;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
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
     * @var \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     */
    private $dateTimeFilter;

    /**
     * Helper constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param StockDataFilter $stockFilter
     * @param ProductLinks $productLinks
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StockDataFilter $stockFilter,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $productLinks,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->stockFilter = $stockFilter;
        $this->productLinks = $productLinks;
        $this->jsHelper = $jsHelper;
        $this->dateFilter = $dateFilter;
    }

    /**
     * Initialize product from data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $productData
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function initializeFromData(\Magento\Catalog\Model\Product $product, array $productData)
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
                    $dateFieldFilters[$attrKey] = $this->getDateTimeFilter();
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

        /**
         * Initialize product options
         */
        if ($productOptions && !$product->getOptionsReadonly()) {
            // mark custom options that should to fall back to default value
            $options = $this->mergeProductOptions(
                $productOptions,
                $this->request->getPost('options_use_default')
            );
            $customOptions = [];
            foreach ($options as $customOptionData) {
                if (empty($customOptionData['is_delete'])) {
                    if (isset($customOptionData['values'])) {
                        $customOptionData['values'] = array_filter($customOptionData['values'], function ($valueData) {
                            return empty($valueData['is_delete']);
                        });
                    }
                    $customOption = $this->getCustomOptionFactory()->create(['data' => $customOptionData]);
                    $customOption->setProductSku($product->getSku());
                    $customOption->setOptionId(null);
                    $customOptions[] = $customOption;
                }
            }
            $product->setOptions($customOptions);
        }

        $product->setCanSaveCustomOptions(
            !empty($productData['affect_product_custom_options']) && !$product->getOptionsReadonly()
        );

        return $product;
    }

    /**
     * Initialize product before saving
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function initialize(\Magento\Catalog\Model\Product $product)
    {
        $productData = $this->request->getPost('product', []);
        return $this->initializeFromData($product, $productData);
    }

    /**
     * Setting product links
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setProductLinks(\Magento\Catalog\Model\Product $product)
    {
        $links = $this->getLinkResolver()->getLinks();

        $product->setProductLinks([]);

        $product = $this->productLinks->initializeLinks($product, $links);
        $productLinks = $product->getProductLinks();
        $linkTypes = [
            'related' => $product->getRelatedReadonly(),
            'upsell' => $product->getUpsellReadonly(),
            'crosssell' => $product->getCrosssellReadonly()
        ];

        foreach ($linkTypes as $linkType => $readonly) {
            if (isset($links[$linkType]) && !$readonly) {
                foreach ((array) $links[$linkType] as $linkData) {
                    if (empty($linkData['id'])) {
                        continue;
                    }

                    $linkProduct = $this->getProductRepository()->getById($linkData['id']);
                    $link = $this->getProductLinkFactory()->create();
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

        foreach ($productOptions as $index => $option) {
            $optionId = $option['option_id'];

            if (!isset($overwriteOptions[$optionId])) {
                continue;
            }

            foreach ($overwriteOptions[$optionId] as $fieldName => $overwrite) {
                if ($overwrite && isset($option[$fieldName]) && isset($option['default_' . $fieldName])) {
                    $productOptions[$index][$fieldName] = $option['default_' . $fieldName];
                }
            }
        }

        return $productOptions;
    }

    /**
     * @return CustomOptionFactory
     */
    private function getCustomOptionFactory()
    {
        if (null === $this->customOptionFactory) {
            $this->customOptionFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory');
        }
        return $this->customOptionFactory;
    }

    /**
     * @return ProductLinkFactory
     */
    private function getProductLinkFactory()
    {
        if (null === $this->productLinkFactory) {
            $this->productLinkFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Api\Data\ProductLinkInterfaceFactory');
        }
        return $this->productLinkFactory;
    }

    /**
     * @return ProductRepository
     */
    private function getProductRepository()
    {
        if (null === $this->productRepository) {
            $this->productRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Api\ProductRepositoryInterface\Proxy');
        }
        return $this->productRepository;
    }

    /**
     * @deprecated
     * @return LinkResolver
     */
    private function getLinkResolver()
    {
        if (!is_object($this->linkResolver)) {
            $this->linkResolver = ObjectManager::getInstance()->get(LinkResolver::class);
        }
        return $this->linkResolver;
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     *
     * @deprecated
     */
    private function getDateTimeFilter()
    {
        if ($this->dateTimeFilter === null) {
            $this->dateTimeFilter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Stdlib\DateTime\Filter\DateTime::class);
        }
        return $this->dateTimeFilter;
    }
}
