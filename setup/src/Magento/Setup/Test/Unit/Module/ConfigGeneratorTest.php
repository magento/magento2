<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\Data\ConfigDataFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Model\ConfigGenerator;
use Magento\Setup\Model\ConfigOptionsList\DriverOptions;
use Magento\Setup\Model\CryptKeyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Setup\Model\ConfigGenerator class.
 */
class ConfigGeneratorTest extends TestCase
{
    /**
     * @var ConfigGenerator
     */
    private $configGeneratorObject;

    protected function setUp(): void
    {
        /** @var DeploymentConfig|MockObject $deployConfig */
        $deployConfig = $this->createMock(DeploymentConfig::class);
        $deployConfig->expects($this->any())->method('isAvailable')->willReturn(false);

        /** @var Random|MockObject $randomMock */
        $randomMock = $this->createMock(Random::class);
        $randomMock->expects($this->any())->method('getRandomString')->willReturn('key');

        $cryptKeyGenerator = new CryptKeyGenerator($randomMock);

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->method('create')->willReturn(new ConfigData('app_env'));

        $configDataFactoryMock = (new ObjectManager($this))
            ->getObject(ConfigDataFactory::class, ['objectManager' => $objectManagerMock]);

        $driverOptions = $this->getMockBuilder(DriverOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDriverOptions'])
            ->getMock();

        $this->configGeneratorObject = new ConfigGenerator(
            $randomMock,
            $deployConfig,
            $configDataFactoryMock,
            $cryptKeyGenerator,
            $driverOptions
        );
    }

    public function testCreateCryptConfigWithInput()
    {
        $testData = [ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY => 'some-test_key'];
        $returnValue = $this->configGeneratorObject->createCryptConfig($testData);
        $this->assertEquals(ConfigFilePool::APP_ENV, $returnValue->getFileKey());
        $this->assertEquals(['crypt' => ['key' => 'some-test_key']], $returnValue->getData());
    }

    public function testCreateCryptConfigWithoutInput()
    {
        $returnValue = $this->configGeneratorObject->createCryptConfig([]);
        $this->assertEquals(ConfigFilePool::APP_ENV, $returnValue->getFileKey());
        // phpcs:ignore Magento2.Security.InsecureFunction
        $this->assertEquals(['crypt' => ['key' => md5('key')]], $returnValue->getData());
    }

    public function testCreateSessionConfigWithInput()
    {
        $testData = [ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE => 'files'];
        $returnValue = $this->configGeneratorObject->createSessionConfig($testData);
        $this->assertEquals(ConfigFilePool::APP_ENV, $returnValue->getFileKey());
        $this->assertEquals(
            ['session' => ['save' => ConfigOptionsListConstants::SESSION_SAVE_FILES]],
            $returnValue->getData()
        );

        $testData = [ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE => 'db'];
        $returnValue = $this->configGeneratorObject->createSessionConfig($testData);
        $this->assertEquals(ConfigFilePool::APP_ENV, $returnValue->getFileKey());
        $this->assertEquals(
            ['session' => ['save' => ConfigOptionsListConstants::SESSION_SAVE_DB]],
            $returnValue->getData()
        );
    }

    public function testCreateSessionConfigWithoutInput()
    {
        $returnValue = $this->configGeneratorObject->createSessionConfig([]);
        $this->assertEquals(ConfigFilePool::APP_ENV, $returnValue->getFileKey());
        $this->assertEquals([], $returnValue->getData());
    }

    public function testCreateDbConfig()
    {
        $testData = [
            ConfigOptionsListConstants::INPUT_KEY_DB_HOST => 'testLocalhost',
            ConfigOptionsListConstants::INPUT_KEY_DB_NAME => 'testDbName',
            ConfigOptionsListConstants::INPUT_KEY_DB_USER => 'testDbUser',
            ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX => 'testSomePrefix',
        ];
        $returnValue = $this->configGeneratorObject->createDbConfig($testData);
        $this->assertEquals(ConfigFilePool::APP_ENV, $returnValue->getFileKey());
        $dbData = $returnValue->getData();
        $dbData = $dbData['db'];
        $this->assertArrayHasKey('table_prefix', $dbData);
        $this->assertSame('testSomePrefix', $dbData['table_prefix']);
        $this->assertArrayHasKey('connection', $dbData);
        $this->assertArrayHasKey('default', $dbData['connection']);
        $this->assertArrayHasKey('host', $dbData['connection']['default']);
        $this->assertSame('testLocalhost', $dbData['connection']['default']['host']);
        $this->assertArrayHasKey('dbname', $dbData['connection']['default']);
        $this->assertSame('testDbName', $dbData['connection']['default']['dbname']);
        $this->assertArrayHasKey('username', $dbData['connection']['default']);
        $this->assertSame('testDbUser', $dbData['connection']['default']['username']);
        $this->assertArrayHasKey('active', $dbData['connection']['default']);
        $this->assertSame('1', $dbData['connection']['default']['active']);
    }

    public function testCreateResourceConfig()
    {
        $returnValue = $this->configGeneratorObject->createResourceConfig();
        $this->assertEquals(ConfigFilePool::APP_ENV, $returnValue->getFileKey());
        $this->assertEquals(['resource' => ['default_setup' => ['connection' => 'default']]], $returnValue->getData());
    }
}
