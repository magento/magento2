<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Model\Data\ReEncryptorList\CoreConfigDataReEncryptor;

use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\Initial;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Test for the core configuration re-encryption handler.
 */
class HandlerTest extends TestCase
{
    /**
     * @var Config|null
     */
    private ?Config $config;

    /**
     * @var ConfigResource|null
     */
    private ?ConfigResource $configResource;

    /**
     * @var EncryptorInterface|null
     */
    private ?EncryptorInterface $encryptor;

    /**
     * Initialize dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = Bootstrap::getObjectManager()->get(
            Config::class
        );

        $this->configResource = Bootstrap::getObjectManager()->get(
            ConfigResource::class
        );

        $this->encryptor = Bootstrap::getObjectManager()->get(
            EncryptorInterface::class
        );
    }

    /**
     * Test ReEncryption for core config data
     *
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testReEncrypt():void
    {
        $testConfigPath1 = "test/correct_enc_value";
        $testConfigPath2 = "test/incorrect_enc_value";
        $testConfigPath3 = "test/empty_enc_value";

        $this->configResource->saveConfig(
            $testConfigPath1,
            $this->encryptor->encrypt("Encrypted Config Value")
        );
        $this->configResource->saveConfig(
            $testConfigPath2,
            substr_replace(
                $this->encryptor->encrypt("Encrypted Config Value"),
                "9",
                2,
                1
            )
        );
        $this->configResource->saveConfig($testConfigPath3, "");

        $this->config->clean();

        $configValue1BeforeReEncryption = $this->config->getValue($testConfigPath1);
        $configValue2BeforeReEncryption = $this->config->getValue($testConfigPath2);

        /** @var Handler $coreConfigDataReEncryptionHandler */
        $coreConfigDataReEncryptionHandler = Bootstrap::getObjectManager()->create(
            Handler::class
        );

        try {
            $errors = $coreConfigDataReEncryptionHandler->reEncrypt();
        } catch (\Throwable $e) {
            $this->fail(
                sprintf(
                    'Re-encryption failed: %s',
                    $e->getMessage()
                )
            );
        }

        // Asserting that the handler reacts properly to DB row level errors
        // during re-encryption.
        $this->assertEquals(1, count($errors));
        $this->assertEquals("config_id", $errors[0]->getRowIdField());
        $this->assertEquals("Not supported cipher version", $errors[0]->getMessage());

        $this->config->clean();

        $configValue1AfterReEncryption = $this->config->getValue($testConfigPath1);
        $configValue2AfterReEncryption = $this->config->getValue($testConfigPath2);
        $configValue3AfterReEncryption = $this->config->getValue($testConfigPath3);

        // Asserting changes done by re-encryption the first test configuration value.
        // Encrypted value that was not empty should not be empty.
        $this->assertNotEmpty($configValue1AfterReEncryption);
        // Encrypted value should be changed.
        $this->assertNotEquals(
            $configValue1BeforeReEncryption,
            $configValue1AfterReEncryption
        );
        // It still should be possible to decrypt the value.
        $this->assertEquals(
            "Encrypted Config Value",
            $this->encryptor->decrypt($configValue1AfterReEncryption)
        );

        // Asserting changes done by re-encryption the second test configuration value.
        // Encrypted field should stay unchanged if DB row level error occurred.
        $this->assertEquals(
            $configValue2BeforeReEncryption,
            $configValue2AfterReEncryption
        );

        // Asserting changes done by re-encryption the third test configuration value.
        // Empty or NULL values should stay unchanged.
        $this->assertEmpty($configValue3AfterReEncryption);
    }
}
