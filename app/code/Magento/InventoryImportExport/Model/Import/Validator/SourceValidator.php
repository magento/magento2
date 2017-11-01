<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import\Validator;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryImportExport\Model\Import\Sources;

/**
 * Extension point for source validation
 */
class SourceValidator implements ValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var array
     */
    private $sourceIds = [];

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        SourceRepositoryInterface $sourceRepository
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->sourceRepository = $sourceRepository;
        $this->loadSourceIds();
    }

    /**
     * @inheritdoc
     */
    public function validate(array $rowData, int $rowNumber)
    {
        $errors = [];

        if (!isset($rowData[Sources::COL_SOURCE])) {
            $errors[] = __('Missing required column "%column"', ['column' => Sources::COL_SOURCE]);
        } elseif (!$this->isExistingSource($rowData[Sources::COL_SOURCE])) {
            $errors[] = __('Source id "%id" does not exists', ['id' => $rowData[Sources::COL_SOURCE]]);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }

    /**
     * Returns exits already the source in sources.
     *
     * @param int $sourceId
     * @return bool
     */
    private function isExistingSource($sourceId): bool
    {
        return isset($this->sourceIds[$sourceId]);
    }

    /**
     * Loads all existing source ids
     *
     * @return void
     */
    private function loadSourceIds()
    {
        $sources = $this->sourceRepository->getList();
        foreach ($sources->getItems() as $source) {
            $sourceId = $source->getSourceId();
            $this->sourceIds[$sourceId] = $sourceId;
        }
    }
}
