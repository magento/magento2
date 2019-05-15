<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Associated;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ProductFactory;

/**
* Class AssociatedDataProvider
*
* @api
* @since 100.0.2
*/
class AssociatedDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{

    /**
     * @var Magento\Catalog\Model\ProductFactory
     */
    protected $productData;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var PoolInterface
     */
    private $modifiersPool;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $modifiersPool
     */
    public function __construct(
        Configurable $configurableType,
        ProductFactory $productFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        LocatorInterface $locator,
        PoolInterface $modifiersPool = null,
        StoreRepositoryInterface $storeRepository,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request
    )
    {
        $this->_configurableType = $configurableType;
        $this->productData = $productFactory;
        $this->locator = $locator;
        $this->_coreRegistry = $registry;
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $collectionFactory, $addFieldStrategies, $addFilterStrategies, $meta, $data);
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();
        $collection->addAttributeToSelect('status');


        if ($this->getStore()) {
            $collection->setStore($this->getStore());
        }

        $collection->addFieldToFilter('type_id',
            ['in' => [
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                \Magento\Catalog\Model\Product\Type::TYPE_DOWNLOADABLE
            ]]);

        $product = $this->_getProduct();
        foreach ((array)$this->_configurableType->getConfigurableAttributesAsArray($product) as $attribute) {
            $collection->addAttributeToSelect($attribute['attribute_code']);
            $collection->addAttributeToFilter($attribute['attribute_code'], array('notnull' => 1));
        }

        return $collection;
    }

    /**
     * Get product
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _getProduct()
    {
        $product_id = (int) $this->request->getParam('id');
        return $this->productData->create()->load($product_id);
    }

    /**
     * Retrieve store
     *
     * @return StoreInterface|null
     * @since 101.0.0
     */
    protected function getStore()
    {
        if (null !== $this->store) {
            return $this->store;
        }

        if (!($storeId = $this->request->getParam('current_store_id'))) {
            return null;
        }

        return $this->store = $this->storeRepository->getById($storeId);
    }

}
