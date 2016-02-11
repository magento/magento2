<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory as ProductLinkFactory;
use Magento\Catalog\Api\ProductRepositoryInterface\Proxy as ProductRepository;

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
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param StockDataFilter $stockFilter
     * @param \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $productLinks
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param CustomOptionFactory $customOptionFactory
     * @param ProductLinkFactory $productLinkFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StockDataFilter $stockFilter,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $productLinks,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        CustomOptionFactory $customOptionFactory,
        ProductLinkFactory $productLinkFactory,
        ProductRepository $productRepository
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->stockFilter = $stockFilter;
        $this->productLinks = $productLinks;
        $this->jsHelper = $jsHelper;
        $this->dateFilter = $dateFilter;
        $this->customOptionFactory = $customOptionFactory;
        $this->productLinkFactory = $productLinkFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Initialize product before saving
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function initialize(\Magento\Catalog\Model\Product $product)
    {
        $productData = $this->request->getPost('product');
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
        $productLinks = [];
        $savedLinksByType = [];
        foreach ($product->getProductLinks() as $link) {
            $savedLinksByType[$link->getLinkType()][] = $link;
        }
        $this->dropRelationProductsCache($product);

        $linkTypes = [
            'related' => $product->getRelatedReadonly(),
            'upsell' => $product->getUpsellReadonly(),
            'crosssell' => $product->getCrosssellReadonly()
        ];
        foreach ($linkTypes as $linkType => $readonly) {
            if (isset($links[$linkType]) && !$readonly) {
                foreach ($links[$linkType] as $linkId => $linkData) {
                    $linkProduct = $this->productRepository->getById($linkId);
                    $link = $this->productLinkFactory->create();
                    $link->setSku($product->getSku())
                        ->setLinkedProductSku($linkProduct->getSku())
                        ->setLinkType($linkType)
                        ->setPosition(isset($linkData['position']) ? (int)$linkData['position'] : 0);
                    $productLinks[] = $link;
                }
            } else {
                if (array_key_exists($linkType, $savedLinksByType)) {
                    $productLinks = array_merge($productLinks, $savedLinksByType[$linkType]);
                }
            }
            if (isset($savedLinksByType[$linkType])) {
                unset($savedLinksByType[$linkType]);
            }
        }

        foreach ($savedLinksByType as $links) {
            $productLinks = array_merge($productLinks, $links);
        }
        $product->setProductLinks($productLinks);

        /**
         * Initialize product options
         */
        if (isset($productData['options']) && !$product->getOptionsReadonly()) {
            // mark custom options that should to fall back to default value
            $options = $this->mergeProductOptions(
                $productData['options'],
                $this->request->getPost('options_use_default')
            );
            $customOptions = [];
            foreach ($options as $customOptionData) {
                if (!(bool)$customOptionData['is_delete']) {
                    $customOption = $this->customOptionFactory->create(['data' => $customOptionData]);
                    $customOption->setProductSku($product->getSku());
                    $customOption->setOptionId(null);
                    $customOptions[] = $customOption;
                }
            }
            $product->setOptions($customOptions);
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

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    private function dropRelationProductsCache(\Magento\Catalog\Model\Product $product)
    {
        $product->unsetData('up_sell_products');
        $product->unsetData('up_sell_products_ids');
        $product->unsetData('related_products');
        $product->unsetData('related_products_ids');
        $product->unsetData('cross_sell_products');
        $product->unsetData('cross_sell_products_ids');
    }
}
