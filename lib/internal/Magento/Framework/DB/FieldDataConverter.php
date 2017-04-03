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
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select\QueryModifierInterface;

/**
 * Convert field data from one representation to another
 */
class FieldDataConverter
{
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
     * Constructor
     *
     * @param Generator $queryGenerator
     * @param DataConverterInterface $dataConverter
     * @param SelectFactory $selectFactory
     */
    public function __construct(
        Generator $queryGenerator,
        DataConverterInterface $dataConverter,
        SelectFactory $selectFactory = null
    ) {
        $this->queryGenerator = $queryGenerator;
        $this->dataConverter = $dataConverter;
        $this->selectFactory = $selectFactory ?: ObjectManager::getInstance()->get(SelectFactory::class);
    }

    /**
     * Convert table field data from one representation to another uses DataConverterInterface
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
        $iterator = $this->queryGenerator->generate($identifier, $select);
        foreach ($iterator as $selectByRange) {
            $rows = $connection->fetchAll($selectByRange);
            foreach ($rows as $row) {
                try {
                    $convertedValue = $this->dataConverter->convert($row[$field]);
                    if ($row[$field] === $convertedValue) {
                        // skip for data rows that have been already converted
                        continue;
                    }
                    $bind = [$field => $convertedValue];
                    $where = [$identifier . ' = ?' => (int) $row[$identifier]];
                    $connection->update($table, $bind, $where);
                } catch (DataConversionException $e) {
                    throw new \Magento\Framework\DB\FieldDataConversionException(
                        sprintf(
                            \Magento\Framework\DB\FieldDataConversionException::MESSAGE_PATTERN,
                            $field,
                            $table,
                            $identifier,
                            $row[$identifier],
                            get_class($this->dataConverter),
                            $e->getMessage()
                        )
                    );
                }
            }
        }
    }
}
