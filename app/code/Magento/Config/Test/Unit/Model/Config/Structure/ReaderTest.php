<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure;

use Magento\Config\Model\Config\SchemaLocator;
use Magento\Config\Model\Config\Structure\Converter;
use Magento\Config\Model\Config\Structure\Reader;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var FileResolverInterface|MockObject
     */
    protected $fileResolverMock;

    /**
     * @var Converter|MockObject
     */
    protected $converterMock;

    /**
     * @var SchemaLocator|MockObject
     */
    protected $schemaLocatorMock;

    /**
     * @var ValidationStateInterface|MockObject
     */
    protected $validationStateMock;

    /**
     * @var CompilerInterface|MockObject
     */
    protected $compilerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->fileResolverMock = $this->getMockBuilder(FileResolverInterface::class)
            ->getMockForAbstractClass();
        $this->converterMock = $this->getMockBuilder(Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->schemaLocatorMock = $this->getMockBuilder(SchemaLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationStateMock = $this->getMockBuilder(ValidationStateInterface::class)
            ->getMockForAbstractClass();
        $this->compilerMock = $this->getMockBuilder(
            CompilerInterface::class
        )->getMockForAbstractClass();

        $this->reader = new Reader(
            $this->fileResolverMock,
            $this->converterMock,
            $this->schemaLocatorMock,
            $this->validationStateMock,
            $this->compilerMock
        );
    }

    /**
     * Test the successful execution of the 'read' method
     *
     * @return void
     */
    public function testReadSuccessNotValidatedCase()
    {
        $content = '<config><item name="test1"></item><item name="test2"></item></config>';
        $expectedResult = ['result_data'];
        $fileList = ['file' => $content];

        $this->fileResolverMock->expects($this->once())
            ->method('get')
            ->with('system.xml', 'global')
            ->willReturn($fileList);

        $this->compilerMock->expects($this->once())
            ->method('compile')
            ->with(
                $this->isInstanceOf('\DOMElement'),
                $this->isInstanceOf(DataObject::class),
                $this->isInstanceOf(DataObject::class)
            );
        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with($this->isInstanceOf('\DOMDocument'))
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->reader->read());
    }

    /**
     * Test the execution with the Validation exception of the 'read' method
     */
    public function testReadWithValidationException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Verify the XML and try again.');
        $content = '<config><item name="test1"></item><wrong></config>';
        $expectedResult = ['result_data'];
        $fileList = ['file' => $content];

        $this->fileResolverMock->expects($this->once())
            ->method('get')
            ->with('system.xml', 'global')
            ->willReturn($fileList);

        $this->assertEquals($expectedResult, $this->reader->read());
    }
}
