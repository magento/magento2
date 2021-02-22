<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Filter;

use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class AliasResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver
     */
    private $aliasResolver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->aliasResolver = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver::class,
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
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Term::class)
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
