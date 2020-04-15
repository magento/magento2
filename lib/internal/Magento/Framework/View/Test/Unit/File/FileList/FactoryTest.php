<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\File\FileList;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\File\FileList\Factory
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = new \Magento\Framework\View\File\FileList\Factory($this->objectManager);
    }

    public function testCreate()
    {
        $helperObjectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $collator = $helperObjectManager->getObject(\Magento\Framework\View\File\FileList\Factory::FILE_LIST_COLLATOR);
        $list = $helperObjectManager->getObject(\Magento\Framework\View\File\FileList::class);

        $this->objectManager
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo(\Magento\Framework\View\File\FileList\Factory::FILE_LIST_COLLATOR))
            ->willReturn($collator);

        $this->objectManager
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\View\File\FileList::class),
                $this->equalTo(['collator' => $collator])
            )
            ->willReturn($list);
        $this->assertSame($list, $this->model->create());
    }

    /**
     */
    public function testCreateException()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Magento\\Framework\\View\\File\\FileList\\Collator has to implement the collate interface.');

        $collator = new \stdClass();

        $this->objectManager
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo(\Magento\Framework\View\File\FileList\Factory::FILE_LIST_COLLATOR))
            ->willReturn($collator);

        $this->model->create();
    }
}
