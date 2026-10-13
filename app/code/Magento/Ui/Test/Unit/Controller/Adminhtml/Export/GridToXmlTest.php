<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Controller\Adminhtml\Export\GridToXml;
use Magento\Ui\Model\Export\ConvertToXml;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class GridToXmlTest extends TestCase
{
    /**
     * @var GridToXml
     */
    protected $controller;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var ConvertToXml|MockObject
     */
    protected $converter;

    /**
     * @var FileFactory|MockObject
     */
    protected $fileFactory;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);

        $this->converter = $this->createMock(ConvertToXml::class);

        $this->fileFactory = $this->createMock(FileFactory::class);

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
