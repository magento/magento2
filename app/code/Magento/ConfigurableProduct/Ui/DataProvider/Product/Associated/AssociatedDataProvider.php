<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\DataProvider\Product\Associated;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\StoreRepositoryInterface;
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
     * @var StoreInterface
     */
    private $store;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Configurable $configurableType
     * @param ProductFactory $productFactory
     * @param LocatorInterface $locator
     * @param StoreRepositoryInterface $storeRepository
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Configurable $configurableType,
        ProductFactory $productFactory,
        LocatorInterface $locator,
        StoreRepositoryInterface $storeRepository,
        RequestInterface $request,
        array $meta = [],
        array $data = [],
        array $addFieldStrategies = [],
        array $addFilterStrategies = []
    ) {
        $this->_configurableType = $configurableType;
        $this->productData = $productFactory;
        $this->locator = $locator;
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
    }

    /**
     * Filtered Collection
     *
     * @return Collection|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
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
                \Magento\Catalog\Model\Product\Type::TYPE_DOWNLOADABLE
            ]]
        );

        $product = $this->_getProduct();
        foreach ((array)$this->_configurableType->getConfigurableAttributesAsArray($product) as $attribute) {
            $collection->addAttributeToSelect($attribute['attribute_code']);
            $collection->addAttributeToFilter($attribute['attribute_code'], ['notnull' => 1]);
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
     * @return StoreInterface|\Magento\Store\Api\Data\StoreInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
