<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;

/**
 * Data mapper factory uses to create appropriate mapper class
 */
class DataMapperFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $dataMappers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $dataMappers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $dataMappers = []
    ) {
        $this->objectManager = $objectManager;
        $this->dataMappers = $dataMappers;
    }

    /**
     * Create instance of data mapper for specified entity type
     *
     * @param string $entityType
     * @return BatchDataMapperInterface
     * @throws NoSuchEntityException
     * @throws ConfigurationMismatchException
     */
    public function create($entityType)
    {
        if (!isset($this->dataMappers[$entityType])) {
            throw new NoSuchEntityException(
                __(
                    'There is no such data mapper "%1" for interface %2',
                    $entityType,
                    BatchDataMapperInterface::class
                )
            );
        }
        $dataMapperClass = $this->dataMappers[$entityType];
        $dataMapperEntity = $this->objectManager->create($dataMapperClass);
        if (!$dataMapperEntity instanceof BatchDataMapperInterface) {
            throw new ConfigurationMismatchException(
                __(
                    'Data mapper "%1" must implement interface %2',
                    $dataMapperClass,
                    BatchDataMapperInterface::class
                )
            );
        }

        return $dataMapperEntity;
    }
}
