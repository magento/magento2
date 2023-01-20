<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Plugin product resource model
 */
class Product
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ActionInterface
     */
    private $productIndexer;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * Initialize Product dependencies.
     *
     * @param Configurable $configurable
     * @param ActionInterface $productIndexer
     * @param ProductAttributeRepositoryInterface|null $productAttributeRepository
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param FilterBuilder|null $filterBuilder
     * @param CacheContext|null $cacheContext
     * @param IndexerRegistry|null $indexerRegistry
     */
    public function __construct(
        Configurable $configurable,
        ActionInterface $productIndexer,
        ProductAttributeRepositoryInterface $productAttributeRepository = null,
        ?SearchCriteriaBuilder $searchCriteriaBuilder = null,
        ?FilterBuilder $filterBuilder = null,
        ?CacheContext $cacheContext = null,
        ?IndexerRegistry $indexerRegistry = null
    ) {
        $this->configurable = $configurable;
        $this->productIndexer = $productIndexer;
        $this->productAttributeRepository = $productAttributeRepository ?: ObjectManager::getInstance()
            ->get(ProductAttributeRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class);
        $this->filterBuilder = $filterBuilder ?: ObjectManager::getInstance()
            ->get(FilterBuilder::class);
        $this->cacheContext = $cacheContext ?: ObjectManager::getInstance()->get(CacheContext::class);
        $this->indexerRegistry = $indexerRegistry ?: ObjectManager::getInstance()
            ->get(IndexerRegistry::class);
    }

    /**
     * We need reset attribute set id to attribute after related simple product was saved
     *
     * @param ProductResource $subject
     * @param DataObject $object
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        ProductResource $subject,
        DataObject $object
    ) {
        /** @var ProductModel $object */
        if ($object->getTypeId() == Configurable::TYPE_CODE) {
            $object->getTypeInstance()->getSetAttributes($object);
            $this->resetConfigurableOptionsData($object);
        }
    }

    /**
     * Invalidate cache and perform reindexing for configurable associated product
     *
     * @param ProductResource $subject
     * @param ProductResource $result
     * @param DataObject $object
     * @return ProductResource
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        ProductResource $subject,
        ProductResource $result,
        DataObject $object
    ): ProductResource {
        $productId = $object->getId();
        $parentProductIds = $this->configurable->getParentIdsByChild($productId);
        if (count($parentProductIds) > 0) {
            $productCategoryIndexer = $this->indexerRegistry->get(Category::INDEXER_ID);
            $productCategoryIndexer->reindexRow($productId);

            $this->cacheContext->registerEntities(
                ProductModel::CACHE_TAG,
                array_unique(array_merge([$productId], $parentProductIds))
            );
            $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
            $indexer->reindexRow($productId);
        }

        return $result;
    }

    /**
     * Set null for configurable options attribute of configurable product
     *
     * @param ProductModel $object
     * @return void
     */
    private function resetConfigurableOptionsData($object)
    {
        $extensionAttribute = $object->getExtensionAttributes();
        if ($extensionAttribute && $extensionAttribute->getConfigurableProductOptions()) {
            $attributeIds = [];
            /** @var OptionInterface $option */
            foreach ($extensionAttribute->getConfigurableProductOptions() as $option) {
                $attributeIds[] = $option->getAttributeId();
            }

            $filter = $this->filterBuilder
                ->setField(ProductAttributeInterface::ATTRIBUTE_ID)
                ->setConditionType('in')
                ->setValue($attributeIds)
                ->create();
            $this->searchCriteriaBuilder->addFilters([$filter]);
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $optionAttributes = $this->productAttributeRepository->getList($searchCriteria)->getItems();

            foreach ($optionAttributes as $optionAttribute) {
                $object->setData($optionAttribute->getAttributeCode(), null);
            }
        }
    }

    /**
     * Gather configurable parent ids of product being deleted and reindex after delete is complete.
     *
     * @param ProductResource $subject
     * @param \Closure $proceed
     * @param ProductModel $product
     * @return ProductResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        ProductResource $subject,
        \Closure $proceed,
        ProductModel $product
    ) {
        $configurableProductIds = $this->configurable->getParentIdsByChild($product->getId());
        $result = $proceed($product);
        $this->productIndexer->executeList($configurableProductIds);

        return $result;
    }
}
