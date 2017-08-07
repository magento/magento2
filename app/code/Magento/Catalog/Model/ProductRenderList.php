<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductRenderListInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorComposite;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Data\CollectionModifier;
use Magento\Framework\Data\CollectionModifierInterface;

/**
 * Provide product render information (this information should be enough for rendering product on front)
 * for one or few products
 *
 * @since 2.2.0
 */
class ProductRenderList implements ProductRenderListInterface
{
    /**
     * @var CollectionProcessorInterface
     * @since 2.2.0
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     * @since 2.2.0
     */
    private $collectionFactory;

    /**
     * @var ProductRenderCollectorInterface
     * @since 2.2.0
     */
    private $productRenderCollectorComposite;

    /**
     * @var SearchResultFactory
     * @since 2.2.0
     */
    private $searchResultFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRenderFactory
     * @since 2.2.0
     */
    private $productRenderFactory;

    /**
     * @var array
     * @since 2.2.0
     */
    private $productAttributes;

    /**
     * @var CollectionModifierInterface
     * @since 2.2.0
     */
    private $collectionModifier;

    /**
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductRenderCollectorComposite $productRenderCollectorComposite
     * @param ProductRenderSearchResultsFactory $searchResultFactory
     * @param ProductRenderFactory $productRenderDtoFactory
     * @param Config $config
     * @param Product\Visibility $productVisibility
     * @param CollectionModifier $collectionModifier
     * @param array $productAttributes
     * @since 2.2.0
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ProductRenderCollectorComposite $productRenderCollectorComposite,
        ProductRenderSearchResultsFactory $searchResultFactory,
        ProductRenderFactory $productRenderDtoFactory,
        \Magento\Catalog\Model\Config $config,
        CollectionModifier $collectionModifier,
        array $productAttributes
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->productRenderCollectorComposite = $productRenderCollectorComposite;
        $this->searchResultFactory = $searchResultFactory;
        $this->productRenderFactory = $productRenderDtoFactory;
        $this->productAttributes = array_merge($productAttributes, $config->getProductAttributes());
        $this->collectionModifier = $collectionModifier;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $storeId, $currencyCode)
    {
        $items = [];
        $productCollection = $this->collectionFactory->create();
        $productCollection->addAttributeToSelect($this->productAttributes)
            ->setStoreId($storeId)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents();

        $this->collectionModifier->apply($productCollection);
        $this->collectionProcessor->process($searchCriteria, $productCollection);

        foreach ($productCollection as $item) {
            $productRenderInfo = $this->productRenderFactory->create();
            $productRenderInfo->setStoreId($storeId);
            $productRenderInfo->setCurrencyCode($currencyCode);
            $this->productRenderCollectorComposite->collect($item, $productRenderInfo);
            $items[$item->getId()] = $productRenderInfo;
        }

        $searchResult = $this->searchResultFactory->create();
        $searchResult->setItems($items);
        $searchResult->setTotalCount(count($items));
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }
}
