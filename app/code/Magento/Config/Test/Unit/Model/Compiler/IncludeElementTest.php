<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Compiler;

/**
 * Class IncludeElementTest
 */
class IncludeElementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Compiler\IncludeElement
     */
    protected $includeElement;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleReaderMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $readFactoryMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->moduleReaderMock = $this->getMockBuilder(\Magento\Framework\Module\Dir\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readFactoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->includeElement = new \Magento\Config\Model\Config\Compiler\IncludeElement(
            $this->moduleReaderMock,
            $this->readFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testCompileSuccess()
    {
        $xmlContent = '<rootConfig><include path="Module_Name::path/to/file.xml"/></rootConfig>';

        $document = new \DOMDocument();
        $document->loadXML($xmlContent);

        $compilerMock = $this->getMockBuilder(\Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface::class)
            ->getMockForAbstractClass();
        $processedObjectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->getContentStep();

        $compilerMock->expects($this->exactly(2))
            ->method('compile')
            ->with($this->isInstanceOf('\DOMElement'), $processedObjectMock, $processedObjectMock);

        $this->includeElement->compile(
            $compilerMock,
            $document->documentElement->firstChild,
            $processedObjectMock,
            $processedObjectMock
        );

        $this->assertEquals(
            '<?xml version="1.0"?><rootConfig><item id="1"><test/></item><item id="2"/></rootConfig>',
            str_replace(PHP_EOL, '', $document->saveXML())
        );
    }

    /**
     */
    public function testCompileException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The "adminhtml/path/to/file.xml" file doesn\'t exist.');

        $xmlContent = '<rootConfig><include path="Module_Name::path/to/file.xml"/></rootConfig>';

        $document = new \DOMDocument();
        $document->loadXML($xmlContent);

        $compilerMock = $this->getMockBuilder(\Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface::class)
            ->getMockForAbstractClass();
        $processedObjectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->getContentStep(false);

        $compilerMock->expects($this->never())
            ->method('compile')
            ->with($this->isInstanceOf('\DOMElement'), $processedObjectMock, $processedObjectMock);

        $this->includeElement->compile(
            $compilerMock,
            $document->documentElement->firstChild,
            $processedObjectMock,
            $processedObjectMock
        );
    }

    /**
     * @param bool $check
     */
    protected function getContentStep($check = true)
    {
        $resultPath = 'adminhtml/path/to/file.xml';
        $includeXmlContent = '<config><item id="1"><test/></item><item id="2"></item></config>';

        $modulesDirectoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->getMockForAbstractClass();

        $this->readFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($modulesDirectoryMock);

        $this->moduleReaderMock->expects($this->once())
            ->method('getModuleDir')
            ->with('etc', 'Module_Name')
            ->willReturn('path/in/application/module');

        $modulesDirectoryMock->expects($this->once())
            ->method('isExist')
            ->with($resultPath)
            ->willReturn($check);

        if ($check) {
            $modulesDirectoryMock->expects($this->once())
                ->method('isFile')
                ->with($resultPath)
                ->willReturn($check);
            $modulesDirectoryMock->expects($this->once())
                ->method('readFile')
                ->with($resultPath)
                ->willReturn($includeXmlContent);
        }
    }
}
