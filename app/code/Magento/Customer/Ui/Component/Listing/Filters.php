<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing;

use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;
use Magento\Framework\Indexer\IndexerRegistry;

class Filters extends \Magento\Ui\Component\Filters
{
    /** @var IndexerRegistry  */
    protected $indexerRegistry;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Customer\Ui\Component\FilterFactory $filterFactory
     * @param AttributeRepository $attributeRepository
     * @param IndexerRegistry $indexerRegistry
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Customer\Ui\Component\FilterFactory $filterFactory,
        \Magento\Customer\Ui\Component\Listing\AttributeRepository $attributeRepository,
        IndexerRegistry $indexerRegistry,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->filterFactory = $filterFactory;
        $this->attributeRepository = $attributeRepository;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $indexer = $this->indexerRegistry->get(\Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID);
        if ($indexer->getState()->getStatus() == \Magento\Framework\Indexer\StateInterface::STATUS_INVALID) {
            parent::prepare();
            return false;
        }

        /** @var \Magento\Customer\Model\Attribute $attribute */
        foreach ($this->attributeRepository->getList() as $attributeCode => $attributeData) {
            if (!isset($this->components[$attributeCode])) {
                if (!$attributeData[AttributeMetadata::BACKEND_TYPE] != 'static'
                    && $attributeData[AttributeMetadata::IS_USED_IN_GRID]
                    && $attributeData[AttributeMetadata::IS_FILTERABLE_IN_GRID]
                ) {
                    $filter = $this->filterFactory->create($attributeData, $this->getContext());
                    $filter->prepare();
                    $this->addComponent($attributeCode, $filter);
                }
            } elseif ($attributeData[AttributeMetadata::IS_USED_IN_GRID]
                && !$attributeData[AttributeMetadata::IS_FILTERABLE_IN_GRID]
            ) {
                unset($this->components[$attributeCode]);
            }
        }
        parent::prepare();
    }
}
