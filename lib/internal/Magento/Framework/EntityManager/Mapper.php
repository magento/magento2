<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

/**
 * Class Mapper
 * @since 2.1.0
 */
class Mapper implements MapperInterface
{
    /**
     * @var array
     * @since 2.1.0
     */
    private $config;

    /**
     * Initialize dependencies.
     *
     * @param array $config
     * @since 2.1.0
     */
    public function __construct(
        $config = []
    ) {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function entityToDatabase($entityType, $data)
    {
        if (isset($this->config[$entityType])) {
            foreach ($this->config[$entityType] as $databaseFieldName => $entityFieldName) {
                if (!$entityFieldName) {
                    throw new \LogicException('Incorrect configuration for ' . $entityType);
                }
                if (isset($data[$entityFieldName])) {
                    $data[$databaseFieldName] = $data[$entityFieldName];
                    unset($data[$entityFieldName]);
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function databaseToEntity($entityType, $data)
    {
        if (isset($this->config[$entityType])) {
            foreach ($this->config[$entityType] as $databaseFieldName => $entityFieldName) {
                if (!$entityFieldName) {
                    throw new \LogicException('Incorrect configuration for ' . $entityType);
                }
                if (isset($data[$databaseFieldName])) {
                    $data[$entityFieldName] = $data[$databaseFieldName];
                    unset($data[$databaseFieldName]);
                }
            }
        }
        return $data;
    }
}
