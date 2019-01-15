<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Rule\Metadata;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers Magento\SalesRule\Model\Rule\Metadata\ValueProvider
 */
class ValueProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Metadata\ValueProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactoryMock;

    protected function setUp()
    {
        $this->searchCriteriaBuilderMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\System\Store::class);
        $this->groupRepositoryMock = $this->createMock(\Magento\Customer\Api\GroupRepositoryInterface::class);
        $this->dataObjectMock = $this->createMock(\Magento\Framework\Convert\DataObject::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $groupSearchResultsMock = $this->createMock(\Magento\Customer\Api\Data\GroupSearchResultsInterface::class);
        $groupsMock = $this->createMock(\Magento\Customer\Api\Data\GroupInterface::class);

        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $this->groupRepositoryMock->expects($this->once())->method('getList')->with($searchCriteriaMock)
            ->willReturn($groupSearchResultsMock);
        $groupSearchResultsMock->expects($this->once())->method('getItems')->willReturn([$groupsMock]);
        $this->storeMock->expects($this->once())->method('getWebsiteValuesForForm')->willReturn([]);
        $this->dataObjectMock->expects($this->once())->method('toOptionArray')->with([$groupsMock], 'id', 'code')
            ->willReturn([]);
        $this->ruleFactoryMock = $this->createPartialMock(\Magento\SalesRule\Model\RuleFactory::class, ['create']);
        $this->model = (new ObjectManager($this))->getObject(
            \Magento\SalesRule\Model\Rule\Metadata\ValueProvider::class,
            [
                'store' => $this->storeMock,
                'groupRepository' => $this->groupRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'objectConverter' => $this->dataObjectMock,
                'salesRuleFactory' => $this->ruleFactoryMock,
            ]
        );
    }

    public function testGetMetadataValues()
    {
        $expectedData = include __DIR__ . '/_files/MetaData.php';

        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $ruleMock */
        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);
        $ruleMock->expects($this->once())
            ->method('getCouponTypes')
            ->willReturn(
                [
                    'key1' => 'couponType1',
                    'key2' => 'couponType2',
                ]
            );
        $ruleMock->expects($this->once())
            ->method('getStoreLabels')
            ->willReturn(
                [
                    'label0'
                ]
            );
        $test = $this->model->getMetadataValues($ruleMock);
        $this->assertEquals($expectedData, $test);
    }
}
