<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Controller\Adminhtml\Export\GridToXml;
use Magento\Ui\Model\Export\ConvertToXml;

class GridToXmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GridToXml
     */
    protected $controller;

    /**
     * @var Context | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var ConvertToXml | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $converter;

    /**
     * @var FileFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileFactory;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->converter = $this->getMockBuilder(\Magento\Ui\Model\Export\ConvertToXml::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileFactory = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new GridToXml(
            $this->context,
            $this->converter,
            $this->fileFactory
        );
    }

    public function testExecute()
    {
        $content = 'test';

        $this->converter->expects($this->once())
            ->method('getXmlFile')
            ->willReturn($content);

        $this->fileFactory->expects($this->once())
            ->method('create')
            ->with('export.xml', $content, 'var')
            ->willReturn($content);

        $this->assertEquals($content, $this->controller->execute());
    }
}
