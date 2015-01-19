<?php
/**
 * Grouped product type implementation
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grouped extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_CODE = 'grouped';

    /**
     * Cache key for Associated Products
     *
     * @var string
     */
    protected $_keyAssociatedProducts = '_cache_instance_associated_products';

    /**
     * Cache key for Associated Product Ids
     *
     * @var string
     */
    protected $_keyAssociatedProductIds = '_cache_instance_associated_product_ids';

    /**
     * Cache key for Status Filters
     *
     * @var string
     */
    protected $_keyStatusFilters = '_cache_instance_status_filters';

    /**
     * Product is composite properties
     *
     * @var bool
     */
    protected $_isComposite = true;

    /**
     * Product is possible to configure
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * Catalog product status
     *
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_catalogProductStatus;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog product link
     *
     * @var \Magento\GroupedProduct\Model\Resource\Product\Link
     */
    protected $productLinks;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /** @var \Magento\Msrp\Helper\Data  */
    protected $msrpData;

    /**
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\GroupedProduct\Model\Resource\Product\Link $catalogProductLink
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Msrp\Helper\Data $msrpData
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\GroupedProduct\Model\Resource\Product\Link $catalogProductLink,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Framework\App\State $appState,
        \Magento\Msrp\Helper\Data $msrpData
    ) {
        $this->productLinks = $catalogProductLink;
        $this->_storeManager = $storeManager;
        $this->_catalogProductStatus = $catalogProductStatus;
        $this->_appState = $appState;
        $this->msrpData = $msrpData;
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $coreData,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository
        );
    }

    /**
     * Return relation info about used products
     *
     * @return \Magento\Framework\Object Object with information data
     */
    public function getRelationInfo()
    {
        $info = new \Magento\Framework\Object();
        $info->setTable(
            'catalog_product_link'
        )->setParentFieldName(
            'product_id'
        )->setChildFieldName(
            'linked_product_id'
        )->setWhere(
            'link_type_id=' . \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED
        );
        return $info;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return $this->productLinks->getChildrenIds(
            $parentId,
            \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED
        );
    }

    /**
     * Retrieve parent ids array by requested child
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        return $this->productLinks->getParentIdsByChild(
            $childId,
            \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED
        );
    }

    /**
     * Retrieve array of associated products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAssociatedProducts($product)
    {
        if (!$product->hasData($this->_keyAssociatedProducts)) {
            $associatedProducts = [];

            $this->setSaleableStatus($product);

            $collection = $this->getAssociatedProductCollection(
                $product
            )->addAttributeToSelect(
                '*'
            )->addFilterByRequiredOptions()->setPositionOrder()->addStoreFilter(
                $this->getStoreFilter($product)
            )->addAttributeToFilter(
                'status',
                ['in' => $this->getStatusFilters($product)]
            );

            foreach ($collection as $item) {
                $associatedProducts[] = $item;
            }

            $product->setData($this->_keyAssociatedProducts, $associatedProducts);
        }
        return $product->getData($this->_keyAssociatedProducts);
    }

    /**
     * Add status filter to collection
     *
     * @param  int $status
     * @param  \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function addStatusFilter($status, $product)
    {
        $statusFilters = $product->getData($this->_keyStatusFilters);
        if (!is_array($statusFilters)) {
            $statusFilters = [];
        }

        $statusFilters[] = $status;
        $product->setData($this->_keyStatusFilters, $statusFilters);

        return $this;
    }

    /**
     * Set only saleable filter
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setSaleableStatus($product)
    {
        $product->setData($this->_keyStatusFilters, $this->_catalogProductStatus->getSaleableStatusIds());
        return $this;
    }

    /**
     * Return all assigned status filters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getStatusFilters($product)
    {
        if (!$product->hasData($this->_keyStatusFilters)) {
            return [
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
            ];
        }
        return $product->getData($this->_keyStatusFilters);
    }

    /**
     * Retrieve related products identifiers
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAssociatedProductIds($product)
    {
        if (!$product->hasData($this->_keyAssociatedProductIds)) {
            $associatedProductIds = [];
            /** @var $item \Magento\Catalog\Model\Product */
            foreach ($this->getAssociatedProducts($product) as $item) {
                $associatedProductIds[] = $item->getId();
            }
            $product->setData($this->_keyAssociatedProductIds, $associatedProductIds);
        }
        return $product->getData($this->_keyAssociatedProductIds);
    }

    /**
     * Retrieve collection of associated products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Resource\Product\Link\Product\Collection
     */
    public function getAssociatedProductCollection($product)
    {
        /** @var \Magento\Catalog\Model\Product\Link  $links */
        $links = $product->getLinkInstance();
        $links->setLinkTypeId(\Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED);
        $collection = $links->getProductCollection()->setFlag(
            'require_stock_items',
            true
        )->setFlag(
            'product_children',
            true
        )->setIsStrongMode();
        $collection->setProduct($product);
        return $collection;
    }

    /**
     * Save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function save($product)
    {
        parent::save($product);

        $data = $product->getGroupedLinkData();
        if (!is_null($data)) {
            $this->productLinks->saveGroupedLinks($product, $data);
        }
        return $this;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and add logic specific to Grouped product type.
     *
     * @param \Magento\Framework\Object $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareProduct(\Magento\Framework\Object $buyRequest, $product, $processMode)
    {
        $productsInfo = $buyRequest->getSuperGroup();
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        if (!$isStrictProcessMode || !empty($productsInfo) && is_array($productsInfo)) {
            $products = [];
            $associatedProductsInfo = [];
            $associatedProducts = $this->getAssociatedProducts($product);
            if ($associatedProducts || !$isStrictProcessMode) {
                foreach ($associatedProducts as $subProduct) {
                    $subProductId = $subProduct->getId();
                    if (isset($productsInfo[$subProductId])) {
                        $qty = $productsInfo[$subProductId];
                        if (!empty($qty) && is_numeric($qty)) {
                            $_result = $subProduct->getTypeInstance()->_prepareProduct(
                                $buyRequest,
                                $subProduct,
                                $processMode
                            );
                            if (is_string($_result) && !is_array($_result)) {
                                return $_result;
                            }

                            if (!isset($_result[0])) {
                                return __('We cannot process the item.');
                            }

                            if ($isStrictProcessMode) {
                                $_result[0]->setCartQty($qty);
                                $_result[0]->addCustomOption(
                                    'info_buyRequest',
                                    serialize(
                                        [
                                            'super_product_config' => [
                                                'product_type' => self::TYPE_CODE,
                                                'product_id' => $product->getId(),
                                            ],
                                        ]
                                    )
                                );
                                $products[] = $_result[0];
                            } else {
                                $associatedProductsInfo[] = [$subProductId => $qty];
                                $product->addCustomOption('associated_product_' . $subProductId, $qty);
                            }
                        }
                    }
                }
            }

            if (!$isStrictProcessMode || count($associatedProductsInfo)) {
                $product->addCustomOption('product_type', self::TYPE_CODE, $product);
                $product->addCustomOption('info_buyRequest', serialize($buyRequest->getData()));

                $products[] = $product;
            }

            if (count($products)) {
                return $products;
            }
        }

        return __('Please specify the quantity of product(s).');
    }

    /**
     * Retrieve products divided into groups required to purchase
     * At least one product in each group has to be purchased
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductsToPurchaseByReqGroups($product)
    {
        return [$this->getAssociatedProducts($product)];
    }

    /**
     * Prepare selected qty for grouped product's options
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Framework\Object $buyRequest
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $superGroup = $buyRequest->getSuperGroup();
        $superGroup = is_array($superGroup) ? array_filter($superGroup, 'intval') : [];

        $options = ['super_group' => $superGroup];

        return $options;
    }

    /**
     * Check that product of this type has weight
     *
     * @return bool
     */
    public function hasWeight()
    {
        return false;
    }

    /**
     * Delete data specific for Grouped product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($product)
    {
        if ($product->hasData('product_options')) {
            throw new \Exception('Custom options for grouped product type are not supported');
        }
        return parent::beforeSave($product);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function getChildrenMsrp(\Magento\Catalog\Model\Product $product)
    {
        $prices = [];
        foreach ($this->getAssociatedProducts($product) as $item) {
            if ($item->getMsrp() !== null) {
                $prices[] = $item->getMsrp();
            }
        }
        return $prices ? min($prices) : 0;
    }
}
