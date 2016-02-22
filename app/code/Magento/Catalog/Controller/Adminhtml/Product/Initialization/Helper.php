<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory as ProductLinkFactory;
use Magento\Catalog\Api\ProductRepositoryInterface\Proxy as ProductRepository;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;

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
     * @var ProductLinks
     */
    protected $productLinks;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param StockDataFilter $stockFilter
     * @param ProductLinks $productLinks
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param CustomOptionFactory $customOptionFactory
     * @param ProductLinkFactory $productLinkFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StockDataFilter $stockFilter,
        ProductLinks $productLinks,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        CustomOptionFactory $customOptionFactory,
        ProductLinkFactory $productLinkFactory,
        ProductRepository $productRepository
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->stockFilter = $stockFilter;
        $this->productLinks = $productLinks;
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
        $productData = (array)$this->request->getPost('product', []);
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
        $useDefaults = (array)$this->request->getPost('use_default', []);

        foreach ($useDefaults as $attributeCode => $useDefaultState) {
            if ($useDefaultState) {
                $product->setData($attributeCode, false);
            }
        }

        $product = $this->setProductLinks($product);

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
                if (empty($customOptionData['is_delete'])) {
                    $customOption = $this->customOptionFactory->create(['data' => $customOptionData]);
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
     * Setting product links
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setProductLinks(\Magento\Catalog\Model\Product $product)
    {
        $links = (array)$this->request->getParam('links', []);

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
