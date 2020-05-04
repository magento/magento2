<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Metadata;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\SimpleActionOptionsProvider;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\System\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Magento\SalesRule\Model\Rule\Metadata\ValueProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValueProviderTest extends TestCase
{
    /**
     * @var ValueProvider
     */
    protected $model;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var DataObject|MockObject
     */
    protected $dataObjectMock;

    /**
     * @var RuleFactory|MockObject
     */
    protected $ruleFactoryMock;

    /**
     * @var SimpleActionOptionsProvider|MockObject
     */
    private $simpleActionOptionsProviderMock;

    protected function setUp(): void
    {
        $expectedData = include __DIR__ . '/_files/MetaData.php';
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->groupRepositoryMock = $this->getMockForAbstractClass(GroupRepositoryInterface::class);
        $this->dataObjectMock = $this->createMock(DataObject::class);
        $this->simpleActionOptionsProviderMock = $this->createMock(SimpleActionOptionsProvider::class);
        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $groupSearchResultsMock = $this->getMockForAbstractClass(GroupSearchResultsInterface::class);
        $groupsMock = $this->getMockForAbstractClass(GroupInterface::class);

        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $this->groupRepositoryMock->expects($this->once())->method('getList')->with($searchCriteriaMock)
            ->willReturn($groupSearchResultsMock);
        $groupSearchResultsMock->expects($this->once())->method('getItems')->willReturn([$groupsMock]);
        $this->storeMock->expects($this->once())->method('getWebsiteValuesForForm')->willReturn([]);
        $this->dataObjectMock->expects($this->once())->method('toOptionArray')->with([$groupsMock], 'id', 'code')
            ->willReturn([]);
        $this->ruleFactoryMock = $this->createPartialMock(RuleFactory::class, ['create']);
        $this->simpleActionOptionsProviderMock->method('toOptionArray')->willReturn(
            $expectedData['actions']['children']['simple_action']['arguments']['data']['config']['options']
        );
        $this->model = (new ObjectManager($this))->getObject(
            ValueProvider::class,
            [
                'store' => $this->storeMock,
                'groupRepository' => $this->groupRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'objectConverter' => $this->dataObjectMock,
                'salesRuleFactory' => $this->ruleFactoryMock,
                'simpleActionOptionsProvider' => $this->simpleActionOptionsProviderMock
            ]
        );
    }

    public function testGetMetadataValues()
    {
        $expectedData = include __DIR__ . '/_files/MetaData.php';

        /** @var Rule|MockObject $ruleMock */
        $ruleMock = $this->createMock(Rule::class);
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
