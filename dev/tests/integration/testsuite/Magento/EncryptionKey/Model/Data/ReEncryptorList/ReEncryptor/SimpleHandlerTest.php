<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\EncryptionKey\Test\Fixture\TableWithEncryptedData;
use Magento\EncryptionKey\Model\Data\ReEncryptorList\ReEncryptor\Handler\ErrorFactory;

/**
 * Test for the Simple Re-encryption Handler.
 */
class SimpleHandlerTest extends TestCase
{
    /**
     * @var ErrorFactory|null
     */
    private ?ErrorFactory $errorFactory;

    /**
     * @var EncryptorInterface|null
     */
    private ?EncryptorInterface $encryptor;

    /**
     * @var ResourceConnection|null
     */
    private ?ResourceConnection $resourceConnection;

    protected function setUp(): void
    {
        $this->errorFactory = Bootstrap::getObjectManager()->get(
            ErrorFactory::class
        );

        $this->encryptor = Bootstrap::getObjectManager()->get(
            EncryptorInterface::class
        );

        $this->resourceConnection = Bootstrap::getObjectManager()->get(
            ResourceConnection::class
        );
    }

    /**
     * Tests data re-encryption.
     */
    #[
        DataFixture(TableWithEncryptedData::class)
    ]
    public function testReEncrypt()
    {
        /** @var SimpleHandler $testDataReEncryptionHandler */
        $testDataReEncryptionHandler = Bootstrap::getObjectManager()->create(
            SimpleHandler::class,
            [
                "tableName" => "test_table_with_encrypted_data",
                "identifierField" => "id",
                "fieldsToReEncrypt" => ["enc_column_1", "enc_column_2"]
            ]
        );

        $tableName = $this->resourceConnection->getTableName(
            "test_table_with_encrypted_data"
        );

        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            $tableName,
            ["id", "not_enc_column_1", "enc_column_1", "enc_column_2"]
        )->order(
            "id ASC"
        );

        $dataBeforeReEncryption = $connection->fetchAll($select);

        try {
            $errors = $testDataReEncryptionHandler->reEncrypt();
        } catch (\Throwable $e) {
            $this->fail(
                sprintf(
                    'Re-encryption failed: %s',
                    $e->getMessage()
                )
            );
        }

        $expectedErrors[] = $this->errorFactory->create(
            "id",
            4,
            "Not supported cipher version"
        );

        $this->assertEquals($expectedErrors, $errors);

        $dataAfterReEncryption = $connection->fetchAll($select);

        $this->assertRowOne($dataBeforeReEncryption, $dataAfterReEncryption);
        $this->assertRowTwo($dataBeforeReEncryption, $dataAfterReEncryption);
        $this->assertRowThree($dataBeforeReEncryption, $dataAfterReEncryption);
        $this->assertRowFour($dataBeforeReEncryption, $dataAfterReEncryption);
    }

    /**
     * Asserts changes done by re-encryption to the first DB row.
     *
     * @param array $dataBeforeReEncryption
     * @param array $dataAfterReEncryption
     *
     * @return void
     */
    private function assertRowOne(array $dataBeforeReEncryption, array $dataAfterReEncryption): void
    {
        // Fields not supposed to be affected should stay unchanged.
        $this->assertEquals(
            $dataBeforeReEncryption[0]["not_enc_column_1"],
            $dataAfterReEncryption[0]["not_enc_column_1"]
        );

        // Empty or NULL fields should stay unchanged.
        $this->assertEmpty($dataAfterReEncryption[0]["enc_column_1"]);
        $this->assertEmpty($dataAfterReEncryption[0]["enc_column_2"]);
    }

    /**
     * Asserts changes done by re-encryption to the second DB row.
     *
     * @param array $dataBeforeReEncryption
     * @param array $dataAfterReEncryption
     *
     * @return void
     */
    private function assertRowTwo(array $dataBeforeReEncryption, array $dataAfterReEncryption): void
    {
        // Fields not supposed to be affected should stay unchanged.
        $this->assertEquals(
            $dataBeforeReEncryption[1]["not_enc_column_1"],
            $dataAfterReEncryption[1]["not_enc_column_1"]
        );

        // Encrypted fields that were not empty should not be empty.
        $this->assertNotEmpty($dataAfterReEncryption[1]["enc_column_1"]);

        // Empty or NULL fields should stay unchanged.
        $this->assertEmpty($dataAfterReEncryption[1]["enc_column_2"]);

        // Encrypted fields should be changed.
        $this->assertNotEquals(
            $dataBeforeReEncryption[1]["enc_column_1"],
            $dataAfterReEncryption[1]["enc_column_1"]
        );

        // It still should be possible to decrypt encrypted fields.
        $this->assertEquals(
            "Encrypted Column Value",
            $this->encryptor->decrypt($dataAfterReEncryption[1]["enc_column_1"])
        );
    }

    /**
     * Asserts changes done by re-encryption to the third DB row.
     *
     * @param array $dataBeforeReEncryption
     * @param array $dataAfterReEncryption
     *
     * @return void
     */
    private function assertRowThree(array $dataBeforeReEncryption, array $dataAfterReEncryption): void
    {
        // Fields not supposed to be affected should stay unchanged.
        $this->assertEquals(
            $dataBeforeReEncryption[2]["not_enc_column_1"],
            $dataAfterReEncryption[2]["not_enc_column_1"]
        );

        // Encrypted fields that were not empty should not be empty.
        $this->assertNotEmpty($dataAfterReEncryption[2]["enc_column_1"]);
        $this->assertNotEmpty($dataAfterReEncryption[2]["enc_column_2"]);

        // Encrypted fields should be changed.
        $this->assertNotEquals(
            $dataBeforeReEncryption[2]["enc_column_1"],
            $dataAfterReEncryption[2]["enc_column_1"]
        );
        $this->assertNotEquals(
            $dataBeforeReEncryption[2]["enc_column_2"],
            $dataAfterReEncryption[2]["enc_column_2"]
        );

        // It still should be possible to decrypt encrypted fields.
        $this->assertEquals(
            "Encrypted Column Value",
            $this->encryptor->decrypt($dataAfterReEncryption[2]["enc_column_1"])
        );
        $this->assertEquals(
            "Encrypted Column Value",
            $this->encryptor->decrypt($dataAfterReEncryption[2]["enc_column_2"])
        );
    }

    /**
     * Asserts changes done by re-encryption to the forth DB row.
     *
     * @param array $dataBeforeReEncryption
     * @param array $dataAfterReEncryption
     *
     * @return void
     */
    private function assertRowFour(array $dataBeforeReEncryption, array $dataAfterReEncryption): void
    {
        // Fields not supposed to be affected should stay unchanged.
        $this->assertEquals(
            $dataBeforeReEncryption[3]["not_enc_column_1"],
            $dataAfterReEncryption[3]["not_enc_column_1"]
        );

        // Encrypted fields should stay unchanged if DB row level error occurred.
        $this->assertEquals(
            $dataBeforeReEncryption[3]["enc_column_1"],
            $dataAfterReEncryption[3]["enc_column_1"]
        );

        // Empty or NULL fields should stay unchanged.
        $this->assertEmpty($dataAfterReEncryption[3]["enc_column_2"]);
    }
}
