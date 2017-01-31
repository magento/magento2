<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

/**
 * Product initialization helper class
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
     * @var \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks
     */
    protected $productLinks;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param StockDataFilter $stockFilter
     * @param \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $productLinks
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
     * Initialize product before saving
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initialize(\Magento\Catalog\Model\Product $product)
    {
        $productData = $this->request->getPost('product', []);
        unset($productData['custom_attributes']);
        unset($productData['extension_attributes']);

        if ($productData) {
            $stockData = isset($productData['stock_data']) ? $productData['stock_data'] : [];
            $productData['stock_data'] = $this->stockFilter->filter($stockData);
        }

        foreach (['category_ids', 'website_ids'] as $field) {
            if (!isset($productData[$field])) {
                $productData[$field] = [];
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
                    $dateFieldFilters[$attrKey] = $this->dateFilter;
                }
            }
        }

        $inputFilter = new \Zend_Filter_Input($dateFieldFilters, [], $productData);
        $productData = $inputFilter->getUnescaped();

        $product->addData($productData);

        if ($wasLockedMedia) {
            $product->lockAttribute('media');
        }

        if ($this->storeManager->hasSingleStore()) {
            $product->setWebsiteIds([$this->storeManager->getStore(true)->getWebsite()->getId()]);
        }

        /**
         * Check "Use Default Value" checkboxes values
         */
        $useDefaults = $this->request->getPost('use_default');
        if ($useDefaults) {
            foreach ($useDefaults as $attributeCode) {
                $product->setData($attributeCode, false);
            }
        }

        $links = $this->request->getPost('links');
        $links = is_array($links) ? $links : [];
        $linkTypes = ['related', 'upsell', 'crosssell'];
        foreach ($linkTypes as $type) {
            if (isset($links[$type])) {
                $links[$type] = $this->jsHelper->decodeGridSerializedInput($links[$type]);
            }
        }
        $product = $this->productLinks->initializeLinks($product, $links);

        /**
         * Initialize product options
         */
        if (isset($productData['options']) && !$product->getOptionsReadonly()) {
            // mark custom options that should to fall back to default value
            $options = $this->mergeProductOptions(
                $productData['options'],
                $this->request->getPost('options_use_default')
            );
            foreach ($options as &$customOptionData) {
                if (isset($customOptionData['values'])) {
                    $customOptionData['values'] = array_filter($customOptionData['values'], function ($valueData) {
                        return !($valueData['option_type_id'] == '-1' && !empty($valueData['is_delete']));
                    });
                }
            }
            $product->setProductOptions($options);
        }

        $product->setCanSaveCustomOptions(
            (bool)$this->request->getPost('affect_product_custom_options') && !$product->getOptionsReadonly()
        );

        return $product;
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
            $productOptions = [];
        }
        if (is_array($overwriteOptions)) {
            $options = array_replace_recursive($productOptions, $overwriteOptions);
            array_walk_recursive($options, function (&$item) {
                if ($item === "") {
                    $item = null;
                }
            });
        } else {
            $options = $productOptions;
        }

        return $options;
    }
}
