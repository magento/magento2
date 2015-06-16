<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestJoinDirectives\Model;

use Magento\TestJoinDirectives\Api\TestRepositoryInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

/**
 * Model TestRepository
 */
class  TestRepository implements TestRepositoryInterface
{
    /**
     * @var \Magento\Quote\Model\Resource\Quote\Collection
     */
    protected $quoteCollection;

    /**
     * @var \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory
     */
    protected $searchResultsDataFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @param \Magento\Quote\Model\Resource\Quote\Collection $quoteCollection
     * @param \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory $searchResultsDataFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        \Magento\Quote\Model\Resource\Quote\Collection $quoteCollection,
        \Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory $searchResultsDataFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->searchResultsDataFactory = $searchResultsDataFactory;
        $this->quoteCollection = $quoteCollection;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        $searchData = $this->searchResultsDataFactory->create();
        $searchData->setSearchCriteria($searchCriteria);
        $this->extensionAttributesJoinProcessor->process($this->quoteCollection);
        $searchData->setItems($this->quoteCollection->getItems());
        return $searchData;
    }
}
