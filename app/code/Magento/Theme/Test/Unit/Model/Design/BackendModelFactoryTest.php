<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design;

use Magento\Framework\App\Config\Value;
use Magento\Framework\ObjectManagerInterface;
use Magento\Theme\Model\Design\Backend\Exceptions;
use Magento\Theme\Model\Design\BackendModelFactory;
use Magento\Theme\Model\Design\Config\MetadataProvider;
use Magento\Theme\Model\ResourceModel\Design\Config\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackendModelFactoryTest extends TestCase
{
    /** @var BackendModelFactory */
    protected $model;

    /** @var ObjectManagerInterface|MockObject */
    protected $objectManagerMock;

    /** @var MetadataProvider|MockObject */
    protected $metadataProviderMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /** @var Collection|MockObject */
    protected $collection;

    /** @var Value|MockObject */
    protected $backendModel;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->metadataProviderMock = $this->getMockBuilder(MetadataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(
            \Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModel = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['setValue'])
            ->getMock();

        $this->model = new BackendModelFactory(
            $this->objectManagerMock,
            $this->metadataProviderMock,
            $this->collectionFactoryMock
        );
    }

    public function testCreate()
    {
        $scope = 'website';
        $scopeId = 1;
        $data = [
            'scope' => $scope,
            'scopeId' => $scopeId,
            'value' => 'value',
            'config' => [
                'path' => 'design/head/default_title',
                'backend_model' => Value::class
            ]
        ];
        $this->metadataProviderMock->expects($this->once())
            ->method('get')
            ->willReturn([
                'head_default_title' => [
                    'path' => 'design/head/default_title'
                ]
            ]);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collection);
        $this->collection->expects($this->once())
            ->method('addPathsFilter')
            ->with(['head_default_title' => 'design/head/default_title']);
        $this->collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('scope', $scope);
        $this->collection->expects($this->once())
            ->method('addScopeIdFilter')
            ->with($scopeId);
        $this->collection->expects($this->once())
            ->method('getData')
            ->willReturn([
                [
                    'config_id' => 1,
                    'path' => 'design/head/default_title'
                ]
            ]);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                Value::class,
                [
                    'data' => [
                        'path' => 'design/head/default_title',
                        'scope' => $scope,
                        'scope_id' => $scopeId,
                        'field_config' => $data['config'],
                        'config_id' => 1
                    ]
                ]
            )
            ->willReturn($this->backendModel);
        $this->backendModel->expects($this->once())
            ->method('setValue')
            ->willReturn('value');
        $this->assertSame($this->backendModel, $this->model->create($data));
    }

    public function testCreateByPath()
    {
        $path = 'design/head/default_title';
        $backendModelType = Exceptions::class;
        $backendModel = $this->getMockBuilder($backendModelType)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataProviderMock->expects($this->once())
            ->method('get')
            ->willReturn([
                'head_default_title' => [
                    'path' => $path,
                    'backend_model' => $backendModelType
                ]
            ]);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($backendModelType, ['data' => []])
            ->willReturn($backendModel);
        $this->assertEquals($backendModel, $this->model->createByPath($path));
    }
}
