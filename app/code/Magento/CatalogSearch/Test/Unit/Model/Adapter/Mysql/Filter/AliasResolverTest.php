<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Filter;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 */
class AliasResolverTest extends TestCase
{
    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->aliasResolver = $objectManagerHelper->getObject(
            AliasResolver::class,
            []
        );
    }

    /**
     * @param string $field
     * @param string $expectedAlias
     * @dataProvider aliasDataProvider
     */
    public function testGetFilterAlias($field, $expectedAlias)
    {
        $filter = $this->getMockBuilder(Term::class)
            ->setMethods(['getField'])
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->once())
            ->method('getField')
            ->willReturn($field);
        $this->assertSame($expectedAlias, $this->aliasResolver->getAlias($filter));
    }

    /**
     * @return array
     */
    public function aliasDataProvider()
    {
        return [
            'general' => [
                'field' => 'general',
                'alias' => 'general' . RequestGenerator::FILTER_SUFFIX,
            ],
            'price' => [
                'field' => 'price',
                'alias' => 'price_index',
            ],
            'category_ids' => [
                'field' => 'category_ids',
                'alias' => 'category_ids_index',
            ],
        ];
    }
}
