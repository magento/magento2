<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit;

use Magento\Framework\ObjectManager\Definition\Compiled\Serialized;

class DefinitionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\DriverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemDriverMock;

    /**
     * @var \Magento\Framework\ObjectManager\DefinitionFactory
     */
    protected $model;

    /**
     * @var string
     */
    protected $sampleContent;

    protected function setUp()
    {
        $this->sampleContent = serialize([1, 2, 3]);
        $this->filesystemDriverMock = $this->getMock(
            'Magento\Framework\Filesystem\Driver\File',
            [],
            [],
            '',
            false
        );
        $this->model = new \Magento\Framework\ObjectManager\DefinitionFactory(
            $this->filesystemDriverMock,
            'DefinitionDir',
            'GenerationDir',
            Serialized::MODE_NAME
        );
    }

    public function testCreateClassDefinitionFromString()
    {
        $this->assertInstanceOf(
            '\Magento\Framework\ObjectManager\Definition\Compiled\Serialized',
            $this->model->createClassDefinition($this->sampleContent)
        );
    }

    /**
     * @param string $path
     * @param string $callMethod
     * @param string $expectedClass
     * @dataProvider createPluginsAndRelationsReadableDataProvider
     */
    public function testCreatePluginsAndRelationsReadable($path, $callMethod, $expectedClass)
    {
        $this->filesystemDriverMock->expects($this->once())->method('isReadable')
            ->with($path)
            ->will($this->returnValue(true));
        $this->filesystemDriverMock->expects($this->once())->method('fileGetContents')
            ->with($path)
            ->will($this->returnValue($this->sampleContent));
        $this->assertInstanceOf($expectedClass, $this->model->$callMethod());
    }

    public function createPluginsAndRelationsReadableDataProvider()
    {
        return [
            'relations' => [
                'DefinitionDir/relations.ser',
                'createRelations',
                '\Magento\Framework\ObjectManager\Relations\Compiled',
            ],
            'plugins' => [
                'DefinitionDir/plugins.ser',
                'createPluginDefinition',
                '\Magento\Framework\Interception\Definition\Compiled',
            ],
        ];
    }

    /**
     * @param string $path
     * @param string $callMethod
     * @param string $expectedClass
     * @dataProvider createPluginsAndRelationsNotReadableDataProvider
     */
    public function testCreatePluginsAndRelationsNotReadable($path, $callMethod, $expectedClass)
    {
        $this->filesystemDriverMock->expects($this->once())->method('isReadable')
            ->with($path)
            ->will($this->returnValue(false));
        $this->assertInstanceOf($expectedClass, $this->model->$callMethod());
    }

    public function createPluginsAndRelationsNotReadableDataProvider()
    {
        return [
            'relations' => [
                'DefinitionDir/relations.ser',
                'createRelations',
                '\Magento\Framework\ObjectManager\Relations\Runtime',
            ],
            'plugins' => [
                'DefinitionDir/plugins.ser',
                'createPluginDefinition',
                '\Magento\Framework\Interception\Definition\Runtime',
            ],
        ];
    }

    public function testGetSupportedFormats()
    {
        $actual = \Magento\Framework\ObjectManager\DefinitionFactory::getSupportedFormats();
        $this->assertInternalType('array', $actual);
        foreach ($actual as $className) {
            $this->assertInternalType('string', $className);
        }
    }
}
