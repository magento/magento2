<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Model\Data\ReEncryptorList\CoreConfigDataReEncryptor;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor\HandlerInterface;
use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor\Handler\ErrorFactory;
use Magento\Framework\DB\Query\Generator;

/**
 * Handler for core configuration re-encryption.
 */
class Handler implements HandlerInterface
{
    /**
     * @var string
     */
    private const PATTERN = "^[[:digit:]]+:[[:digit:]]+:.*$";

    /**
     * @var string
     */
    private const TABLE_NAME = "core_config_data";

    /**
     * @var int
     */
    private const BATCH_SIZE = 1000;

    /**
     * @var string
     */
    private const IDENTIFIER = "config_id";

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var ErrorFactory
     */
    private ErrorFactory $errorFactory;

    /**
     * @var Generator
     */
    private Generator $queryGenerator;

    /**
     * @param EncryptorInterface $encryptor
     * @param ResourceConnection $resourceConnection
     * @param ErrorFactory $errorFactory
     * @param Generator $queryGenerator
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ResourceConnection $resourceConnection,
        ErrorFactory $errorFactory,
        Generator $queryGenerator
    ) {
        $this->encryptor = $encryptor;
        $this->resourceConnection = $resourceConnection;
        $this->errorFactory = $errorFactory;
        $this->queryGenerator = $queryGenerator;
    }

    /**
     * @inheritDoc
     */
    public function reEncrypt(): array
    {
        $errors = [];
        $tableName = $this->resourceConnection->getTableName(
            self::TABLE_NAME
        );
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from($tableName, ['config_id', 'value'])
            ->where('value != ?', '')
            ->where('value IS NOT NULL')
            ->where('value REGEXP ?', self::PATTERN);

        $iterator = $this->queryGenerator->generate(
            self::IDENTIFIER,
            $select,
            self::BATCH_SIZE
        );

        foreach ($iterator as $batch) {
            foreach ($connection->fetchAll($batch) as $row) {
                try {
                    $connection->update(
                        $tableName,
                        ['value' => $this->encryptor->encrypt($this->encryptor->decrypt($row['value']))],
                        ['config_id = ?' => $row['config_id']]
                    );
                } catch (\Throwable $e) {
                    $errors[] = $this->errorFactory->create(
                        self::IDENTIFIER,
                        $row['config_id'],
                        $e->getMessage()
                    );

                    continue;
                }
            }
        }

        return $errors;
    }
}
