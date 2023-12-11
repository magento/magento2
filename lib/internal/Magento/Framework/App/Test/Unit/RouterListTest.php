<?php
/**
 * RouterList model test class
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\RouterList;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouterListTest extends TestCase
{
    /**
     * @var RouterList
     */
    protected $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var array
     */
    protected $routerList;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->routerList = [
            'adminRouter' => ['class' => 'AdminClass', 'disable' => true, 'sortOrder' => 10],
            'frontendRouter' => ['class' => 'FrontClass', 'disable' => false, 'sortOrder' => 10],
            'default' => ['class' => 'DefaultClass', 'disable' => false, 'sortOrder' => 5],
            'someRouter' => ['class' => 'SomeClass', 'disable' => false, 'sortOrder' => 10],
            'anotherRouter' => ['class' => 'AnotherClass', 'disable' => false, 'sortOrder' => 15]
        ];

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->model = new RouterList($this->objectManagerMock, $this->routerList);
    }

    /**
     * @return void
     */
    public function testCurrent(): void
    {
        $expectedClass = new DefaultClass();
        $this->objectManagerMock
            ->method('create')
            ->with('DefaultClass')
            ->willReturn($expectedClass);

        $this->assertEquals($expectedClass, $this->model->current());
    }

    /**
     * @return void
     */
    public function testNext(): void
    {
        $expectedClass = new FrontClass();
        $this->objectManagerMock
            ->method('create')
            ->with('FrontClass')
            ->willReturn($expectedClass);

        $this->model->next();
        $this->assertEquals($expectedClass, $this->model->current());
    }

    /**
     * @return void
     */
    public function testValid(): void
    {
        $this->assertTrue($this->model->valid());
        $this->model->next();
        $this->assertTrue($this->model->valid());
        $this->model->next();
        $this->assertTrue($this->model->valid());
        $this->model->next();
        $this->assertTrue($this->model->valid());
        $this->model->next();
        $this->assertFalse($this->model->valid());
    }

    /**
     * @return void
     */
    public function testRewind(): void
    {
        $frontClass = new FrontClass();
        $defaultClass = new DefaultClass();

        $this->objectManagerMock
            ->method('create')
            ->withConsecutive(['DefaultClass'], ['FrontClass'])
            ->willReturnOnConsecutiveCalls($defaultClass, $frontClass);

        $this->assertEquals($defaultClass, $this->model->current());
        $this->model->next();
        $this->assertEquals($frontClass, $this->model->current());
        $this->model->rewind();
        $this->assertEquals($defaultClass, $this->model->current());
    }
}
