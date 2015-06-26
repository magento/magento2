<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module;

use Magento\Setup\Model\ConfigGenerator;
use Magento\Setup\Model\ConfigOptionsList;
use Magento\Setup\Validator\DbValidator;

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
        $this->assertSame('Session save location', $options[1]->getDescription());
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
        $this->assertEquals(12, count($options));
    }

    public function testCreateOptions()
    {
        $configDataMock = $this->getMock('Magento\Framework\Config\Data\ConfigData', [], [], '', false);
        $this->generator->expects($this->once())->method('createInstallConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createCryptConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createSessionConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createDefinitionsConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createDbConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createResourceConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createXFrameConfig')->willReturn($configDataMock);
        $configData = $this->object->createConfig([], $this->deploymentConfig);
        $this->assertEquals(7, count($configData));
    }

    public function testCreateOptionsWithOptionalNull()
    {
        $configDataMock = $this->getMock('Magento\Framework\Config\Data\ConfigData', [], [], '', false);
        $this->generator->expects($this->once())->method('createInstallConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createCryptConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createSessionConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createDefinitionsConfig')->willReturn(null);
        $this->generator->expects($this->once())->method('createDbConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createResourceConfig')->willReturn($configDataMock);
        $this->generator->expects($this->once())->method('createXFrameConfig')->willReturn($configDataMock);
        $configData = $this->object->createConfig([], $this->deploymentConfig);
        $this->assertEquals(6, count($configData));
    }
}
