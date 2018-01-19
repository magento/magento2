<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryConfiguration\Block\Adminhtml\Product\Lowstock\Grid\Options;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class SourceCodeOptionHash implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * SourceCodeOptionHash constructor.
     *
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository
    ) {
        $this->sourceRepository = $sourceRepository;
    }


    /**
     * Return array of available sources
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $sourcesSearchResult = $this->sourceRepository->getList();
        $sourcesList = $sourcesSearchResult->getItems();

        /** @var SourceInterface $source */
        foreach ($sourcesList as $source) {
            $optionArray[] = ['value' => $source->getSourceCode(), 'label' => $source->getSourceCode()];
        }

        return $optionArray;
    }
}
