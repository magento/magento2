<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tax\Model;

class TaxRuleRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\TaxRuleRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRuleRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->taxRuleRegistry = $this->getMock('\Magento\Tax\Model\Calculation\TaxRuleRegistry', [], [], '', false);
        $this->searchResultBuilder = $this->getMock(
            '\Magento\Tax\Api\Data\TaxRuleSearchResultsDataBuilder',
            ['setSearchCriteria', 'setTotalCount', 'setItems', 'create'],
            [],
            '',
            false
        );
        $this->ruleFactory = $this->getMock('\Magento\Tax\Model\Calculation\RuleFactory', [], [], '', false);
        $this->collectionFactory = $this->getMock(
            '\Magento\Tax\Model\Resource\Calculation\Rule\CollectionFactory', ['create'], [], '', false
        );
        $this->resource = $this->getMock('\Magento\Tax\Model\Resource\Calculation\Rule', [], [], '', false);

        $this->model = new TaxRuleRepository(
            $this->taxRuleRegistry,
            $this->searchResultBuilder,
            $this->ruleFactory,
            $this->collectionFactory,
            $this->resource
        );
    }

    public function testGet()
    {
        $rule = $this->getMock('\Magento\Tax\Model\Calculation\Rule', [], [], '', false);
        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->assertEquals($rule, $this->model->get(10));
    }

    public function testDelete()
    {
        $rule = $this->getMock('\Magento\Tax\Model\Calculation\Rule', [], [], '', false);
        $rule->expects($this->once())->method('getId')->willReturn(10);
        $this->resource->expects($this->once())->method('delete')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('removeTaxRule')->with(10);
        $this->assertTrue($this->model->delete($rule));
    }

    public function testDeleteById()
    {
        $rule = $this->getMock('\Magento\Tax\Model\Calculation\Rule', [], [], '', false);
        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);

        $rule->expects($this->once())->method('getId')->willReturn(10);
        $this->resource->expects($this->once())->method('delete')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('removeTaxRule')->with(10);
        $this->assertTrue($this->model->deleteById(10));
    }

    public function testSave()
    {
        $rule = $this->getMock('\Magento\Tax\Model\Calculation\Rule', [], [], '', false);
        $rule->expects($this->once())->method('getId')->willReturn(10);

        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->resource->expects($this->once())->method('save')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('registerTaxRule')->with($rule);
        $this->assertEquals($rule, $this->model->save($rule));
    }

    public function testGetList()
    {
        $taxRuleOne = $this->getMock('\Magento\Tax\Api\Data\TaxRuleInterface');
        $taxRuleTwo = $this->getMock('\Magento\Tax\Api\Data\TaxRuleInterface');
        $searchCriteria = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([]);
        $searchCriteria->expects($this->once())->method('getPageSize')->willReturn(20);
        $searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn(0);

        $result = $this->getMock('\Magento\Tax\Api\Data\TaxRuleSearchResultsInterface');
        $collection = $this->objectManager->getCollectionMock(
            '\Magento\Tax\Model\Resource\TaxClass\Collection',
            [$taxRuleOne, $taxRuleTwo]
        );
        $collection->expects($this->any())->method('getSize')->willReturn(2);
        $collection->expects($this->any())->method('setItems')->with([$taxRuleOne, $taxRuleTwo]);
        $collection->expects($this->once())->method('setCurPage')->with(0);
        $collection->expects($this->once())->method('setPageSize')->with(20);

        $this->searchResultBuilder->expects($this->once())->method('setSearchCriteria')->with($searchCriteria);
        $this->searchResultBuilder->expects($this->once())->method('setTotalCount')->with(2);
        $this->searchResultBuilder->expects($this->once())->method('create')->willReturn($result);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);

        $this->assertEquals($result, $this->model->getList($searchCriteria));
    }
}
