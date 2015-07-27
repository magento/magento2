<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;
use Magento\SalesRule\Model\RuleRepository;

/**
 * Class RuleRepositoryTest
 */
class RuleRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToDataModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $toDataModelConverter;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $toModelConverter;

    protected function setUp()
    {
        $this->ruleFactory = $this->getMock('\Magento\SalesRule\Model\RuleFactory', [], [], '', false);

        $className = '\Magento\SalesRule\Model\Converter\ToDataModel';
        $this->toDataModelConverter = $this->getMock($className, [], [], '', false);

        $className = '\Magento\SalesRule\Model\Converter\ToModel';
        $this->toModelConverter = $this->getMock($className, [], [], '', false);

        $this->ruleRepository = new RuleRepository(
            $this->ruleFactory,
            $this->getMock('\Magento\SalesRule\Api\Data\RuleInterfaceFactory', [], [], '', false),
            $this->getMock('\Magento\SalesRule\Api\Data\ConditionInterfaceFactory', [], [], '', false),
            $this->toDataModelConverter,
            $this->toModelConverter,
            $this->getMock('\Magento\SalesRule\Api\Data\RuleSearchResultInterfaceFactory', [], [], '', false),
            $this->getMock('\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface', [], [], '', false),
            $this->getMock('\Magento\SalesRule\Model\Resource\Rule\CollectionFactory', [], [], '', false),
            $this->getMock('\Magento\Framework\Reflection\DataObjectProcessor', [], [], '', false)
        );
    }

    public function testDeleteById()
    {
        $model = $this->getMock('\Magento\SalesRule\Model\Rule', [], [], '', false);
        $this->ruleFactory->expects($this->once())->method('create')->willReturn($model);
        $model->expects($this->once())->method('load')->with(10)->willReturnSelf();
        $model->expects($this->once())->method('getId')->willReturn(10);
        $model->expects($this->once())->method('delete');

        $this->assertTrue($this->ruleRepository->deleteById(10));
    }

    public function testGetById()
    {
        $model = $this->getMock('\Magento\SalesRule\Model\Rule', [], [], '', false);
        $this->ruleFactory->expects($this->once())->method('create')->willReturn($model);
        $model->expects($this->once())->method('load')->with(10)->willReturnSelf();
        $model->expects($this->once())->method('getId')->willReturn(10);
        $model->expects($this->once())->method('getStoreLabels');

        $rule = $this->getMock('\Magento\SalesRule\Model\Data\Rule',[], [], '', false);
        $this->toDataModelConverter->expects($this->once())->method('toDataModel')->with($model)->willReturn($rule);

        $this->assertEquals($rule, $this->ruleRepository->getById(10));
    }

    public function testSave()
    {
        $rule = $this->getMock('\Magento\SalesRule\Model\Data\Rule',[], [], '', false);

        $model = $this->getMock('\Magento\SalesRule\Model\Rule', [], [], '', false);
        $this->toModelConverter->expects($this->once())->method('toModel')->with($rule)->willReturn($model);
        $model->expects($this->once())->method('save');
        $model->expects($this->once())->method('getId')->willReturn(10);
        $model->expects($this->once())->method('load')->with(10);
        $model->expects($this->once())->method('getStoreLabels');

        $this->toDataModelConverter->expects($this->once())->method('toDataModel')->with($model)->willReturn($rule);

        $this->assertEquals($rule, $this->ruleRepository->save($rule));
    }

    public function testGetList()
    {
        $collectionSize = 1;
        $currentPage = 12;
        $pageSize = 4;

        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);

        $className = '\Magento\SalesRule\Api\Data\RuleSearchResultInterface';
        $searchResultsMock = $this->getMock($className, [], [], '', false);


        // TODO: TBD
        //$this->assertEquals($searchResultsMock, $this->ruleRepository->getList($searchCriteriaMock));
    }
}
