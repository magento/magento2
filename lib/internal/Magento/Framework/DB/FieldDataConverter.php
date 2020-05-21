<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\DataConverterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select\QueryModifierInterface;

/**
 * Convert field data from one representation to another
 */
class FieldDataConverter
{
    /**
     * Batch size env variable name
     */
    const BATCH_SIZE_VARIABLE_NAME = 'DATA_CONVERTER_BATCH_SIZE';

    /**
     * Default batch size for data converter
     */
    const DEFAULT_BATCH_SIZE = 50000;

    /**
     * @var Generator
     */
    private $queryGenerator;

    /**
     * @var DataConverterInterface
     */
    private $dataConverter;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    /**
     * @var string|null
     */
    private $envBatchSize;

    /**
     * Constructor
     *
     * @param Generator $queryGenerator
     * @param DataConverterInterface $dataConverter
     * @param SelectFactory $selectFactory
     * @param string|null $envBatchSize
     */
    public function __construct(
        Generator $queryGenerator,
        DataConverterInterface $dataConverter,
        SelectFactory $selectFactory,
        $envBatchSize = null
    ) {
        $this->queryGenerator = $queryGenerator;
        $this->dataConverter = $dataConverter;
        $this->selectFactory = $selectFactory;
        $this->envBatchSize = $envBatchSize;
    }

    /**
     * Convert table field data from one representation to another
     *
     * @param AdapterInterface $connection
     * @param string $table
     * @param string $identifier
     * @param string $field
     * @param QueryModifierInterface|null $queryModifier
     * @throws FieldDataConversionException
     * @return void
     */
    public function convert(
        AdapterInterface $connection,
        $table,
        $identifier,
        $field,
        QueryModifierInterface $queryModifier = null
    ) {
        $select = $this->selectFactory->create($connection)
            ->from($table, [$identifier, $field])
            ->where($field . ' IS NOT NULL');
        if ($queryModifier) {
            $queryModifier->modify($select);
        }
        $iterator = $this->queryGenerator->generate($identifier, $select, $this->getBatchSize());
        foreach ($iterator as $selectByRange) {
            $rows = $connection->fetchPairs($selectByRange);
            $uniqueFieldDataArray = array_unique($rows);
            foreach ($uniqueFieldDataArray as $uniqueFieldData) {
                $ids = array_keys($rows, $uniqueFieldData);
                try {
                    $convertedValue = $this->dataConverter->convert($uniqueFieldData);
                    if ($uniqueFieldData === $convertedValue) {
                        // Skip for data rows that have been already converted
                        continue;
                    }
                    $bind = [$field => $convertedValue];
                    $where = [$identifier . ' IN (?)' => $ids];
                    $connection->update($table, $bind, $where);
                } catch (DataConversionException $e) {
                    throw new \Magento\Framework\DB\FieldDataConversionException(
                        sprintf(
                            \Magento\Framework\DB\FieldDataConversionException::MESSAGE_PATTERN,
                            $field,
                            $table,
                            $identifier,
                            implode(', ', $ids),
                            get_class($this->dataConverter),
                            $e->getMessage()
                        )
                    );
                }
            }
        }
    }

    /**
     * Get batch size from environment variable or default
     *
     * @return int
     */
    private function getBatchSize()
    {
        if (null !== $this->envBatchSize) {
            $batchSize = (int) $this->envBatchSize;
            $envBatchSize = preg_replace('#[^0-9]+#', '', $this->envBatchSize);
            if (bccomp($envBatchSize, (string)PHP_INT_MAX, 0) === 1 || $batchSize < 1) {
                throw new \InvalidArgumentException(
                    'Invalid value for environment variable ' . self::BATCH_SIZE_VARIABLE_NAME . '. '
                    . 'Should be integer, >= 1 and < value of PHP_INT_MAX'
                );
            }
            return $batchSize;
        }
        return self::DEFAULT_BATCH_SIZE;
    }
}
