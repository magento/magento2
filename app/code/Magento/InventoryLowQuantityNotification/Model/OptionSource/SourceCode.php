<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Provide option values for UI
 *
 * @api
 */
class SourceCode implements OptionSourceInterface
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository
    ) {
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $optionArray = [];
        $sourcesSearchResult = $this->sourceRepository->getList();
        $sourcesList = $sourcesSearchResult->getItems();

        foreach ($sourcesList as $source) {
            $optionArray[] = ['value' => $source->getSourceCode(), 'label' => $source->getSourceCode()];
        }

        return $optionArray;
    }
}
