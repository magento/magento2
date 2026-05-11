<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Controller\Adminhtml\Export\GridToCsv;
use Magento\Ui\Model\Export\ConvertToCsv;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridToCsvTest extends TestCase
{
    /**
     * @var GridToCsv
     */
    protected $controller;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var ConvertToCsv|MockObject
     */
    protected $converter;

    /**
     * @var FileFactory|MockObject
     */
    protected $fileFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->objectManager->prepareObjectManager();

        $this->context = $this->createMock(Context::class);

        $this->converter = $this->createMock(ConvertToCsv::class);

        $this->fileFactory = $this->createMock(FileFactory::class);

        $this->controller = new GridToCsv(
            $this->context,
            $this->converter,
            $this->fileFactory
        );
    }

    public function testExecute()
    {
        $content = 'test';

        $this->converter->expects($this->once())
            ->method('getCsvFile')
            ->willReturn($content);

        $this->fileFactory->expects($this->once())
            ->method('create')
            ->with('export.csv', $content, 'var')
            ->willReturn($content);

        $this->assertEquals($content, $this->controller->execute());
    }
}
