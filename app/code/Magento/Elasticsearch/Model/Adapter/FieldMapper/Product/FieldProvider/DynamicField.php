<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface
    as IndexTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide dynamic fields for product.
 */
class DynamicField implements FieldProviderInterface
{
    /**
     * Category collection.
     *
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * Customer group repository.
     *
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @var IndexTypeConverterInterface
     */
    private $indexTypeConverter;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param FieldTypeConverterInterface $fieldTypeConverter
     * @param IndexTypeConverterInterface $indexTypeConverter
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FieldNameResolver $fieldNameResolver
     * @param AttributeProvider $attributeAdapterProvider
     * @param Collection $categoryCollection @deprecated @see $categoryCollectionFactory
     * @param StoreManagerInterface|null $storeManager
     * @param CollectionFactory|null $categoryCollectionFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        FieldTypeConverterInterface $fieldTypeConverter,
        IndexTypeConverterInterface $indexTypeConverter,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FieldNameResolver $fieldNameResolver,
        AttributeProvider $attributeAdapterProvider,
        Collection $categoryCollection,
        ?StoreManagerInterface $storeManager = null,
        ?CollectionFactory $categoryCollectionFactory = null
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->fieldTypeConverter = $fieldTypeConverter;
        $this->indexTypeConverter = $indexTypeConverter;
        $this->fieldNameResolver = $fieldNameResolver;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->categoryCollectionFactory = $categoryCollectionFactory
            ?: ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $context = []): array
    {
        $allAttributes = [];
        $categoryIds = $this->categoryCollectionFactory->create()->getAllIds();
        $positionAttribute = $this->attributeAdapterProvider->getByAttributeCode('position');
        $categoryNameAttribute = $this->attributeAdapterProvider->getByAttributeCode('category_name');
        foreach ($categoryIds as $categoryId) {
            $categoryPositionKey = $this->fieldNameResolver->getFieldName(
                $positionAttribute,
                ['categoryId' => $categoryId]
            );
            $categoryNameKey = $this->fieldNameResolver->getFieldName(
                $categoryNameAttribute,
                ['categoryId' => $categoryId]
            );
            $allAttributes[$categoryPositionKey] = [
                'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_INT),
                'index' => $this->indexTypeConverter->convert(IndexTypeConverterInterface::INTERNAL_NO_INDEX_VALUE)
            ];
            $allAttributes[$categoryNameKey] = [
                'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING),
                'index' => $this->indexTypeConverter->convert(IndexTypeConverterInterface::INTERNAL_NO_INDEX_VALUE)
            ];
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $groups = $this->groupRepository->getList($searchCriteria)->getItems();
        $priceAttribute = $this->attributeAdapterProvider->getByAttributeCode('price');
        /**
         * For backword compatibility, we use 'websiteId' if the 'storeId' parameter is missing,
         * although the 'websiteId' may contain the store ID instead of website ID
         * @see \Magento\Elasticsearch\Model\Adapter\Elasticsearch:494
         */
        $ctx = [];
        if (isset($context['storeId'])) {
            $ctx['websiteId'] = $this->storeManager->getStore($context['storeId'])->getWebsiteId();
        } elseif (isset($context['websiteId'])) {
            $ctx['websiteId'] = $context['websiteId'];
        }
        foreach ($groups as $group) {
            $ctx['customerGroupId'] = $group->getId();
            $groupPriceKey = $this->fieldNameResolver->getFieldName(
                $priceAttribute,
                $ctx
            );
            $allAttributes[$groupPriceKey] = [
                'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_FLOAT),
                'store' => true
            ];
        }

        return $allAttributes;
    }
}
