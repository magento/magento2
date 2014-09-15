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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1;

use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Catalog\Service\V1\Data\Converter;
use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\Catalog\Service\V1\Data\Product as ProductData;
use Magento\Framework\Service\V1\Data\Search\FilterGroup;
use Magento\Catalog\Model\Resource\Product\Collection;
use Magento\Catalog\Service\V1\Product\MetadataServiceInterface as ProductMetadataServiceInterface;
use Magento\Framework\Service\V1\Data\SortOrder;

/**
 * Class ProductService
 * @package Magento\Catalog\Service\V1
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductService implements ProductServiceInterface
{
    /**
     * @var Product\Initialization\Helper
     */
    private $initializationHelper;

    /**
     * @var \Magento\Catalog\Service\V1\Data\ProductMapper
     */
    protected $productMapper;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    private $productTypeManager;

    /**
     * @var \Magento\Catalog\Service\V1\Product\ProductLoader
     */
    private $productLoader;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    private $productCollection;

    /**
     * @var ProductMetadataServiceInterface
     */
    private $metadataService;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Product\SearchResultsBuilder
     */
    private $searchResultsBuilder;

    /**
     * @var \Magento\Catalog\Service\V1\Product\ProductLoadProcessorInterface
     */
    private $productLoadProcessor;

    /**
     * @var \Magento\Catalog\Service\V1\Product\ProductSaveProcessorInterface
     */
    private $productSaveProcessor;

    /**
     * @param Product\Initialization\Helper $initializationHelper
     * @param Data\ProductMapper $productMapper
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollection
     * @param ProductMetadataServiceInterface $metadataService
     * @param \Magento\Catalog\Service\V1\Data\Converter $converter
     * @param \Magento\Catalog\Service\V1\Data\Product\SearchResultsBuilder $searchResultsBuilder
     * @param \Magento\Catalog\Service\V1\Product\ProductLoadProcessorInterface $productLoadProcessor
     * @param \Magento\Catalog\Service\V1\Product\ProductSaveProcessorInterface $productSaveProcessor
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Service\V1\Data\ProductMapper $productMapper,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollection,
        ProductMetadataServiceInterface $metadataService,
        \Magento\Catalog\Service\V1\Data\Converter $converter,
        \Magento\Catalog\Service\V1\Data\Product\SearchResultsBuilder $searchResultsBuilder,
        \Magento\Catalog\Service\V1\Product\ProductLoadProcessorInterface $productLoadProcessor,
        \Magento\Catalog\Service\V1\Product\ProductSaveProcessorInterface $productSaveProcessor
    ) {
        $this->initializationHelper = $initializationHelper;
        $this->productMapper = $productMapper;
        $this->productTypeManager = $productTypeManager;
        $this->productLoader = $productLoader;
        $this->productCollection = $productCollection;
        $this->metadataService = $metadataService;
        $this->converter = $converter;
        $this->searchResultsBuilder = $searchResultsBuilder;
        $this->productLoadProcessor = $productLoadProcessor;
        $this->productSaveProcessor = $productSaveProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function create(\Magento\Catalog\Service\V1\Data\Product $product)
    {
        try {
            $productModel = $this->productMapper->toModel($product, null, ['bundle_product_options']);
            $this->initializationHelper->initialize($productModel);
            $this->productSaveProcessor->create($productModel, $product);
            $productModel->validate();
            $productModel->save();
            $this->productSaveProcessor->afterCreate($productModel, $product);
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $exception) {
            throw \Magento\Framework\Exception\InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $productModel->getData($exception->getAttributeCode()),
                $exception
            );
        }
        if (!$productModel->getId()) {
            throw new \Magento\Framework\Exception\StateException('Unable to save product');
        }
        return $productModel->getSku();
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, \Magento\Catalog\Service\V1\Data\Product $product)
    {
        $productModel = $this->productLoader->load($id);
        try {
            $this->productMapper->toModel($product, $productModel, ['bundle_product_options']);
            $this->initializationHelper->initialize($productModel);
            $this->productTypeManager->processProduct($productModel);
            $this->productSaveProcessor->update($id, $product);
            $productModel->validate();
            $productModel->save();
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $exception) {
            throw \Magento\Framework\Exception\InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $productModel->getData($exception->getAttributeCode()),
                $exception
            );
        }
        return $productModel->getSku();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $productModel = $this->productLoader->load($id);

        $productDataObject = $this->converter->createProductDataFromModel($productModel);
        $this->productSaveProcessor->delete($productDataObject);

        $productModel->delete();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $productBuilder = $this->converter->createProductBuilderFromModel($this->productLoader->load($id));
        $this->productLoadProcessor->load($id, $productBuilder);
        return $productBuilder->create();
    }

    /**
     * {@inheritdoc}
     * Example of request:
     * {
     *     "searchCriteria": {
     *         "filterGroups": [
     *             {
     *                 "filters": [
     *                     {"value": "16.000", "conditionType" : "eq", "field" : "price"}
     *                 ]
     *             }
     *         ]
     *     },
     *     "sort_orders" : {"id": "1"},
     *     "page_size" : "30",
     *     "current_page" : "10"
     * }
     *
     * products?searchCriteria[filterGroups][0][filters][0][field]=price&
     * searchCriteria[filterGroups][0][filters][0][value]=16.000&page_size=30&current_page=1&sort_orders[id]=1
     */
    public function search(SearchCriteria $searchCriteria)
    {
        $this->searchResultsBuilder->setSearchCriteria($searchCriteria);
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
        $collection = $this->productCollection->create();
        // This is needed to make sure all the attributes are properly loaded
        foreach ($this->metadataService->getProductAttributesMetadata() as $metadata) {
            $collection->addAttributeToSelect($metadata->getAttributeCode());
        }

        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        /** @var SortOrder $sortOrder*/
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $this->translateField($sortOrder->getField());
            $collection->addOrder($field, ($sortOrder->getDirection() == SearchCriteria::SORT_ASC) ? 'ASC' : 'DESC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $products = array();
        /** @var \Magento\Catalog\Model\Product $productModel */
        foreach ($collection as $productModel) {
            $productBuilder = $this->converter->createProductBuilderFromModel($productModel);
            $this->productLoadProcessor->load($productModel->getSku(), $productBuilder);
            $products[] = $productBuilder->create();
        }

        $this->searchResultsBuilder->setItems($products);
        $this->searchResultsBuilder->setTotalCount($collection->getSize());
        return $this->searchResultsBuilder->create();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $field = $this->translateField($filter->getField());
            $fields[] = array('attribute' => $field, $condition => $filter->getValue());
        }
        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }

    /**
     * Translates a field name to a DB column name for use in collection queries.
     *
     * @param string $field a field name that should be translated to a DB column name.
     * @return string
     */
    protected function translateField($field)
    {
        return $field;
    }
}
