<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportCsv\Model;

use Magento\ImportCsvApi\Api\Data\SourceDataInterface;

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
     * @param string $entity
     * @param string $behavior
     * @param string $validationStrategy
     * @param string $allowedErrorCount
     */
    public function __construct(
        string $entity,
        string $behavior,
        string $validationStrategy,
        string $allowedErrorCount
    ) {
        $this->entity = $entity;
        $this->behavior = $behavior;
        $this->validationStrategy = $validationStrategy;
        $this->allowedErrorCount = $allowedErrorCount;
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
     * @return array
     */
    public function toArray()
    {
        return [
            'entity' => $this->entity,
            'behavior' => $this->behavior,
            'validation_strategy' => $this->validationStrategy,
            'allowed_error_count' => $this->allowedErrorCount
        ];
    }
}
