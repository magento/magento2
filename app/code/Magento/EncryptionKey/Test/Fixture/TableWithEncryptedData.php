<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\Module\Setup;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Fixture that creates custom DB table filled with encrypted data.
 */
class TableWithEncryptedData implements RevertibleDataFixtureInterface
{
    /**
     * @var string
     */
    private const TABLE_NAME = "test_table_with_encrypted_data";

    /**
     * @var Setup
     */
    private Setup $setup;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @param Setup $setup
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Setup $setup,
        EncryptorInterface $encryptor
    ) {
        $this->setup = $setup;
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(array $data = []): ?DataObject
    {
        $connection = $this->setup->getConnection();

        $tableName = $this->setup->getTable(self::TABLE_NAME);

        $table = $connection->newTable($tableName);

        $table->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'not_enc_column_1',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Not Encrypted Column'
        )->addColumn(
            'enc_column_1',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Encrypted Column One'
        )->addColumn(
            'enc_column_2',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Encrypted Column Two'
        )->setComment(
            'Test table with encrypted data.'
        );

        $connection->createTable($table);

        $connection->insertArray(
            $tableName,
            ["not_enc_column_1", "enc_column_1", "enc_column_2"],
            [
                [
                    "Not Encrypted Column Value",
                    "",
                    null
                ],
                [
                    "Not Encrypted Column Value",
                    $this->encryptor->encrypt("Encrypted Column Value"),
                    ""
                ],
                [
                    "Not Encrypted Column Value",
                    $this->encryptor->encrypt("Encrypted Column Value"),
                    $this->encryptor->encrypt("Encrypted Column Value")
                ],
                [
                    "Not Encrypted Column Value",
                    substr_replace(
                        $this->encryptor->encrypt("Encrypted Column Value"),
                        "9",
                        2,
                        1
                    ),
                    ""
                ]
            ]
        );

        return null;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->setup->getConnection()->dropTable(
            $this->setup->getTable(self::TABLE_NAME)
        );
    }
}
