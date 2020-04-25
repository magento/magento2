<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\File\FileList;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\File\FileList\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\File\FileList;

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
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
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
            ->with($this->equalTo(Factory::FILE_LIST_COLLATOR))
            ->will($this->returnValue($collator));

        $this->objectManager
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(FileList::class),
                $this->equalTo(['collator' => $collator])
            )
            ->will($this->returnValue($list));
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
            ->with($this->equalTo(Factory::FILE_LIST_COLLATOR))
            ->will($this->returnValue($collator));

        $this->model->create();
    }
}
