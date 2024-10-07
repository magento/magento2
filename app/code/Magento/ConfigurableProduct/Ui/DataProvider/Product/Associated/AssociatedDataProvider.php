<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Associated;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class AssociatedDataProvider
 *
 */
class AssociatedDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var Configurable
     */
    protected $configurableType;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;


    /**
     * AssociatedDataProvider constructor.
     * @param Configurable $configurableType
     * @param ProductFactory $productFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param LocatorInterface $locator
     * @param PoolInterface|null $modifiersPool
     * @param StoreRepositoryInterface $storeRepository
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     */
    public function __construct(
        Configurable $configurableType,
        ProductFactory $productFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        LocatorInterface $locator,
        PoolInterface $modifiersPool = null,
        StoreRepositoryInterface $storeRepository,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request
    ) {
        $this->_configurableType = $configurableType;
        $this->productFactory= $productFactory;
        $this->locator = $locator;
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $collectionFactory, $addFieldStrategies, $addFilterStrategies, $meta, $data);
    }

    /**
     * @return Collection|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();
        $collection->addAttributeToSelect('status');


        if ($this->getStore()) {
            $collection->setStore($this->getStore());
        }

        $collection->addFieldToFilter(
            'type_id',
            ['in' => [
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ]]
        );

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
    private function _getProduct()
    {
        $product_id = (int) $this->request->getParam('id');
        return $this->productFactory->create()->load($product_id);
    }

    /**
     * Retrieve store
     *
     * @return StoreInterface|null
     * @since 101.0.0
     */
    private function getStore()
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
