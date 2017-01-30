<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ConfigGenerator;
use Magento\Setup\Model\ConfigOptionsList;
use Magento\Setup\Validator\DbValidator;
use Magento\Framework\Config\ConfigOptionsListConstants;

class ConfigOptionsListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigOptionsList
     */
    private $object;

    /**
     * @var ConfigGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var DbValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbValidator;

    protected function setUp()
    {
        $this->generator = $this->getMock('Magento\Setup\Model\ConfigGenerator', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->dbValidator = $this->getMock('Magento\Setup\Validator\DbValidator', [], [], '', false);
        $this->object = new ConfigOptionsList($this->generator, $this->dbValidator);
    }

    public function testGetOptions()
    {
        $options = $this->object->getOptions();
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[0]);
        $this->assertSame('Encryption key', $options[0]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\SelectConfigOption', $options[1]);
        $this->assertSame('Session save handler', $options[1]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\SelectConfigOption', $options[2]);
        $this->assertSame('Type of definitions used by Object Manager', $options[2]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[3]);
        $this->assertSame('Database server host', $options[3]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[4]);
        $this->assertSame('Database name', $options[4]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[5]);
        $this->assertSame('Database server username', $options[5]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[6]);
        $this->assertSame('Database server engine', $options[6]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[7]);
        $this->assertSame('Database server password', $options[7]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[8]);
        $this->assertSame('Database table prefix', $options[8]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[9]);
        $this->assertSame('Database type', $options[9]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[10]);
        $this->assertSame('Database  initial set of commands', $options[10]->getDescription());
        $this->assertInstanceOf('Magento\Framework\Setup\Option\FlagConfigOption', $options[11]);
        $this->assertSame(
            'If specified, then db connection validation will be skipped',
            $options[11]->getDescription()
        );
        $this->assertInstanceOf('Magento\Framework\Setup\Option\TextConfigOption', $options[12]);
        $this->assertSame('http Cache hosts', $options[12]->getDescription());
        $this->assertEquals(13, count($options));
    }

    public function testCreateOptions()
    {
        $configDataMock = $this->getMock('Magento\Framework\Config\Data\ConfigData', [], [], '', false);
        $this->generator->expects($this->once())->method('createCryptConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createSessionConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createDefinitionsConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createDbConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createResourceConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createXFrameConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createCacheHostsConfig')->willReturn($configDataMock);
        $configData = $this->object->createConfig([], $this->deploymentConfig);
        $this->assertEquals(8, count($configData));
    }

    public function testCreateOptionsWithOptionalNull()
    {
        $configDataMock = $this->getMock('Magento\Framework\Config\Data\ConfigData', [], [], '', false);
        $this->generator->expects($this->once())->method('createCryptConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createSessionConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createDefinitionsConfig')->willReturn(null);
        $this->generator->expects($this->once())->method('createDbConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createResourceConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createXFrameConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createCacheHostsConfig')->willReturn($configDataMock);
        $configData = $this->object->createConfig([], $this->deploymentConfig);
        $this->assertEquals(7, count($configData));
    }

    public function testValidateSuccess()
    {
        $options = [
            ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX => 'prefix',
            ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE => 'files',
            ConfigOptionsListConstants::INPUT_KEY_SKIP_DB_VALIDATION => false,
            ConfigOptionsListConstants::INPUT_KEY_DB_NAME => 'name',
            ConfigOptionsListConstants::INPUT_KEY_DB_HOST => 'host',
            ConfigOptionsListConstants::INPUT_KEY_DB_USER => 'user',
            ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD => 'pass'
        ];
        $this->prepareValidationMocks();
        $this->assertEquals([], $this->object->validate($options, $this->deploymentConfig));
    }

    public function testValidateInvalidSessionHandler()
    {
        $invalidSaveHandler = 'clay-tablet';

        $options = [
            ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX => 'prefix',
            ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE => $invalidSaveHandler,
            ConfigOptionsListConstants::INPUT_KEY_SKIP_DB_VALIDATION => false,
            ConfigOptionsListConstants::INPUT_KEY_DB_NAME => 'name',
            ConfigOptionsListConstants::INPUT_KEY_DB_HOST => 'host',
            ConfigOptionsListConstants::INPUT_KEY_DB_USER => 'user',
            ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD => 'pass'
        ];
        $this->prepareValidationMocks();
        $this->assertEquals(
            ["Invalid session handler '$invalidSaveHandler'"],
            $this->object->validate($options, $this->deploymentConfig)
        );
    }

    private function prepareValidationMocks()
    {
        $configDataMock = $this->getMockBuilder('Magento\Framework\Config\Data\ConfigData')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbValidator->expects($this->once())->method('checkDatabaseTablePrefix')->willReturn($configDataMock);
        $this->dbValidator->expects($this->once())->method('checkDatabaseConnection')->willReturn($configDataMock);
    }

    /**
     * @param string $hosts
     * @param bool $expectedError
     * @dataProvider validateCacheHostsDataProvider
     */
    public function testValidateCacheHosts($hosts, $expectedError)
    {
        $options = [
            ConfigOptionsListConstants::INPUT_KEY_SKIP_DB_VALIDATION => true,
            ConfigOptionsListConstants::INPUT_KEY_CACHE_HOSTS => $hosts
        ];
        $result = $this->object->validate($options, $this->deploymentConfig);
        if ($expectedError) {
            $this->assertCount(1, $result);
            $this->assertEquals("Invalid http cache hosts '$hosts'", $result[0]);
        } else {
            $this->assertCount(0, $result);
        }

    }

    public function validateCacheHostsDataProvider()
    {
        return [
            ['localhost', false],
            ['122.11.2.34:800', false],
            ['122.11.2.34:800,localhost', false],
            ['website.com:9000', false],
            ['website.com/m2ce:9000', true],
            ['website.com+:9000', true],
        ];
    }
}
