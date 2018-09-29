<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationAdminUi\Model\OptionSource;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Provide option values for UI
 *
 * @api
 */
class EnabledSourceCode implements OptionSourceInterface
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    
    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceInterface::ENABLED, true)
            ->create();
        $sourcesSearchResult = $this->sourceRepository->getList($searchCriteria);
        $sourcesList = $sourcesSearchResult->getItems();

        $optionArray = [];
        foreach ($sourcesList as $source) {
            $optionArray[] = ['value' => $source->getSourceCode(), 'label' => $source->getSourceCode()];
        }
        return $optionArray;
    }
}
