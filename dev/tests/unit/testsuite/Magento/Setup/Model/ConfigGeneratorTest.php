<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Config\File\ConfigFilePool;

class ConfigGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigGenerator
     */
    private $configGeneratorObject;

    protected function setUp()
    {
        $random = $this->getMock('Magento\Framework\Math\Random', [], [], '', false);
        $random->expects($this->any())->method('getRandomString')->willReturn('key');
        $loader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $loader->expects($this->any())->method('load')->willReturn([
                'module1' => ['name' => 'module_one', 'version' => '1.0.0'],
                'module2' => ['name' => 'module_two', 'version' => '1.0.0']
            ]
        );
        $deployConfig= $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $deployConfig->expects($this->any())->method('isAvailable')->willReturn(false);
        $this->configGeneratorObject = new ConfigGenerator($random, $loader, $deployConfig);
    }

    public function testCreateInstallConfig()
    {
        $returnValue = $this->configGeneratorObject->createInstallConfig();
        $this->assertInstanceOf('Magento\Framework\Config\Data\ConfigData', $returnValue);
        $this->assertEquals('install', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
    }

    public function testCreateCryptConfigWithInput()
    {
        $testData = [ConfigOptions::INPUT_KEY_CRYPT_KEY => 'some-test_key'];
        $returnValue = $this->configGeneratorObject->createCryptConfig($testData);
        $this->assertEquals('crypt', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
        $this->assertEquals(['key' => 'some-test_key'], $returnValue->getData());
    }

    public function testCreateCryptConfigWithoutInput()
    {
        $returnValue = $this->configGeneratorObject->createCryptConfig([]);
        $this->assertEquals('crypt', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
        $this->assertEquals(['key' => md5('key')], $returnValue->getData());
    }

    public function testCreateModuleConfig()
    {
        $returnValue = $this->configGeneratorObject->createModuleConfig();
        $this->assertEquals('modules', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
        $this->assertEquals(['module1' => 1, 'module2' => 1], $returnValue->getData());
    }

    public function testCreateSessionConfigWithInput()
    {
        $testData = [ConfigOptions::INPUT_KEY_SESSION_SAVE => 'files'];
        $returnValue = $this->configGeneratorObject->createSessionConfig($testData);
        $this->assertEquals('session', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
        $this->assertEquals(['save' => ConfigOptions::SESSION_SAVE_FILES], $returnValue->getData());

        $testData = [ConfigOptions::INPUT_KEY_SESSION_SAVE => 'db'];
        $returnValue = $this->configGeneratorObject->createSessionConfig($testData);
        $this->assertEquals('session', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
        $this->assertEquals(['save' => ConfigOptions::SESSION_SAVE_DB], $returnValue->getData());
    }

    public function testCreateSessionConfigWithoutInput()
    {
        $returnValue = $this->configGeneratorObject->createSessionConfig([]);
        $this->assertEquals('session', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
        $this->assertEquals(['save' => ConfigOptions::SESSION_SAVE_FILES], $returnValue->getData());
    }

    public function testCreateDefinitionsConfig()
    {
        $testData = [ConfigOptions::INPUT_KEY_DEFINITION_FORMAT => 'test-format'];
        $returnValue = $this->configGeneratorObject->createDefinitionsConfig($testData);
        $this->assertEquals('definition', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
        $this->assertEquals(['format' => 'test-format'], $returnValue->getData());
    }

    public function testCreateDbConfig()
    {
        $testData = [
            ConfigOptions::INPUT_KEY_DB_HOST => 'testLocalhost',
            ConfigOptions::INPUT_KEY_DB_NAME => 'testDbName',
            ConfigOptions::INPUT_KEY_DB_USER => 'testDbUser',
            ConfigOptions::INPUT_KEY_DB_PREFIX => 'testSomePrefix',
        ];
        $returnValue = $this->configGeneratorObject->createDbConfig($testData);
        $this->assertEquals('db', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
        $dbData = $returnValue->getData();
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
        $this->assertArrayHasKey('password', $dbData['connection']['default']);
        $this->assertSame('', $dbData['connection']['default']['password']);
        $this->assertArrayHasKey('model', $dbData['connection']['default']);
        $this->assertSame('mysql4', $dbData['connection']['default']['model']);
        $this->assertArrayHasKey('initStatements', $dbData['connection']['default']);
        $this->assertSame('SET NAMES utf8;', $dbData['connection']['default']['initStatements']);
        $this->assertArrayHasKey('active', $dbData['connection']['default']);
        $this->assertSame('1', $dbData['connection']['default']['active']);
    }

    public function testCreateResourceConfig()
    {
        $returnValue = $this->configGeneratorObject->createResourceConfig();
        $this->assertEquals('resource', $returnValue->getSegmentKey());
        $this->assertEquals(ConfigFilePool::APP_CONFIG, $returnValue->getFileKey());
        $this->assertEquals(['resource' => ['default_setup' => ['connection' => 'default']]], $returnValue->getData());
    }
}
