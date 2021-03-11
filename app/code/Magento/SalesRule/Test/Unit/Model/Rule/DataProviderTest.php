<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Rule;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rule\DataProvider
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataObjectMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory::class,
            ['create']
        );

        $this->collectionMock = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Rule\Collection::class);
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $metaDataValueProviderMock = $this->getMockBuilder(\Magento\SalesRule\Model\Rule\Metadata\ValueProvider::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $registryMock->expects($this->once())
            ->method('registry')
            ->willReturn($ruleMock);
        $metaDataValueProviderMock->expects($this->once())->method('getMetadataValues')->willReturn(['data']);
        $this->model = (new ObjectManager($this))->getObject(
            \Magento\SalesRule\Model\Rule\DataProvider::class,
            [
                'name' => 'Name',
                'primaryFieldName' => 'Primary',
                'requestFieldName' => 'Request',
                'collectionFactory' => $this->collectionFactoryMock,
                'registry' => $registryMock,
                'metadataValueProvider' => $metaDataValueProviderMock
            ]
        );
    }

    public function testGetData()
    {
        $ruleId = 42;
        $ruleData = ['name' => 'Sales Price Rule'];

        $ruleMock = $this->createPartialMock(\Magento\SalesRule\Model\Rule::class, [
                'getDiscountAmount',
                'setDiscountAmount',
                'getDiscountQty',
                'setDiscountQty',
                'load',
                'getId',
                'getData'
            ]);
        $this->collectionMock->expects($this->once())->method('getItems')->willReturn([$ruleMock]);

        $ruleMock->expects($this->atLeastOnce())->method('getId')->willReturn($ruleId);
        $ruleMock->expects($this->once())->method('load')->willReturnSelf();
        $ruleMock->expects($this->once())->method('getData')->willReturn($ruleData);
        $ruleMock->expects($this->once())->method('getDiscountAmount')->willReturn(50.000);
        $ruleMock->expects($this->once())->method('setDiscountAmount')->with(50)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getDiscountQty')->willReturn(20.010);
        $ruleMock->expects($this->once())->method('setDiscountQty')->with(20.01)->willReturn($ruleMock);

        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
        // Load from object-cache the second time
        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
    }
}
