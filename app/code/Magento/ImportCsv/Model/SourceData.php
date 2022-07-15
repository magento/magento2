<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportCsv\Model;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\ImportCsvApi\Api\Data\SourceDataInterface;

class SourceData extends AbstractSimpleObject implements SourceDataInterface
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
    private $csvData;

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
     * @inheritDoc
     */
    public function getCsvData()
    {
        return $this->csvData;
    }

    /**
     * @inheritDoc
     */
    public function setEntity($entity)
    {
        return $this->setData(self::ENTITY, $entity);
    }

    /**
     * @inheritDoc
     */
    public function setBehavior($behavior)
    {
        return $this->setData(self::BEHAVIOR, $behavior);
    }

    /**
     * @inheritDoc
     */
    public function setValidationStrategy($validationStrategy)
    {
        return $this->setData(self::VALIDATION_STRATEGY, $validationStrategy);
    }

    /**
     * @inheritDoc
     */
    public function setAllowedErrorCount($allowedErrorCount)
    {
        return $this->setData(self::ALLOWED_ERROR_COUNT, $allowedErrorCount);
    }

    /**
     * @inheritDoc
     */
    public function setCsvData($csvData)
    {
        return $this->setData(self::PAYLOAD, $csvData);
    }
}
