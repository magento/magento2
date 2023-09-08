<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\EncryptionKey\Setup\Patch\Data;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\Encryptor;

/**
 * Class SodiumChachaPatch library test
 */
class SodiumChachaPatchTest extends \PHPUnit\Framework\TestCase
{
    private const PATH_KEY = 'crypt/key';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DeploymentConfig
     */
    private $deployConfig;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->deployConfig = $this->objectManager->get(DeploymentConfig::class);
    }

    public function testChangeEncryptionKey()
    {
        $testPath = 'test/config';
        $testValue = 'test';

        $structureMock = $this->createMock(
            // phpstan:ignore "Class Magento\Config\Model\Config\Structure\Proxy not found."
            \Magento\Config\Model\Config\Structure\Proxy::class
        );
        $structureMock->expects($this->once())
            ->method('getFieldPathsByAttribute')
            ->willReturn([$testPath]);
        $structureMock->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn([]);

        /** @var \Magento\Config\Model\ResourceModel\Config $configModel */
        $configModel = $this->objectManager->create(\Magento\Config\Model\ResourceModel\Config::class);
        $configModel->saveConfig($testPath, $this->legacyEncrypt($testValue), 'default', 0);

        /** @var \Magento\EncryptionKey\Setup\Patch\Data\SodiumChachaPatch $patch */
        $patch = $this->objectManager->create(
            \Magento\EncryptionKey\Setup\Patch\Data\SodiumChachaPatch::class,
            [
                'structure' => $structureMock,
            ]
        );
        $patch->apply();

        $connection = $configModel->getConnection();
        $values = $connection->fetchPairs(
            $connection->select()->from(
                $configModel->getMainTable(),
                ['config_id', 'value']
            )->where(
                'path IN (?)',
                [$testPath]
            )->where(
                'value NOT LIKE ?',
                ''
            )
        );

        /** @var \Magento\Framework\Encryption\EncryptorInterface $encyptor */
        $encyptor = $this->objectManager->get(\Magento\Framework\Encryption\EncryptorInterface::class);

        $rawConfigValue = array_pop($values);

        $this->assertNotEquals($testValue, $rawConfigValue);
        $this->assertStringStartsWith('0:' . Encryptor::CIPHER_LATEST . ':', $rawConfigValue);
        $this->assertEquals($testValue, $encyptor->decrypt($rawConfigValue));
    }

    private function legacyEncrypt(string $data): string
    {
        // @codingStandardsIgnoreStart
        $handle = @mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
        $initVectorSize = @mcrypt_enc_get_iv_size($handle);
        $initVector = str_repeat("\0", $initVectorSize);
        @mcrypt_generic_init($handle, $this->getEncryptionKey(), $initVector);

        $encrpted = @mcrypt_generic($handle, $data);

        @mcrypt_generic_deinit($handle);
        @mcrypt_module_close($handle);
        // @codingStandardsIgnoreEnd

        return '0:' . Encryptor::CIPHER_RIJNDAEL_256 . ':' . base64_encode($encrpted);
    }

    /**
     * Get Encryption key
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function getEncryptionKey(): string
    {
        $key = $this->deployConfig->get(static::PATH_KEY);
        return (str_starts_with($key, ConfigOptionsListConstants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX)) ?
        base64_decode(substr($key, strlen(ConfigOptionsListConstants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX))) :
        $key;
    }
}
