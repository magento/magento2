<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * Catalog category helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Product extends \Magento\Framework\Url\Helper\Data
{
    public const XML_PATH_PRODUCT_URL_USE_CATEGORY = 'catalog/seo/product_use_categories';

    public const XML_PATH_USE_PRODUCT_CANONICAL_TAG = 'catalog/seo/product_canonical_tag';

    public const XML_PATH_AUTO_GENERATE_MASK = 'catalog/fields_masks';

    public const XML_PATH_APPLY_TRANSLITERATION_TO_URL = 'catalog/seo/product_url_transliteration';

    /**
     * Flag that shows if Magento has to check product to be saleable (enabled and/or inStock)
     *
     * @var boolean
     */
    protected $_skipSaleableCheck = false;

    /**
     * @var array
     */
    protected $_statuses;

    /**
     * @var mixed
     */
    protected $_priceBlock;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     */
    protected $_attributeConfig;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * Invalidate product category indexer params
     *
     * @var array
     */
    protected $_reindexProductCategoryIndexerData;

    /**
     * Invalidate price indexer params
     *
     * @var array
     */
    protected $_reindexPriceIndexerData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
     * @param array $reindexPriceIndexerData
     * @param array $reindexProductCategoryIndexerData
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Attribute\Config $attributeConfig,
        $reindexPriceIndexerData,
        $reindexProductCategoryIndexerData,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_attributeConfig = $attributeConfig;
        $this->_coreRegistry = $coreRegistry;
        $this->_assetRepo = $assetRepo;
        $this->_reindexPriceIndexerData = $reindexPriceIndexerData;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->_reindexProductCategoryIndexerData = $reindexProductCategoryIndexerData;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Retrieve data for price indexer update
     *
     * @param \Magento\Catalog\Model\Product|array $data
     * @return bool
     */
    public function isDataForPriceIndexerWasChanged($data)
    {
        if ($data instanceof ModelProduct) {
            foreach ($this->_reindexPriceIndexerData['byDataResult'] as $param) {
                if ($data->getData($param)) {
                    return true;
                }
            }
            foreach ($this->_reindexPriceIndexerData['byDataChange'] as $param) {
                if ($data->dataHasChangedFor($param)) {
                    return true;
                }
            }
        } elseif (is_array($data)) {
            foreach ($this->_reindexPriceIndexerData['byDataChange'] as $param) {
                if (isset($data[$param])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve data for product category indexer update
     *
     * @param \Magento\Catalog\Model\Product $data
     * @return bool
     */
    public function isDataForProductCategoryIndexerWasChanged(\Magento\Catalog\Model\Product $data)
    {
        foreach ($this->_reindexProductCategoryIndexerData['byDataChange'] as $param) {
            if ($data->dataHasChangedFor($param)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve product view page url
     *
     * @param int|ModelProduct $product
     * @return string|bool
     */
    public function getProductUrl($product)
    {
        if ($product instanceof ModelProduct) {
            return $product->getProductUrl();
        } elseif (is_numeric($product)) {
            return $this->productRepository->getById($product)->getProductUrl();
        }
        return false;
    }

    /**
     * Retrieve product price
     *
     * @param ModelProduct $product
     * @return float
     */
    public function getPrice($product)
    {
        return $product->getPrice();
    }

    /**
     * Retrieve product final price
     *
     * @param ModelProduct $product
     * @return float
     */
    public function getFinalPrice($product)
    {
        return $product->getFinalPrice();
    }

    /**
     * Retrieve base image url
     *
     * @param ModelProduct|\Magento\Framework\DataObject $product
     * @return string|bool
     */
    public function getImageUrl($product)
    {
        $url = false;
        $attribute = $product->getResource()->getAttribute('image');
        if (!$product->getImage()) {
            $url = $this->_assetRepo->getUrl('Magento_Catalog::images/product/placeholder/image.jpg');
        } elseif ($attribute) {
            $url = $attribute->getFrontend()->getUrl($product);
        }
        return $url;
    }

    /**
     * Retrieve small image url
     *
     * @param ModelProduct|\Magento\Framework\DataObject $product
     * @return string|bool
     */
    public function getSmallImageUrl($product)
    {
        $url = false;
        $attribute = $product->getResource()->getAttribute('small_image');
        if (!$product->getSmallImage()) {
            $url = $this->_assetRepo->getUrl('Magento_Catalog::images/product/placeholder/small_image.jpg');
        } elseif ($attribute) {
            $url = $attribute->getFrontend()->getUrl($product);
        }
        return $url;
    }

    /**
     * Retrieve thumbnail image url
     *
     * @param ModelProduct|\Magento\Framework\DataObject $product
     * @return string|bool
     */
    public function getThumbnailUrl($product)
    {
        $url = false;
        $attribute = $product->getResource()->getAttribute('thumbnail');
        if (!$product->getThumbnail()) {
            $url = $this->_assetRepo->getUrl('Magento_Catalog::images/product/placeholder/thumbnail.jpg');
        } elseif ($attribute) {
            $url = $attribute->getFrontend()->getUrl($product);
        }
        return $url;
    }

    /**
     * Retrieve email to friend url
     *
     * @param ModelProduct $product
     * @return string
     */
    public function getEmailToFriendUrl($product)
    {
        $categoryId = null;
        $category = $this->_coreRegistry->registry('current_category');
        if ($category) {
            $categoryId = $category->getId();
        }
        return $this->_getUrl('sendfriend/product/send', ['id' => $product->getId(), 'cat_id' => $categoryId]);
    }

    /**
     * Get statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        if (null === $this->_statuses) {
            $this->_statuses = [];
        }

        return $this->_statuses;
    }

    /**
     * Check if a product can be shown
     *
     * @param ModelProduct|int $product
     * @param string $where
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canShow($product, $where = 'catalog')
    {
        if (is_int($product)) {
            try {
                $product = $this->productRepository->getById($product);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        } else {
            if (!$product->getId()) {
                return false;
            }
        }
        return $product->isVisibleInCatalog() && $product->isVisibleInSiteVisibility();
    }

    /**
     * Check if <link rel="canonical"> can be used for product
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canUseCanonicalTag($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_USE_PRODUCT_CANONICAL_TAG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return information array of product attribute input types
     * Only a small number of settings returned, so we won't break anything in current data flow
     * As soon as development process goes on we need to add there all possible settings
     *
     * @param string $inputType
     * @return array
     */
    public function getAttributeInputTypes($inputType = null)
    {
        /**
         * @todo specify there all relations for properties depending on input type
         */
        $inputTypes = [
            'multiselect' => ['backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class],
            'boolean' => ['source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class],
        ];

        if ($inputType === null) {
            return $inputTypes;
        } else {
            if (isset($inputTypes[$inputType])) {
                return $inputTypes[$inputType];
            }
        }
        return [];
    }

    /**
     * Return default attribute backend model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeBackendModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['backend_model'])) {
            return $inputTypes[$inputType]['backend_model'];
        }
        return null;
    }

    /**
     * Return default attribute source model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeSourceModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['source_model'])) {
            return $inputTypes[$inputType]['source_model'];
        }
        return null;
    }

    /**
     * Inits product to be used for product controller actions and layouts
     * $params can have following data:
     *   'category_id' - id of category to check and append to product as current.
     *     If empty (except FALSE) - will be guessed (e.g. from last visited) to load as current.
     *
     * @param int $productId
     * @param \Magento\Framework\App\Action\Action $controller
     * @param \Magento\Framework\DataObject $params
     *
     * @return bool|ModelProduct
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initProduct($productId, $controller, $params = null)
    {
        // Prepare data for routine
        if (!$params) {
            $params = new \Magento\Framework\DataObject();
        }

        // Init and load product
        $this->_eventManager->dispatch(
            'catalog_controller_product_init_before',
            ['controller_action' => $controller, 'params' => $params]
        );

        if (!$productId) {
            return false;
        }

        try {
            $product = $this->productRepository->getById($productId, false, $this->_storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }

        if (!$this->canShow($product)) {
            return false;
        }
        if (!in_array($this->_storeManager->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }

        // Load product current category
        $categoryId = $params->getCategoryId();
        if (!$categoryId && $categoryId !== false) {
            $lastId = $this->_catalogSession->getLastVisitedCategoryId();
            if ($product->canBeShowInCategory($lastId)) {
                $categoryId = $lastId;
            }
        } elseif (!$product->canBeShowInCategory($categoryId)) {
            $categoryId = null;
        }

        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $e) {
                $category = null;
            }
            if ($category) {
                $product->setCategory($category);
                $this->_coreRegistry->register('current_category', $category);
            }
        }

        // Register current data and dispatch final events
        $this->_coreRegistry->register('current_product', $product);
        $this->_coreRegistry->register('product', $product);

        try {
            $this->_eventManager->dispatch(
                'catalog_controller_product_init_after',
                ['product' => $product, 'controller_action' => $controller]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return false;
        }

        return $product;
    }

    /**
     * Prepares product options by buyRequest: retrieves values and assigns them as default.
     *
     * Also parses and adds product management related values - e.g. qty
     *
     * @param ModelProduct $product
     * @param \Magento\Framework\DataObject $buyRequest
     * @return Product
     */
    public function prepareProductOptions($product, $buyRequest)
    {
        $optionValues = $product->processBuyRequest($buyRequest);
        $optionValues->setQty($buyRequest->getQty());
        $product->setPreconfiguredValues($optionValues);

        return $this;
    }

    /**
     * Process $buyRequest and sets its options before saving configuration to some product item.
     *
     * This method is used to attach additional parameters to processed buyRequest.
     *
     * $params holds parameters of what operation must be performed:
     * - 'current_config', \Magento\Framework\DataObject or array - current buyRequest
     *   that configures product in this item, used to restore currently attached files
     * - 'files_prefix': string[a-z0-9_] - prefix that was added at frontend to names of file inputs,
     *   so they won't intersect with other submitted options
     *
     * @param \Magento\Framework\DataObject|array $buyRequest
     * @param \Magento\Framework\DataObject|array $params
     * @return \Magento\Framework\DataObject
     */
    public function addParamsToBuyRequest($buyRequest, $params)
    {
        if (is_array($buyRequest)) {
            $buyRequest = new \Magento\Framework\DataObject($buyRequest);
        }
        if (is_array($params)) {
            $params = new \Magento\Framework\DataObject($params);
        }

        // Ensure that currentConfig goes as \Magento\Framework\DataObject - for easier work with it later
        $currentConfig = $params->getCurrentConfig();
        if ($currentConfig) {
            if (is_array($currentConfig)) {
                $params->setCurrentConfig(new \Magento\Framework\DataObject($currentConfig));
            } elseif (!$currentConfig instanceof \Magento\Framework\DataObject) {
                $params->unsCurrentConfig();
            }
        }

        /*
         * Notice that '_processing_params' must always be object to protect processing forged requests
         * where '_processing_params' comes in $buyRequest as array from user input
         */
        $processingParams = $buyRequest->getData('_processing_params');
        if (!$processingParams || !$processingParams instanceof \Magento\Framework\DataObject) {
            $processingParams = new \Magento\Framework\DataObject();
            $buyRequest->setData('_processing_params', $processingParams);
        }
        $processingParams->addData($params->getData());

        return $buyRequest;
    }

    /**
     * Set flag that shows if Magento has to check product to be saleable (enabled and/or inStock)
     *
     * @param bool $skipSaleableCheck
     * @return Product
     */
    public function setSkipSaleableCheck($skipSaleableCheck = false)
    {
        $this->_skipSaleableCheck = $skipSaleableCheck;
        return $this;
    }

    /**
     * Get flag that shows if Magento has to check product to be saleable (enabled and/or inStock)
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getSkipSaleableCheck()
    {
        return $this->_skipSaleableCheck;
    }

    /**
     * Get masks for auto generation of fields
     *
     * @return mixed
     */
    public function getFieldsAutogenerationMasks()
    {
        return $this->scopeConfig->getValue(Product::XML_PATH_AUTO_GENERATE_MASK, 'default');
    }

    /**
     * Retrieve list of attributes that allowed for autogeneration
     *
     * @return array
     */
    public function getAttributesAllowedForAutogeneration()
    {
        return $this->_attributeConfig->getAttributeNames('used_in_autogeneration');
    }
}
