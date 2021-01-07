<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Plugin\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\ActionInterface;

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
     * Initialize Product dependencies.
     *
     * @param Configurable $configurable
     * @param ActionInterface $productIndexer
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        Configurable $configurable,
        ActionInterface $productIndexer,
        ProductAttributeRepositoryInterface $productAttributeRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        FilterBuilder $filterBuilder = null
    ) {
        $this->configurable = $configurable;
        $this->productIndexer = $productIndexer;
        $this->productAttributeRepository = $productAttributeRepository ?: ObjectManager::getInstance()
            ->get(ProductAttributeRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class);
        $this->filterBuilder = $filterBuilder ?: ObjectManager::getInstance()
            ->get(FilterBuilder::class);
    }

    /**
     * We need reset attribute set id to attribute after related simple product was saved
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Magento\Framework\DataObject $object
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Framework\DataObject $object
    ) {
        /** @var \Magento\Catalog\Model\Product $object */
        if ($object->getTypeId() == Configurable::TYPE_CODE) {
            $object->getTypeInstance()->getSetAttributes($object);
            $this->resetConfigurableOptionsData($object);
        }
    }

    /**
     * Set null for configurable options attribute of configurable product
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $configurableProductIds = $this->configurable->getParentIdsByChild($product->getId());
        $result = $proceed($product);
        $this->productIndexer->executeList($configurableProductIds);

        return $result;
    }
}
