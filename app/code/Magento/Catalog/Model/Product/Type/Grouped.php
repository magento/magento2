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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Grouped product type implementation
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Type;

class Grouped extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_CODE = 'grouped';

    /**
     * Cache key for Associated Products
     *
     * @var string
     */
    protected $_keyAssociatedProducts   = '_cache_instance_associated_products';

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
    protected $_keyStatusFilters        = '_cache_instance_status_filters';

    /**
     * Product is composite properties
     *
     * @var bool
     */
    protected $_isComposite             = true;

    /**
     * Product is configurable
     *
     * @var bool
     */
    protected $_canConfigure            = true;

    /**
     * Catalog product status
     *
     * @var \Magento\Catalog\Model\Product\Status
     */
    protected $_catalogProductStatus;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog product link
     *
     * @var \Magento\Catalog\Model\Resource\Product\Link
     */
    protected $_catalogProductLink;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Catalog\Model\Resource\Product\Link $catalogProductLink
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Status $catalogProductStatus
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Core\Model\Logger $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Catalog\Model\Resource\Product\Link $catalogProductLink,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Status $catalogProductStatus,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Filesystem $filesystem,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Core\Model\Logger $logger,
        array $data = array()
    ) {
        $this->_catalogProductLink = $catalogProductLink;
        $this->_storeManager = $storeManager;
        $this->_catalogProductStatus = $catalogProductStatus;
        parent::__construct($productFactory, $catalogProductOption, $eavConfig, $catalogProductType,
            $eventManager, $coreData, $fileStorageDb, $filesystem, $coreRegistry, $logger, $data);
    }

    /**
     * Return relation info about used products
     *
     * @return \Magento\Object Object with information data
     */
    public function getRelationInfo()
    {
        $info = new \Magento\Object();
        $info->setTable('catalog_product_link')
            ->setParentFieldName('product_id')
            ->setChildFieldName('linked_product_id')
            ->setWhere('link_type_id=' . \Magento\Catalog\Model\Product\Link::LINK_TYPE_GROUPED);
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
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return $this->_catalogProductLink
            ->getChildrenIds($parentId,
                \Magento\Catalog\Model\Product\Link::LINK_TYPE_GROUPED);
    }

    /**
     * Retrieve parent ids array by requered child
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        return $this->_catalogProductLink
            ->getParentIdsByChild($childId,
                \Magento\Catalog\Model\Product\Link::LINK_TYPE_GROUPED);
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
            $associatedProducts = array();

            if (!$this->_storeManager->getStore()->isAdmin()) {
                $this->setSaleableStatus($product);
            }

            $collection = $this->getAssociatedProductCollection($product)
                ->addAttributeToSelect('*')
                ->addFilterByRequiredOptions()
                ->setPositionOrder()
                ->addStoreFilter($this->getStoreFilter($product))
                ->addAttributeToFilter('status', array('in' => $this->getStatusFilters($product)));

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
     * @return \Magento\Catalog\Model\Product\Type\Grouped
     */
    public function addStatusFilter($status, $product)
    {
        $statusFilters = $product->getData($this->_keyStatusFilters);
        if (!is_array($statusFilters)) {
            $statusFilters = array();
        }

        $statusFilters[] = $status;
        $product->setData($this->_keyStatusFilters, $statusFilters);

        return $this;
    }

    /**
     * Set only saleable filter
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product\Type\Grouped
     */
    public function setSaleableStatus($product)
    {
        $product->setData($this->_keyStatusFilters,
            $this->_catalogProductStatus->getSaleableStatusIds());
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
            return array(
                \Magento\Catalog\Model\Product\Status::STATUS_ENABLED,
                \Magento\Catalog\Model\Product\Status::STATUS_DISABLED
            );
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
            $associatedProductIds = array();
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
        $collection = $product->getLinkInstance()->useGroupedLinks()
            ->getProductCollection()
            ->setFlag('require_stock_items', true)
            ->setFlag('product_children', true)
            ->setIsStrongMode();
        $collection->setProduct($product);
        return $collection;
    }

    /**
     * Check is product available for sale
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        $salable = parent::isSalable($product);
        if (!is_null($salable)) {
            return $salable;
        }

        $salable = false;
        foreach ($this->getAssociatedProducts($product) as $associatedProduct) {
            $salable = $salable || $associatedProduct->isSalable();
        }
        return $salable;
    }

    /**
     * Save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product\Type\Grouped
     */
    public function save($product)
    {
        parent::save($product);
        $product->getLinkInstance()->saveGroupedLinks($product);
        return $this;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and add logic specific to Grouped product type.
     *
     * @param \Magento\Object $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(\Magento\Object $buyRequest, $product, $processMode)
    {
        $productsInfo = $buyRequest->getSuperGroup();
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        if (!$isStrictProcessMode || (!empty($productsInfo) && is_array($productsInfo))) {
            $products = array();
            $associatedProductsInfo = array();
            $associatedProducts = $this->getAssociatedProducts($product);
            if ($associatedProducts || !$isStrictProcessMode) {
                foreach ($associatedProducts as $subProduct) {
                    $subProductId = $subProduct->getId();
                    if(isset($productsInfo[$subProductId])) {
                        $qty = $productsInfo[$subProductId];
                        if (!empty($qty) && is_numeric($qty)) {

                            $_result = $subProduct->getTypeInstance()
                                ->_prepareProduct($buyRequest, $subProduct, $processMode);
                            if (is_string($_result) && !is_array($_result)) {
                                return $_result;
                            }

                            if (!isset($_result[0])) {
                                return __('We cannot process the item.');
                            }

                            if ($isStrictProcessMode) {
                                $_result[0]->setCartQty($qty);
                                $_result[0]->addCustomOption('product_type', self::TYPE_CODE, $product);
                                $_result[0]->addCustomOption('info_buyRequest',
                                    serialize(array(
                                        'super_product_config' => array(
                                            'product_type'  => self::TYPE_CODE,
                                            'product_id'    => $product->getId()
                                        )
                                    ))
                                );
                                $products[] = $_result[0];
                            } else {
                                $associatedProductsInfo[] = array($subProductId => $qty);
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
        return array($this->getAssociatedProducts($product));
    }

    /**
     * Prepare selected qty for grouped product's options
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $superGroup = $buyRequest->getSuperGroup();
        $superGroup = (is_array($superGroup)) ? array_filter($superGroup, 'intval') : array();

        $options = array('super_group' => $superGroup);

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
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
}
