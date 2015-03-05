<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

class ConfigOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigOptions
     */
    private $object;

    protected function setUp()
    {
        $random = $this->getMock('Magento\Framework\Math\Random', [], [], '', false);
        $random->expects($this->any())->method('getRandomString')->willReturn('key');
        $loader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $loader->expects($this->any())->method('load')->willReturn(['module1', 'module2']);
        $deployConfig= $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $deployConfig->expects($this->any())->method('isAvailable')->willReturn(false);
        $this->object = new ConfigOptions($random, $loader, $deployConfig);
    }

    public function testGetOptions()
    {
        $options = $this->object->getOptions();
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[0]);
        $this->assertInstanceOf('Magento\Framework\Setup\Option\SelectConfigOption', $options[1]);
        $this->assertEquals(10, count($options));
    }

    public function testCreateConfig()
    {
        $config = $this->object->createConfig([
            ConfigOptions::INPUT_KEY_CRYPT_KEY => 'key',
            ConfigOptions::INPUT_KEY_SESSION_SAVE => 'db',
            ConfigOptions::INPUT_KEY_DB_HOST => 'localhost',
            ConfigOptions::INPUT_KEY_DB_NAME => 'dbName',
            ConfigOptions::INPUT_KEY_DB_USER => 'dbPass',
        ]);
        $this->assertEquals(5, count($config));
        $this->assertNotEmpty($config[0]->getData()['date']);
        $this->assertNotEmpty($config[1]->getData()['key']);
        $this->assertEquals('key', $config[1]->getData()['key']);
        $this->assertEquals(2, count($config[2]->getData()));
        $this->assertNotEmpty($config[3]->getData()['save']);
        $this->assertEquals('db', $config[3]->getData()['save']);
    }

    public function testCreateConfigNoSessionSave()
    {
        $config = $this->object->createConfig([
            ConfigOptions::INPUT_KEY_CRYPT_KEY => 'key',
            ConfigOptions::INPUT_KEY_DB_HOST => 'localhost',
            ConfigOptions::INPUT_KEY_DB_NAME => 'dbName',
            ConfigOptions::INPUT_KEY_DB_USER => 'dbPass',
        ]);
        $this->assertNotEmpty($config[3]);
        $this->assertEquals('files', $config[3]->getData()['save']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid session save location.
     */
    public function testCreateConfigInvalidSessionSave()
    {
        $this->object->createConfig([
            ConfigOptions::INPUT_KEY_SESSION_SAVE => 'invalid',
            ConfigOptions::INPUT_KEY_DB_HOST => 'localhost',
            ConfigOptions::INPUT_KEY_DB_NAME => 'dbName',
            ConfigOptions::INPUT_KEY_DB_USER => 'dbPass',
        ]);
    }

    /**
     * @param array $options
     * @dataProvider createConfigNoKeyDataProvider
     */
    public function testCreateConfigNoKey(array $options)
    {
        $config = $this->object->createConfig($options);
        $this->assertEquals(md5('key'), $config[1]->getData()['key']);
    }

    /**
     * @return array
     */
    public function createConfigNoKeyDataProvider()
    {
        return [
            'no key data' => [[
                ConfigOptions::INPUT_KEY_DB_HOST => 'localhost',
                ConfigOptions::INPUT_KEY_DB_NAME => 'dbName',
                ConfigOptions::INPUT_KEY_DB_USER => 'dbPass',
            ]],
            'no frontName' => [[
                'something_else' => 'something',
                ConfigOptions::INPUT_KEY_DB_HOST => 'localhost',
                ConfigOptions::INPUT_KEY_DB_NAME => 'dbName',
                ConfigOptions::INPUT_KEY_DB_USER => 'dbPass',
            ]],
        ];
    }

    /**
     * @param array $options
     *
     * @dataProvider createConfigInvalidKeyDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid encryption key.
     */
    public function testCreateConfigInvalidKey(array $options)
    {
        $this->object->createConfig($options);
    }

    /**
     * @return array
     */
    public function createConfigInvalidKeyDataProvider()
    {
        return [
            [[ConfigOptions::INPUT_KEY_CRYPT_KEY => '']],
            [[ConfigOptions::INPUT_KEY_CRYPT_KEY => '0']],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing value for db configuration: db_user
     */
    public function testCreateConfigInvalidDB()
    {
        $data = [
            ConfigOptions::INPUT_KEY_DB_HOST => 'localhost',
            ConfigOptions::INPUT_KEY_DB_NAME => 'dbName',
        ];
        $this->object->createConfig($data);
    }
}
