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
 *
 * Render information provided for one or few products
 */
class ProductRenderList implements ProductRenderListInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductRenderCollectorInterface
     */
    private $productRenderCollectorComposite;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRenderFactory
     */
    private $productRenderFactory;

    /**
     * @var array
     */
    private $productAttributes;

    /**
     * @var CollectionModifierInterface
     */
    private $collectionModifier;

    /**
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductRenderCollectorComposite $productRenderCollectorComposite
     * @param ProductRenderSearchResultsFactory $searchResultFactory
     * @param ProductRenderFactory $productRenderDtoFactory
     * @param Config $config
     * @param CollectionModifier $collectionModifier
     * @param array $productAttributes
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
