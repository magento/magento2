<?php
/**
 * RouterList model test class
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

class RouterListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RouterList
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var array
     */
    protected $routerList;

    protected function setUp(): void
    {
        $this->routerList = [
            'adminRouter' => ['class' => 'AdminClass', 'disable' => true, 'sortOrder' => 10],
            'frontendRouter' => ['class' => 'FrontClass', 'disable' => false, 'sortOrder' => 10],
            'default' => ['class' => 'DefaultClass', 'disable' => false, 'sortOrder' => 5],
            'someRouter' => ['class' => 'SomeClass', 'disable' => false, 'sortOrder' => 10],
            'anotherRouter' => ['class' => 'AnotherClass', 'disable' => false, 'sortOrder' => 15],
        ];

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = new \Magento\Framework\App\RouterList($this->objectManagerMock, $this->routerList);
    }

    public function testCurrent()
    {
        $expectedClass = new DefaultClass();
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('DefaultClass')
            ->willReturn($expectedClass);

        $this->assertEquals($expectedClass, $this->model->current());
    }

    public function testNext()
    {
        $expectedClass = new FrontClass();
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('FrontClass')
            ->willReturn($expectedClass);

        $this->model->next();
        $this->assertEquals($expectedClass, $this->model->current());
    }

    public function testValid()
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

    public function testRewind()
    {
        $frontClass = new FrontClass();
        $defaultClass = new DefaultClass();

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('DefaultClass')
            ->willReturn($defaultClass);

        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with('FrontClass')
            ->willReturn($frontClass);

        $this->assertEquals($defaultClass, $this->model->current());
        $this->model->next();
        $this->assertEquals($frontClass, $this->model->current());
        $this->model->rewind();
        $this->assertEquals($defaultClass, $this->model->current());
    }
}
