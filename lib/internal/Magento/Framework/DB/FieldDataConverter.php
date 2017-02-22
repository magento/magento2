<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\DataConverter\DataConversionException;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\DataConverter\DataConverterInterface;
use Magento\Framework\DB\Select\QueryModifierInterface;
use Magento\Framework\Exception\SerializationException;
use Magento\Setup\Exception;

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
     * @throws Exception
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
                    $bind = [$field => $this->dataConverter->convert($row[$field])];
                    $where = [$identifier . ' = ?' => (int) $row[$identifier]];
                    $connection->update($table, $bind, $where);
                } catch (DataConversionException $e) {
                    throw new Exception(
                        sprintf(
                            "Error converting field `%s` in table `%s` where `%s`=%s using %s."
                            . PHP_EOL
                            . "Fix data or replace with valid value."
                            . PHP_EOL
                            . "Failure reason: '%s'",
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
