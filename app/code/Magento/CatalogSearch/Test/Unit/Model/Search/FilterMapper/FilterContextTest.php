<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\FilterMapper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\ExclusionStrategy;
use Magento\CatalogSearch\Model\Search\FilterMapper\FilterContext;
use Magento\CatalogSearch\Model\Search\FilterMapper\StaticAttributeStrategy;
use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterContextTest extends TestCase
{
    /**
     * @var FilterContext|MockObject
     */
    private $filterContext;

    /**
     * @var AliasResolver|MockObject
     */
    private $aliasResolver;

    /**
     * @var Config|MockObject
     */
    private $eavConfig;

    /**
     * @var ExclusionStrategy|MockObject
     */
    private $exclusionStrategy;

    /**
     * @var TermDropdownStrategy|MockObject
     */
    private $termDropdownStrategy;

    /**
     * @var StaticAttributeStrategy|MockObject
     */
    private $staticAttributeStrategy;

    /**
     * @var Select
     */
    private $select;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->eavConfig = $this->getMockBuilder(Config::class)
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
        $this->select = $this->getMockBuilder(Select::class)
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
            ->with(Product::ENTITY, 'some_field')
            ->willReturn(null);
        $this->assertFalse($this->filterContext->apply($filter, $this->select));
    }

    public function testApplyOnTermFilterBySelect()
    {
        $filter = $this->createFilterMock('select_field', FilterInterface::TYPE_TERM);
        $attribute = $this->createAttributeMock('select');
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'select_field')
            ->willReturn($attribute);
        $this->exclusionStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(false);
        $this->termDropdownStrategy->expects($this->never())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(true);
        $this->assertFalse($this->filterContext->apply($filter, $this->select));
    }

    public function testApplyOnTermFilterByMultiSelect()
    {
        $filter = $this->createFilterMock('multiselect_field', FilterInterface::TYPE_TERM);
        $attribute = $this->createAttributeMock('multiselect');
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'multiselect_field')
            ->willReturn($attribute);
        $this->exclusionStrategy->expects($this->once())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(false);
        $this->termDropdownStrategy->expects($this->never())
            ->method('apply')
            ->with($filter, $this->select)
            ->willReturn(true);
        $this->assertFalse($this->filterContext->apply($filter, $this->select));
    }

    public function testApplyOnTermFilterByStaticAttribute()
    {
        $filter = $this->createFilterMock('multiselect_field', FilterInterface::TYPE_TERM);
        $attribute = $this->createAttributeMock('text', AbstractAttribute::TYPE_STATIC);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'multiselect_field')
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
            ->with(Product::ENTITY, 'multiselect_field')
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
     * @return FilterInterface|MockObject
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
     * @return Attribute|MockObject
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
