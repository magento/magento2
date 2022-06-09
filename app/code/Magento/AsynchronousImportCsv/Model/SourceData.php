<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportCsv\Model;

use Magento\AsynchronousImportCsvApi\Api\Data\SourceDataInterface;

class SourceData implements SourceDataInterface
{

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $behavior;

    /**
     * @var string
     */
    private $validationStrategy;

    /**
     * @var string
     */
    private $allowedErrorCount;

    /**
     * @var string
     */
    private $importFieldSeparator;

    /**
     * @var string
     */
    private $importMultipleValueSeparator;

    /**
     * @var string
     */
    private $importEmptyAttributeValueConstant;

    /**
     * @var string
     */
    private $importImagesFileDir;

    /**
     * @param string $entity
     * @param string $behavior
     * @param string $validationStrategy
     * @param string $allowedErrorCount
     * @param string $importFieldSeparator
     * @param string $importMultipleValueSeparator
     * @param string $importEmptyAttributeValueConstant
     * @param string $importImagesFileDir
     */
    public function __construct(
        string $entity,
        string $behavior,
        string $validationStrategy,
        string $allowedErrorCount,
        string $importFieldSeparator,
        string $importMultipleValueSeparator,
        string $importEmptyAttributeValueConstant,
        string $importImagesFileDir
    ) {
        $this->entity = $entity;
        $this->behavior = $behavior;
        $this->validationStrategy = $validationStrategy;
        $this->allowedErrorCount = $allowedErrorCount;
        $this->importFieldSeparator = $importFieldSeparator;
        $this->importMultipleValueSeparator = $importMultipleValueSeparator;
        $this->importEmptyAttributeValueConstant = $importEmptyAttributeValueConstant;
        $this->importImagesFileDir = $importImagesFileDir;
    }

    /**
     * @inheritdoc
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     */
    public function getBehavior(): string
    {
        return $this->behavior;
    }

    /**
     * @inheritdoc
     */
    public function getValidationStrategy(): string
    {
        return $this->validationStrategy;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedErrorCount(): string
    {
        return $this->allowedErrorCount;
    }

    /**
     * @inheritdoc
     */
    public function getImportFieldSeparator(): string
    {
        return $this->importFieldSeparator;
    }

    /**
     * @inheritdoc
     */
    public function getImportMultipleValueSeparator(): string
    {
        return $this->importMultipleValueSeparator;
    }

    /**
     * @inheritdoc
     */
    public function getImportEmptyAttributeValueConstant(): string
    {
        return $this->importEmptyAttributeValueConstant;
    }

    /**
     * @inheritdoc
     */
    public function getImportImagesFileDir(): string
    {
        return $this->importImagesFileDir;
    }

    public function toArray()
    {
        return [
            'entity' => $this->entity,
            'behavior' => $this->behavior,
            'validation_strategy' => $this->validationStrategy,
            'allowed_error_count' => $this->allowedErrorCount,
            '_import_field_separator' => $this->importFieldSeparator,
            '_import_multiple_value_separator' => $this->importMultipleValueSeparator,
            '_import_empty_attribute_value_constant' => $this->importEmptyAttributeValueConstant,
            'import_images_file_dir' => $this->importImagesFileDir
        ];
    }
}
