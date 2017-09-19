<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\Config\GenericSchemaLocator;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;

/**
 * @covers \Magento\Framework\Config\GenericSchemaLocator
 */
class GenericSchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $testSchemaFileName = 'test-example.xsd';

    /**
     * @var GenericSchemaLocator
     */
    private $schemaLocator;

    /**
     * @var ModuleDirReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleReaderMock;

    private function createNewSchemaLocatorInstance(ModuleDirReader $reader, $moduleName, $mergeSchema, $perFileSchema)
    {
        return new GenericSchemaLocator($reader, $moduleName, $mergeSchema, $perFileSchema);
    }

    protected function setUp()
    {
        $this->moduleReaderMock = $this->createMock(ModuleDirReader::class);
        $this->schemaLocator = $this->createNewSchemaLocatorInstance(
            $this->moduleReaderMock,
            'Test_ModuleName',
            $this->testSchemaFileName,
            null
        );
    }

    public function testItIsAnInstanceOfSchemaLocatorInterface()
    {
        $this->assertInstanceOf(SchemaLocatorInterface::class, $this->schemaLocator);
    }

    public function testItReturnsThePathToTheSpecifiedModuleXsd()
    {
        $this->moduleReaderMock->expects($this->any())->method('getModuleDir')->willReturn('....');
        $this->assertSame('..../' . $this->testSchemaFileName, $this->schemaLocator->getSchema());
    }

    public function testItReturnsNullAsTheDefaultPerFileSchema()
    {
        $this->assertNull($this->schemaLocator->getPerFileSchema());
    }

    public function testItReturnsThePathToThePerFileSchema()
    {
        $this->moduleReaderMock->expects($this->any())->method('getModuleDir')->willReturn('....');
        $schemaLocator = $this->createNewSchemaLocatorInstance(
            $this->moduleReaderMock,
            'Test_ModuleName',
            'some other file name',
            $this->testSchemaFileName
        );
        $this->assertSame('..../' . $this->testSchemaFileName, $schemaLocator->getPerFileSchema());
    }
}
