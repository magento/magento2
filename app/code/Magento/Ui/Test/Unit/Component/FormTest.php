<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /** @var Form */
    protected $model;

    /** @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $filterBuilderMock;

    /** @var Processor|\PHPUnit_Framework_MockObject_MockObject */
    protected $processorMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->getMockForAbstractClass();
        $this->filterBuilderMock = $this->getMockBuilder('Magento\Framework\Api\FilterBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getProcessor')
            ->willReturn($this->processorMock);

        $this->processorMock->expects($this->once())
            ->method('register');

        $this->model = new Form(
            $this->contextMock,
            $this->filterBuilderMock
        );
    }

    public function testGetComponentName()
    {
        $this->assertEquals(Form::NAME, $this->model->getComponentName());
    }

    public function testGetDataSourceData()
    {
        $requestFieldName = 'request_id';
        $primaryFieldName = 'primary_id';
        $fieldId = 44;
        $row = ['key' => 'value'];
        $data = [
            $fieldId => $row,
        ];
        $dataSource = [
            'data' => $row,
        ];

        /** @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject $dataProviderMock */
        $dataProviderMock =
            $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface')
            ->getMock();
        $dataProviderMock->expects($this->once())
            ->method('getRequestFieldName')
            ->willReturn($requestFieldName);
        $dataProviderMock->expects($this->once())
            ->method('getPrimaryFieldName')
            ->willReturn($primaryFieldName);

        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProviderMock);
        $this->contextMock->expects($this->once())
            ->method('getRequestParam')
            ->with($requestFieldName)
            ->willReturn($fieldId);

        /** @var Filter|\PHPUnit_Framework_MockObject_MockObject $filterMock */
        $filterMock = $this->getMockBuilder('Magento\Framework\Api\Filter')
                ->disableOriginalConstructor()
                ->getMock();

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with($primaryFieldName)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($fieldId)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        $dataProviderMock->expects($this->once())
            ->method('addFilter')
            ->with($filterMock);
        $dataProviderMock->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->assertEquals($dataSource, $this->model->getDataSourceData());
    }

    public function testGetDataSourceDataWithoutData()
    {
        $requestFieldName = 'request_id';
        $primaryFieldName = 'primary_id';
        $fieldId = 44;
        $data = [];
        $dataSource = [];

        /** @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject $dataProviderMock */
        $dataProviderMock =
            $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface')
                ->getMock();
        $dataProviderMock->expects($this->once())
            ->method('getRequestFieldName')
            ->willReturn($requestFieldName);
        $dataProviderMock->expects($this->once())
            ->method('getPrimaryFieldName')
            ->willReturn($primaryFieldName);

        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProviderMock);
        $this->contextMock->expects($this->once())
            ->method('getRequestParam')
            ->with($requestFieldName)
            ->willReturn($fieldId);

        /** @var Filter|\PHPUnit_Framework_MockObject_MockObject $filterMock */
        $filterMock = $this->getMockBuilder('Magento\Framework\Api\Filter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with($primaryFieldName)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($fieldId)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        $dataProviderMock->expects($this->once())
            ->method('addFilter')
            ->with($filterMock);
        $dataProviderMock->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->assertEquals($dataSource, $this->model->getDataSourceData());
    }

    public function testGetDataSourceDataWithoutId()
    {
        $requestFieldName = 'request_id';
        $primaryFieldName = 'primary_id';
        $fieldId = null;
        $row = ['key' => 'value'];
        $data = [
            $fieldId => $row,
        ];
        $dataSource = [
            'data' => $row,
        ];

        /** @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject $dataProviderMock */
        $dataProviderMock =
            $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface')
                ->getMock();
        $dataProviderMock->expects($this->once())
            ->method('getRequestFieldName')
            ->willReturn($requestFieldName);
        $dataProviderMock->expects($this->once())
            ->method('getPrimaryFieldName')
            ->willReturn($primaryFieldName);

        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProviderMock);
        $this->contextMock->expects($this->once())
            ->method('getRequestParam')
            ->with($requestFieldName)
            ->willReturn($fieldId);

        /** @var Filter|\PHPUnit_Framework_MockObject_MockObject $filterMock */
        $filterMock = $this->getMockBuilder('Magento\Framework\Api\Filter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with($primaryFieldName)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($fieldId)
            ->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        $dataProviderMock->expects($this->once())
            ->method('addFilter')
            ->with($filterMock);
        $dataProviderMock->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->assertEquals($dataSource, $this->model->getDataSourceData());
    }
}
