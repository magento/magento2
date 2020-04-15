<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure;

/**
 * Class ReaderTest
 */
class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Reader
     */
    protected $reader;

    /**
     * @var \Magento\Framework\Config\FileResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileResolverMock;

    /**
     * @var \Magento\Config\Model\Config\Structure\Converter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $converterMock;

    /**
     * @var \Magento\Config\Model\Config\SchemaLocator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $schemaLocatorMock;

    /**
     * @var \Magento\Framework\Config\ValidationStateInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $validationStateMock;

    /**
     * @var \Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $compilerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->fileResolverMock = $this->getMockBuilder(\Magento\Framework\Config\FileResolverInterface::class)
            ->getMockForAbstractClass();
        $this->converterMock = $this->getMockBuilder(\Magento\Config\Model\Config\Structure\Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->schemaLocatorMock = $this->getMockBuilder(\Magento\Config\Model\Config\SchemaLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationStateMock = $this->getMockBuilder(\Magento\Framework\Config\ValidationStateInterface::class)
            ->getMockForAbstractClass();
        $this->compilerMock = $this->getMockBuilder(
            \Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface::class
        )->getMockForAbstractClass();

        $this->reader = new \Magento\Config\Model\Config\Structure\Reader(
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
                $this->isInstanceOf(\Magento\Framework\DataObject::class),
                $this->isInstanceOf(\Magento\Framework\DataObject::class)
            );
        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with($this->isInstanceOf('\DOMDocument'))
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->reader->read());
    }

    /**
     * Test the execution with the Validation exception of the 'read' method
     *
     */
    public function testReadWithValidationException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
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
