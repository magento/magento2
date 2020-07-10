<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\File\FileList;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\File\FileList;
use Magento\Framework\View\File\FileList\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->model = new Factory($this->objectManager);
    }

    public function testCreate()
    {
        $helperObjectManager = new ObjectManager($this);
        $collator = $helperObjectManager->getObject(Factory::FILE_LIST_COLLATOR);
        $list = $helperObjectManager->getObject(FileList::class);

        $this->objectManager
            ->expects($this->once())
            ->method('get')
            ->with(Factory::FILE_LIST_COLLATOR)
            ->willReturn($collator);

        $this->objectManager
            ->expects($this->once())
            ->method('create')
            ->with(
                FileList::class,
                ['collator' => $collator]
            )
            ->willReturn($list);
        $this->assertSame($list, $this->model->create());
    }

    public function testCreateException()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(
            'Magento\Framework\View\File\FileList\Collator has to implement the collate interface.'
        );
        $collator = new \stdClass();

        $this->objectManager
            ->expects($this->once())
            ->method('get')
            ->with(Factory::FILE_LIST_COLLATOR)
            ->willReturn($collator);

        $this->model->create();
    }
}
