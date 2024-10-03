<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\DataConverter\DataConverterInterface;
use Magento\Framework\DB\Query\BatchRangeIteratorFactory;
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
    public const BATCH_SIZE_VARIABLE_NAME = 'DATA_CONVERTER_BATCH_SIZE';

    /**
     * Default batch size for data converter
     */
    public const DEFAULT_BATCH_SIZE = 50000;

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
     * @var BatchRangeIteratorFactory
     */
    private $batchIteratorFactory;

    /**
     * Constructor
     *
     * @param Generator $queryGenerator
     * @param DataConverterInterface $dataConverter
     * @param SelectFactory $selectFactory
     * @param string|null $envBatchSize
     * @param BatchRangeIteratorFactory|null $batchIteratorFactory
     */
    public function __construct(
        Generator $queryGenerator,
        DataConverterInterface $dataConverter,
        SelectFactory $selectFactory,
        $envBatchSize = null,
        ?BatchRangeIteratorFactory $batchIteratorFactory = null
    ) {
        $this->queryGenerator = $queryGenerator;
        $this->dataConverter = $dataConverter;
        $this->selectFactory = $selectFactory;
        $this->envBatchSize = $envBatchSize;
        $this->batchIteratorFactory = $batchIteratorFactory
            ?? ObjectManager::getInstance()->get(BatchRangeIteratorFactory::class);
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
        $identifiers = explode(',', (string)$identifier);
        if (count($identifiers) > 1) {
            $this->processTableWithCompositeIdentifier($connection, $table, $identifiers, $field, $queryModifier);
        } else {
            $this->processTableWithUniqueIdentifier($connection, $table, $identifier, $field, $queryModifier);
        }
    }

    /**
     * Convert table (with unique identifier) field data from one representation to another
     *
     * @param AdapterInterface $connection
     * @param string $table
     * @param string $identifier
     * @param string $field
     * @param QueryModifierInterface|null $queryModifier
     * @return void
     */
    private function processTableWithUniqueIdentifier(
        AdapterInterface $connection,
        $table,
        $identifier,
        $field,
        QueryModifierInterface $queryModifier = null
    ): void {
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
     * Convert table (with composite identifier) field data from one representation to another
     *
     * @param AdapterInterface $connection
     * @param string $table
     * @param array $identifiers
     * @param string $field
     * @param QueryModifierInterface|null $queryModifier
     * @return void
     */
    private function processTableWithCompositeIdentifier(
        AdapterInterface $connection,
        $table,
        $identifiers,
        $field,
        QueryModifierInterface $queryModifier = null
    ): void {
        $columns = $identifiers;
        $columns[] = $field;
        $select = $this->selectFactory->create($connection)
            ->from($table, $columns)
            ->where($field . ' IS NOT NULL');
        if ($queryModifier) {
            $queryModifier->modify($select);
        }
        $iterator = $this->batchIteratorFactory->create(
            [
                'batchSize' => $this->getBatchSize(),
                'select' => $select,
                'correlationName' => $table,
                'rangeField' => $identifiers,
                'rangeFieldAlias' => '',
            ]
        );
        foreach ($iterator as $selectByRange) {
            $rows = [];
            foreach ($connection->fetchAll($selectByRange) as $row) {
                $value = $row[$field];
                unset($row[$field]);
                $constraints = [];
                foreach ($row as $col => $val) {
                    $constraints[] = $connection->prepareSqlCondition($col, $val);
                }
                $rows[implode(' AND ', $constraints)] = $value;
            }
            $uniqueFieldDataArray = array_unique($rows);
            foreach ($uniqueFieldDataArray as $uniqueFieldData) {
                $constraints = array_keys($rows, $uniqueFieldData);
                try {
                    $convertedValue = $this->dataConverter->convert($uniqueFieldData);
                    if ($uniqueFieldData === $convertedValue) {
                        // Skip for data rows that have been already converted
                        continue;
                    }
                    $bind = [$field => $convertedValue];
                    foreach ($constraints as $where) {
                        $connection->update($table, $bind, $where);
                    }

                } catch (DataConversionException $e) {
                    throw new \Magento\Framework\DB\FieldDataConversionException(
                        sprintf(
                            \Magento\Framework\DB\FieldDataConversionException::MESSAGE_PATTERN,
                            $field,
                            $table,
                            implode(', ', $identifiers),
                            '(' . implode(') OR (', $constraints) . ')',
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
