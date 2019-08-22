<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Theme\Model\Theme\FileProvider;

class FileProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FileProvider
     */
    protected $model;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\File\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $file;

    protected function setUp()
    {
        $fileFactory = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Theme\File\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->file = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Theme\File\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->file);

        /** @var $fileFactory \Magento\Theme\Model\ResourceModel\Theme\File\CollectionFactory */
        $this->model = new FileProvider($fileFactory);
    }

    /**
     * @test
     * @return void
     */
    public function testGetItems()
    {
        $items = ['item'];
        $theme = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)->getMock();
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

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $this->assertEquals($items, $this->model->getItems($theme, $filters));
    }
}
