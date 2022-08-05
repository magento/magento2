<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Compiler;

use Magento\Config\Model\Config\Compiler\IncludeElement;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IncludeElementTest extends TestCase
{
    /**
     * @var IncludeElement
     */
    protected $includeElement;

    /**
     * @var Reader|MockObject
     */
    protected $moduleReaderMock;

    /**
     * @var ReadFactory|MockObject
     */
    protected $readFactoryMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->moduleReaderMock = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readFactoryMock = $this->getMockBuilder(ReadFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->includeElement = new IncludeElement(
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

        $compilerMock = $this->getMockBuilder(CompilerInterface::class)
            ->getMockForAbstractClass();
        $processedObjectMock = $this->getMockBuilder(DataObject::class)
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

    public function testCompileException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The "adminhtml/path/to/file.xml" file doesn\'t exist.');
        $xmlContent = '<rootConfig><include path="Module_Name::path/to/file.xml"/></rootConfig>';

        $document = new \DOMDocument();
        $document->loadXML($xmlContent);

        $compilerMock = $this->getMockBuilder(CompilerInterface::class)
            ->getMockForAbstractClass();
        $processedObjectMock = $this->getMockBuilder(DataObject::class)
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

        $modulesDirectoryMock = $this->getMockBuilder(ReadInterface::class)
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
