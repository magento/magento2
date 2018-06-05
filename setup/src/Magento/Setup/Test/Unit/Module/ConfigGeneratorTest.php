<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Setup\Model\ConfigGenerator;
use Magento\Framework\Config\ConfigOptionsListConstants;

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
        $deployConfig= $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $deployConfig->expects($this->any())->method('isAvailable')->willReturn(false);
        $this->configGeneratorObject = new ConfigGenerator($random, $deployConfig);
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

    public function testCreateDefinitionsConfig()
    {
        $testData = [ConfigOptionsListConstants::INPUT_KEY_DEFINITION_FORMAT => 'test-format'];
        $returnValue = $this->configGeneratorObject->createDefinitionsConfig($testData);
        $this->assertEquals(ConfigFilePool::APP_ENV, $returnValue->getFileKey());
        $this->assertEquals(['definition' => ['format' => 'test-format']], $returnValue->getData());
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
