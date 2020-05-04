<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\ResourceModel\Theme\File\Collection;
use Magento\Theme\Model\Theme\FileProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileProviderTest extends TestCase
{
    /**
     * @var FileProvider
     */
    protected $model;

    /**
     * @var Collection|MockObject
     */
    protected $file;

    protected function setUp(): void
    {
        $fileFactory = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Theme\File\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->file = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->file);

        /** @var \Magento\Theme\Model\ResourceModel\Theme\File\CollectionFactory $fileFactory */
        $this->model = new FileProvider($fileFactory);
    }

    /**
     * @test
     * @return void
     */
    public function testGetItems()
    {
        $items = ['item'];
        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->getMock();
        $filters = ['name' => 'filter'];
        $this->file->expects($this->once())
            ->method('addThemeFilter')
            ->with($theme)
            ->willReturnSelf();
        $this->file->expects($this->once())
            ->method('addFieldToFilter')
            ->with('name', 'filter')
            ->willReturnSelf();
        $this->file->expects($this->once())
            ->method('setDefaultOrder')
            ->willReturnSelf();
        $this->file->expects($this->once())
            ->method('getItems')
            ->willReturn($items);

        /** @var ThemeInterface $theme */
        $this->assertEquals($items, $this->model->getItems($theme, $filters));
    }
}
