<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Model\Resource\Product\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductRepository implements \Magento\Catalog\Api\ProductRepositoryInterface
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Product[]
     */
    protected $instances = [];

    /**
     * @var Product[]
     */
    protected $instancesById = [];

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsDataBuilder
     */
    protected $searchResultsBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaDataBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $resourceModel;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $metadataService;

    /**
     * @param ProductFactory $productFactory
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsDataBuilder $searchResultsBuilder
     * @param Resource\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaDataBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param Resource\Product $resourceModel
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface
     */
    public function __construct(
        ProductFactory $productFactory,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Api\Data\ProductSearchResultsDataBuilder $searchResultsBuilder,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaDataBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\Resource\Product $resourceModel,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface
    ) {
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->initializationHelper = $initializationHelper;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->attributeRepository = $attributeRepository;
        $this->filterBuilder = $filterBuilder;
        $this->metadataService = $metadataServiceInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $editMode = false, $storeId = null)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        if (!isset($this->instances[$sku][$cacheKey])) {
            $product = $this->productFactory->create();

            $productId = $this->resourceModel->getIdBySku($sku);
            if (!$productId) {
                throw new NoSuchEntityException('Requested product doesn\'t exist');
            }
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            $product->load($productId);
            $this->instances[$sku][$cacheKey] = $product;
            $this->instancesById[$product->getId()][$cacheKey] = $product;
        }
        return $this->instances[$sku][$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function getById($productId, $editMode = false, $storeId = null)
    {
        $cacheKey = $this->getCacheKey(func_get_args());
        if (!isset($this->instancesById[$productId][$cacheKey])) {
            $product = $this->productFactory->create();

            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            if (!$product->getId()) {
                throw new NoSuchEntityException('Requested product doesn\'t exist');
            }
            $this->instancesById[$productId][$cacheKey] = $product;
            $this->instances[$product->getSku()][$cacheKey] = $product;
        }
        return $this->instancesById[$productId][$cacheKey];
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        unset($data[0]);
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }

        return md5(serialize($serializeData));
    }

    /**
     * Merge data from DB and updates from request
     *
     * @param array $productData
     * @param bool $createNew
     * @return \Magento\Catalog\Api\Data\ProductInterface|Product
     * @throws NoSuchEntityException
     */
    protected function initializeProductData(array $productData, $createNew)
    {
        if ($createNew) {
            $product = $this->productFactory->create();
        } else {
            $product = $this->get($productData['sku']);
            $this->initializationHelper->initialize($product);
        }
        foreach ($productData as $key => $value) {
            $product->setData($key, $value);
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\ProductInterface $product, $saveOptions = false)
    {
        $productId = $this->resourceModel->getIdBySku($product->getSku());
        $product = $this->initializeProductData($product->toFlatArray(), empty($productId));
        $validationResult = $this->resourceModel->validate($product);
        if (true !== $validationResult) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                sprintf('Invalid product data: %s', implode(',', $validationResult))
            );
        }
        try {
            if ($saveOptions) {
                $product->setCanSaveCustomOptions(true);
            }
            $this->resourceModel->save($product);
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $exception) {
            throw \Magento\Framework\Exception\InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $product->getData($exception->getAttributeCode()),
                $exception
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException('Unable to save product');
        }
        unset($this->instances[$product->getSku()]);
        unset($this->instancesById[$product->getId()]);
        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $productSku = $product->getSku();
        $productId = $product->getId();
        try {
            $this->resourceModel->delete($product);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException('Unable to remove product ' . $productSku);
        }
        unset($this->instances[$productSku]);
        unset($this->instancesById[$productId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($productSku)
    {
        $product = $this->get($productSku);
        return $this->delete($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
        $collection = $this->collectionFactory->create();

        $extendedSearchCriteria = $this->searchCriteriaBuilder->addFilter(
            [
                $this->filterBuilder
                    ->setField('attribute_set_id')
                    ->setValue(\Magento\Catalog\Api\Data\ProductAttributeInterface::DEFAULT_ATTRIBUTE_SET_ID)
                    ->create(),
            ]
        );

        foreach ($this->metadataService->getList($extendedSearchCriteria->create())->getItems() as $metadata) {
            $collection->addAttributeToSelect($metadata->getAttributeCode());
        }
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SearchCriteriaInterface::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);
        $this->searchResultsBuilder->setItems($collection->getItems());
        $this->searchResultsBuilder->setTotalCount($collection->getSize());
        return $this->searchResultsBuilder->create();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = ['attribute' => $filter->getField(), $condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }
}
