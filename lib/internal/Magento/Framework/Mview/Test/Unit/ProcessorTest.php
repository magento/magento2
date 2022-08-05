<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit;

use Magento\Framework\Mview\Processor;
use Magento\Framework\Mview\View\Collection;
use Magento\Framework\Mview\View\CollectionFactory;
use Magento\Framework\Mview\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    protected $model;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $viewsFactoryMock;

    protected function setUp(): void
    {
        $this->viewsFactoryMock =
            $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->model = new Processor($this->viewsFactoryMock);
    }

    /**
     * Return array of mocked views
     *
     * @param string $method
     * @return ViewInterface[]|MockObject[]
     */
    protected function getViews($method)
    {
        $viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $viewMock->expects($this->exactly(2))->method($method);
        return [$viewMock, $viewMock];
    }

    /**
     * Return view collection mock
     *
     * @return Collection|MockObject
     */
    protected function getViewsMock()
    {
        $viewsMock = $this->createMock(Collection::class);
        $this->viewsFactoryMock->expects($this->once())->method('create')->willReturn($viewsMock);
        return $viewsMock;
    }

    public function testUpdate()
    {
        $viewsMock = $this->getViewsMock();
        $viewsMock->expects($this->once())->method('getItems')->willReturn($this->getViews('update'));
        $viewsMock->expects($this->never())->method('getItemsByColumnValue');

        $this->model->update();
    }

    public function testUpdateWithGroup()
    {
        $group = 'group';
        $viewsMock = $this->getViewsMock();
        $viewsMock->expects($this->never())->method('getItems');
        $viewsMock->expects(
            $this->once()
        )->method(
            'getItemsByColumnValue'
        )->with(
            $group
        )->willReturn(
            $this->getViews('update')
        );

        $this->model->update($group);
    }

    public function testClearChangelog()
    {
        $viewsMock = $this->getViewsMock();
        $viewsMock->expects(
            $this->once()
        )->method(
            'getItems'
        )->willReturn(
            $this->getViews('clearChangelog')
        );
        $viewsMock->expects($this->never())->method('getItemsByColumnValue');

        $this->model->clearChangelog();
    }

    public function testClearChangelogWithGroup()
    {
        $group = 'group';
        $viewsMock = $this->getViewsMock();
        $viewsMock->expects($this->never())->method('getItems');
        $viewsMock->expects(
            $this->once()
        )->method(
            'getItemsByColumnValue'
        )->with(
            $group
        )->willReturn(
            $this->getViews('clearChangelog')
        );

        $this->model->clearChangelog($group);
    }
}
