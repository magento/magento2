<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto;

use InvalidArgumentException;
use Magento\Framework\Dto\DtoProjection\ProcessProjection;
use Magento\Framework\Dto\DtoProjection\ProcessStraightMapping;
use Magento\Framework\Exception\SerializationException;
use ReflectionException;

/**
 * DTO projection
 *
 * @api
 */
class DtoProjection
{
    /**
     * @var DtoProcessor
     */
    private $dtoProcessor;

    /**
     * @var DtoConfig
     */
    private $dtoConfig;

    /**
     * @var ProcessProjection
     */
    private $processProjection;

    /**
     * @var ProcessStraightMapping
     */
    private $processStraightMapping;

    /**
     * @param DtoProcessor $dtoProcessor
     * @param DtoConfig $dtoConfig
     * @param ProcessProjection $processProjection
     * @param ProcessStraightMapping $processStraightMapping
     */
    public function __construct(
        DtoProcessor $dtoProcessor,
        DtoConfig $dtoConfig,
        ProcessProjection $processProjection,
        ProcessStraightMapping $processStraightMapping
    ) {
        $this->dtoProcessor = $dtoProcessor;
        $this->dtoConfig = $dtoConfig;
        $this->processProjection = $processProjection;
        $this->processStraightMapping = $processStraightMapping;
    }

    /**
     * Execute DTO projection
     *
     * @param string $toType
     * @param string $fromType
     * @param $sourceObject
     * @return mixed
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function execute(string $toType, string $fromType, $sourceObject)
    {
        $data = $this->dtoProcessor->getObjectData($sourceObject, $fromType);
        $projectionConfig = $this->dtoConfig->get('projection');

        if (!isset($projectionConfig[$toType][$fromType])) {
            throw new InvalidArgumentException('No projection defined from ' . $fromType . ' to ' . $toType);
        }

        $myProjectionConfig = $projectionConfig[$toType][$fromType];

        if (isset($myProjectionConfig['preprocessor'])) {
            $data = $this->processProjection->execute($data, $myProjectionConfig['preprocessor']);
        }

        if (isset($myProjectionConfig['straight'])) {
            $data = $this->processStraightMapping->execute($data, $myProjectionConfig['straight']);
        }

        if (isset($myProjectionConfig['postprocessor'])) {
            $data = $this->processProjection->execute($data, $myProjectionConfig['postprocessor']);
        }

        return $this->dtoProcessor->createFromArray($data, $toType);
    }
}
