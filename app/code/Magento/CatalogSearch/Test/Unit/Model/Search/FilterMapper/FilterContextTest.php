<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\FilterMapper;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\ExclusionStrategy;
use Magento\CatalogSearch\Model\Search\FilterMapper\FilterContext;
use Magento\CatalogSearch\Model\Search\FilterMapper\StaticAttributeStrategy;
use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FilterContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterContext;

    /**
     * @var AliasResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aliasResolver;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var ExclusionStrategy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exclusionStrategy;

    /**
     * @var TermDropdownStrategy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $termDropdownStrategy;

    /**
     * @var StaticAttributeStrategy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $staticAttributeStrategy;

    /**
     * @var \Magento\Framework\DB\Select
     */
    private $select;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();
        $this->aliasResolver = $this->getMockBuilder(
            AliasResolver::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getAlias'])
            ->getMock();
        $this->exclusionStrategy = $this->getMockBuilder(ExclusionStrategy::class)
            ->disableOriginalConstructor()
            ->setMethods(['apply'])
            ->getMock();
        $this->termDropdownStrategy = $this->getMockBuilder(TermDropdownStrategy::class)
            ->disableOriginalConstructor()
            ->setMethods(['apply'])
            ->getMock();
        $this->staticAttributeStrategy = $this->getMockBuilder(StaticAttributeStrategy::class)
            ->disableOriginalConstructor()
            ->setMethods(['apply'])
            ->getMock();
        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->filterContext = $objectManager->getObject(
            FilterContext::class,
            [
                'eavConfig' => $this->eavConfig,
                'aliasResolver' => $this->aliasResolver,
                'exclusionStrategy' => $this->exclusionStrategy,
                'termDropdownStrategy' => $this->termDropdownStrategy,
                'staticAttributeStrategy' => $this->staticAttributeStrategy,
            ]
        );
    }

    public function testApplyOnExclusionFilter()
    {
        $filter = $this->createFilterMock();
        $this->exclusionStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(true);
        $this->eavConfig->expects($this->never())->method('getAttribute');
        $this->assertTrue($this->filterContext->apply($filter, $this->select));
    }

    public function testApplyFilterWithoutAttribute()
    {
        $filter = $this->createFilterMock('some_field');
        $this->exclusionStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(false);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'some_field')
            ->willReturn(null);
        $this->assertFalse($this->filterContext->apply($filter, $this->select));
    }

    public function testApplyOnTermFilterBySelect()
    {
        $filter = $this->createFilterMock('select_field', FilterInterface::TYPE_TERM);
        $attribute = $this->createAttributeMock('select');
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'select_field')
            ->willReturn($attribute);
        $this->exclusionStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(false);
        $this->termDropdownStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(true);
        $this->assertTrue($this->filterContext->apply($filter, $this->select));
    }

    public function testApplyOnTermFilterByMultiSelect()
    {
        $filter = $this->createFilterMock('multiselect_field', FilterInterface::TYPE_TERM);
        $attribute = $this->createAttributeMock('multiselect');
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'multiselect_field')
            ->willReturn($attribute);
        $this->exclusionStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(false);
        $this->termDropdownStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(true);
        $this->assertTrue($this->filterContext->apply($filter, $this->select));
    }

    public function testApplyOnTermFilterByStaticAttribute()
    {
        $filter = $this->createFilterMock('multiselect_field', FilterInterface::TYPE_TERM);
        $attribute = $this->createAttributeMock('text', AbstractAttribute::TYPE_STATIC);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'multiselect_field')
            ->willReturn($attribute);
        $this->exclusionStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(false);
        $this->staticAttributeStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(true);
        $this->assertTrue($this->filterContext->apply($filter, $this->select));
    }

    public function testApplyOnTermFilterByUnknownAttributeType()
    {
        $filter = $this->createFilterMock('multiselect_field', FilterInterface::TYPE_TERM);
        $attribute = $this->createAttributeMock('text', 'text');
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(\Magento\Catalog\Model\Product::ENTITY, 'multiselect_field')
            ->willReturn($attribute);
        $this->exclusionStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(false);
        $this->assertFalse($this->filterContext->apply($filter, $this->select));
    }

    /**
     * @param string $field
     * @param string $type
     * @return FilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFilterMock($field = null, $type = null)
    {
        $filter = $this->getMockBuilder(FilterInterface::class)
            ->setMethods(['getField', 'getType'])
            ->getMockForAbstractClass();
        $filter->expects($this->any())
            ->method('getField')
            ->willReturn($field);
        $filter->expects($this->any())
            ->method('getType')
            ->willReturn($type);

        return $filter;
    }

    /**
     * @param string|null $frontendInput
     * @param string|null $backendType
     * @return Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeMock($frontendInput = null, $backendType = null)
    {
        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFrontendInput', 'getBackendType'])
            ->getMock();
        $attribute->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);
        $attribute->expects($this->any())
            ->method('getBackendType')
            ->willReturn($backendType);
        return $attribute;
    }
}
