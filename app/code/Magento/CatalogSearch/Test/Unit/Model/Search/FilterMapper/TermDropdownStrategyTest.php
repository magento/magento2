<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\FilterMapper;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy;
use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\SelectBuilder;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy.
 *
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 */
class TermDropdownStrategyTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $eavConfig;

    /**
     * @var TermDropdownStrategy
     */
    private $termDropdownStrategy;

    /**
     * @var AliasResolver|MockObject
     */
    private $aliasResolver;

    /**
     * @var SelectBuilder|MockObject
     */
    private $selectBuilder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->eavConfig = $this->createMock(EavConfig::class);
        $this->aliasResolver = $this->createMock(AliasResolver::class);
        $this->selectBuilder = $this->createMock(SelectBuilder::class);
        $this->termDropdownStrategy = $objectManager->getObject(
            TermDropdownStrategy::class,
            [
                'eavConfig' => $this->eavConfig,
                'aliasResolver' => $this->aliasResolver,
                'selectBuilder' => $this->selectBuilder
            ]
        );
    }

    public function testApply()
    {
        $attributeId = 5;
        $alias = 'some_alias';
        $this->aliasResolver->expects($this->once())->method('getAlias')->willReturn($alias);
        $searchFilter = $this->getMockBuilder(FilterInterface::class)
            ->addMethods(['getField'])
            ->onlyMethods(['getType', 'getName'])
            ->getMockForAbstractClass();

        $select = $this->createMock(Select::class);
        $attribute = $this->createMock(Attribute::class);

        $this->eavConfig->expects($this->once())->method('getAttribute')->willReturn($attribute);
        $attribute->expects($this->once())->method('getId')->willReturn($attributeId);
        $searchFilter->expects($this->once())->method('getField');
        $this->selectBuilder->expects($this->once())->method('execute')->with($attributeId, $alias, $select);

        $this->assertTrue($this->termDropdownStrategy->apply($searchFilter, $select));
    }
}
