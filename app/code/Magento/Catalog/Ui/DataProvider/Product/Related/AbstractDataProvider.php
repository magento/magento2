<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Related;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class AbstractDataProvider
 *
 * @api
 * @since 101.0.0
 */
abstract class AbstractDataProvider extends ProductDataProvider
{
    /**
     * @var RequestInterface
     * @since 101.0.0
     */
    protected $request;

    /**
     * @var ProductRepositoryInterface
     * @since 101.0.0
     */
    protected $productRepository;

    /**
     * @var StoreRepositoryInterface
     * @since 101.0.0
     */
    protected $storeRepository;

    /**
     * @var ProductLinkRepositoryInterface
     * @since 101.0.0
     */
    protected $productLinkRepository;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 101.0.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        StoreRepositoryInterface $storeRepository,
        ProductLinkRepositoryInterface $productLinkRepository,
        $addFieldStrategies,
        $addFilterStrategies,
        array $meta = [],
        array $data = []
    ) {
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

        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->storeRepository = $storeRepository;
        $this->productLinkRepository = $productLinkRepository;
    }

    /**
     * Retrieve link type
     *
     * @return string
     * @since 101.0.0
     */
    abstract protected function getLinkType();

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

        if (!$this->getProduct()) {
            return $collection;
        }

        $collection->addAttributeToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getProduct()->getId()]]
        );

        return $this->addCollectionFilters($collection);
    }

    /**
     * Add specific filters
     *
     * @param Collection $collection
     * @return Collection
     * @since 101.0.0
     */
    protected function addCollectionFilters(Collection $collection)
    {
        $relatedProducts = [];

        /** @var ProductLinkInterface $linkItem */
        foreach ($this->productLinkRepository->getList($this->getProduct()) as $linkItem) {
            if ($linkItem->getLinkType() !== $this->getLinkType()) {
                continue;
            }

            $relatedProducts[] = $this->productRepository->get($linkItem->getLinkedProductSku())->getId();
        }

        if ($relatedProducts) {
            $collection->addAttributeToFilter(
                $collection->getIdFieldName(),
                ['nin' => [$relatedProducts]]
            );
        }

        return $collection;
    }

    /**
     * Retrieve product
     *
     * @return ProductInterface|null
     * @since 101.0.0
     */
    protected function getProduct()
    {
        if (null !== $this->product) {
            return $this->product;
        }

        if (!($id = $this->request->getParam('current_product_id'))) {
            return null;
        }

        return $this->product = $this->productRepository->getById($id);
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
