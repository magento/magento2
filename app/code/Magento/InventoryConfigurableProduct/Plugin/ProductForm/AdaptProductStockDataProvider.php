<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfigurableProduct\Plugin\ProductForm;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data\ProductStockDataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Inventory\Model\Source\Command\Get as GetSource;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;

class AdaptProductStockDataProvider
{
    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var GetSource
     */
    private $getSource;

    /**
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param GetSource $getSource
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        GetSource $getSource
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->getSource = $getSource;
    }

    /**
     * @param ProductStockDataProvider $subject
     * @param callable $proceed
     * @param Product $product
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        ProductStockDataProvider $subject,
        callable $proceed,
        Product $product
    ) {
        $formSourceItems = [];

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter(SourceItemInterface::SKU, $product->getSku())->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        foreach ($sourceItems as $sourceItem) {
            $source = $this->getSource->execute($sourceItem->getSourceCode());

            $formSourceItem[SourceItemInterface::SOURCE_CODE] = $sourceItem->getSourceCode();
            $formSourceItem['source'] = $source->getName();
            $formSourceItem[SourceItemInterface::QUANTITY] = $sourceItem->getQuantity();

            $formSourceItems[] = $formSourceItem;
        }

        return $formSourceItems;
    }
}
