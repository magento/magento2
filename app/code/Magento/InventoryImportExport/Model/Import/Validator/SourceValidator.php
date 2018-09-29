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
    private $sourceCodes = [];

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
        $this->loadSourceCodes();
    }

    /**
     * @inheritdoc
     */
    public function validate(array $rowData, int $rowNumber)
    {
        $errors = [];

        if (!isset($rowData[Sources::COL_SOURCE_CODE])) {
            $errors[] = __('Missing required column "%column"', ['column' => Sources::COL_SOURCE_CODE]);
        } elseif (!$this->isExistingSource($rowData[Sources::COL_SOURCE_CODE])) {
            $errors[] = __('Source code "%code" does not exists', ['code' => $rowData[Sources::COL_SOURCE_CODE]]);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }

    /**
     * Returns exits already the source in sources.
     *
     * @param string $sourceCode
     * @return bool
     */
    private function isExistingSource($sourceCode): bool
    {
        return isset($this->sourceCodes[$sourceCode]);
    }

    /**
     * Loads all existing source codes
     *
     * @return void
     */
    private function loadSourceCodes()
    {
        $sources = $this->sourceRepository->getList();
        foreach ($sources->getItems() as $source) {
            $sourceCode = $source->getSourceCode();
            $this->sourceCodes[$sourceCode] = $sourceCode;
        }
    }
}
