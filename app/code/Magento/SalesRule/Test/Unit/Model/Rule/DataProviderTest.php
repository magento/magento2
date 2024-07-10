<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule;

use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\DataProvider;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var MockObject
     */
    protected $dataObjectMock;

    /**
     * @var MockObject
     */
    protected $collectionMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $ruleMock = $this->createMock(Rule::class);
        $metaDataValueProviderMock = $this->getMockBuilder(ValueProvider::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $registryMock = $this->createMock(Registry::class);
        $registryMock->expects($this->once())
            ->method('registry')
            ->willReturn($ruleMock);
        $metaDataValueProviderMock->expects($this->once())->method('getMetadataValues')->willReturn(['data']);
        $this->model = (new ObjectManager($this))->getObject(
            DataProvider::class,
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
        $ruleData = ['name' => 'Sales Price Rule', 'store_labels' => ['1' => 'Store Label']];

        $ruleMock = $this->getMockBuilder(Rule::class)
            ->addMethods(['getDiscountAmount', 'setDiscountAmount', 'getDiscountQty', 'setDiscountQty',])
            ->onlyMethods(['load', 'getId', 'getData', 'getStoreLabels'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock->expects($this->once())->method('getItems')->willReturn([$ruleMock]);

        $ruleMock->expects($this->atLeastOnce())->method('getId')->willReturn($ruleId);
        $ruleMock->expects($this->once())->method('load')->willReturnSelf();
        $ruleMock->expects($this->once())->method('getData')->willReturn($ruleData);
        $ruleMock->expects($this->once())->method('getDiscountAmount')->willReturn(50.000);
        $ruleMock->expects($this->once())->method('setDiscountAmount')->with(50)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getDiscountQty')->willReturn(20.010);
        $ruleMock->expects($this->once())->method('setDiscountQty')->with(20.01)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getStoreLabels')->willReturn(["1" => "Store Label"]);

        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
        // Load from object-cache the second time
        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
    }
}
