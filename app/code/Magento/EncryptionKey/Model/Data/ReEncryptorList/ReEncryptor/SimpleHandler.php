<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor;

use Magento\Framework\DB\Query\Generator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor\Handler\ErrorFactory;

/**
 * Generic re-encryption handler.
 *
 * Serves as a base for re-encryptors that simply try to re-encrypt specific columns
 * in a specific table without any complicated logic.
 */
class SimpleHandler implements HandlerInterface
{
    /**
     * @var string
     */
    private const DEFAULT_RESOURCE_NAME = 'default_setup';

    /**
     * @var int
     */
    private const QUERY_GENERATOR_BATCH_SIZE = 100000;

    /**
     * @var string
     */
    private string $tableName;

    /**
     * @var string
     */
    private string $identifierField;

    /**
     * @var array
     */
    private array $fieldsToReEncrypt;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var Generator
     */
    private Generator $queryGenerator;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var ErrorFactory
     */
    private ErrorFactory $errorFactory;

    /**
     * @var string
     */
    private string $resourceName;

    /**
     * @param string $tableName
     * @param string $identifierField
     * @param array $fieldsToReEncrypt
     * @param EncryptorInterface $encryptor
     * @param Generator $queryGenerator
     * @param ResourceConnection $resourceConnection
     * @param ErrorFactory $errorFactory
     * @param string $resourceName
     */
    public function __construct(
        string $tableName,
        string $identifierField,
        array $fieldsToReEncrypt,
        EncryptorInterface $encryptor,
        Generator $queryGenerator,
        ResourceConnection $resourceConnection,
        ErrorFactory $errorFactory,
        string $resourceName = self::DEFAULT_RESOURCE_NAME
    ) {
        $this->tableName = $tableName;
        $this->identifierField = $identifierField;
        $this->fieldsToReEncrypt = $fieldsToReEncrypt;
        $this->encryptor = $encryptor;
        $this->queryGenerator = $queryGenerator;
        $this->resourceConnection = $resourceConnection;
        $this->errorFactory = $errorFactory;
        $this->resourceName = $resourceName;
    }

    /**
     * @inheritDoc
     */
    public function reEncrypt(): array
    {
        $tableName = $this->resourceConnection->getTableName(
            $this->tableName,
            $this->resourceName
        );

        $connection = $this->resourceConnection->getConnection($this->resourceName);

        $iterator = $this->queryGenerator->generate(
            $this->identifierField,
            $connection->select()->from($tableName, $this->identifierField)
                ->columns($this->fieldsToReEncrypt),
            self::QUERY_GENERATOR_BATCH_SIZE
        );

        $errors = [];

        foreach ($iterator as $select) {
            foreach ($connection->fetchAll($select) as $row) {
                try {
                    $fieldsToUpdate = [];

                    foreach ($this->fieldsToReEncrypt as $field) {
                        // Skip empty fields.
                        if (!empty($row[$field])) {
                            $fieldsToUpdate[$field] = $this->encryptor->encrypt(
                                $this->encryptor->decrypt($row[$field])
                            );
                        }
                    }

                    if (!empty($fieldsToUpdate)) {
                        $connection->update(
                            $tableName,
                            $fieldsToUpdate,
                            [$this->identifierField . ' = ?' => $row[$this->identifierField]]
                        );
                    }
                } catch (\Throwable $e) {
                    $errors[] = $this->errorFactory->create(
                        $this->identifierField,
                        (int) $row[$this->identifierField],
                        $e->getMessage()
                    );

                    continue;
                }
            }
        }

        return $errors;
    }
}
