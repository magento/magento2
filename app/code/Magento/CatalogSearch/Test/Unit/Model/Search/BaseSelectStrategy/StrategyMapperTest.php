<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\BaseSelectStrategy;

use Magento\CatalogSearch\Model\Adapter\Mysql\BaseSelectStrategy\BaseSelectAttributesSearchStrategy;
use Magento\CatalogSearch\Model\Adapter\Mysql\BaseSelectStrategy\BaseSelectFullTextSearchStrategy;
use \Magento\CatalogSearch\Model\Search\BaseSelectStrategy\StrategyMapper;
use \Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;

/**
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class StrategyMapperTest extends \PHPUnit\Framework\TestCase
{
    /** @var  BaseSelectAttributesSearchStrategy|\PHPUnit\Framework\MockObject\MockObject */
    private $baseSelectAttributeSearchStrategyMock;

    /** @var  BaseSelectFullTextSearchStrategy|\PHPUnit\Framework\MockObject\MockObject */
    private $baseSelectFullTextSearchStrategyMock;

    /** @var  SelectContainer|\PHPUnit\Framework\MockObject\MockObject */
    private $selectContainerMock;

    /** @var  StrategyMapper|\PHPUnit\Framework\MockObject\MockObject */
    private $strategyMapper;

    protected function setUp(): void
    {
        $this->baseSelectAttributeSearchStrategyMock = $this->getMockBuilder(
            BaseSelectAttributesSearchStrategy::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->baseSelectFullTextSearchStrategyMock = $this->getMockBuilder(
            BaseSelectFullTextSearchStrategy::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->selectContainerMock = $this->getMockBuilder(SelectContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFullTextSearchRequired', 'hasCustomAttributesFilters', 'hasVisibilityFilter'])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->strategyMapper = $objectManagerHelper->getObject(
            StrategyMapper::class,
            [
                'baseSelectFullTextSearchStrategy' => $this->baseSelectFullTextSearchStrategyMock,
                'baseSelectAttributesSearchStrategy' => $this->baseSelectAttributeSearchStrategyMock,
            ]
        );
    }

    /**
     * @param $validStrategy
     * @param $isFullTextSearchRequired
     * @param $hasCustomAttributesFilters
     * @param $hasVisibilityFilter
     * @param $errorMsg
     * @dataProvider dataProvider
     */
    public function testBaseSelectFullTextSearchStrategy(
        $validStrategy,
        $isFullTextSearchRequired,
        $hasCustomAttributesFilters,
        $hasVisibilityFilter,
        $errorMsg
    ) {
        $this->selectContainerMock
            ->method('isFullTextSearchRequired')
            ->willReturn($isFullTextSearchRequired);

        $this->selectContainerMock
            ->method('hasCustomAttributesFilters')
            ->willReturn($hasCustomAttributesFilters);

        $this->selectContainerMock
            ->method('hasVisibilityFilter')
            ->willReturn($hasVisibilityFilter);

        $expected = $validStrategy === 'BaseSelectFullTextSearchStrategy'
            ? $this->baseSelectFullTextSearchStrategyMock
            : $this->baseSelectAttributeSearchStrategyMock;

        $this->assertSame(
            $expected,
            $this->strategyMapper->mapSelectContainerToStrategy($this->selectContainerMock),
            $errorMsg
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [
                'validStrategy' => 'BaseSelectFullTextSearchStrategy',
                'isFullTextSearchRequired' => true,
                'hasCustomAttributesFilters' => false,
                'hasVisibilityFilter' => false,
                'errorMsg' => 'BaseSelectFullTextSearchStrategy should be returned for selectContainer '
                    . 'that requires fulltext search and has no custom attributes and no visibility filters.'
            ], [
                'validStrategy' => 'BaseSelectAttributeSearchStrategy',
                'isFullTextSearchRequired' => false,
                'hasCustomAttributesFilters' => false,
                'hasVisibilityFilter' => false,
                'errorMsg' => 'BaseSelectAttributeSearchStrategy should be returned for selectContainer '
                    . 'that does not require fulltext search.'
            ], [
                'validStrategy' => 'BaseSelectAttributeSearchStrategy',
                'isFullTextSearchRequired' => false,
                'hasCustomAttributesFilters' => true,
                'hasVisibilityFilter' => true,
                'errorMsg' => 'BaseSelectAttributeSearchStrategy should be returned for selectContainer '
                    . 'that does not require fulltext search but has both custom and visibility filters.'
            ], [
                'validStrategy' => 'BaseSelectAttributeSearchStrategy',
                'isFullTextSearchRequired' => true,
                'hasCustomAttributesFilters' => true,
                'hasVisibilityFilter' => false,
                'errorMsg' => 'BaseSelectAttributeSearchStrategy should be returned for selectContainer '
                    . 'that requires fulltext search and has custom attributes.'
            ], [
                'validStrategy' => 'BaseSelectAttributeSearchStrategy',
                'isFullTextSearchRequired' => true,
                'hasCustomAttributesFilters' => false,
                'hasVisibilityFilter' => true,
                'errorMsg' => 'BaseSelectAttributeSearchStrategy should be returned for selectContainer '
                    . 'that requires fulltext search and has visibility filters.'
            ], [
                'validStrategy' => 'BaseSelectAttributeSearchStrategy',
                'isFullTextSearchRequired' => true,
                'hasCustomAttributesFilters' => true,
                'hasVisibilityFilter' => true,
                'errorMsg' => 'BaseSelectAttributeSearchStrategy should be returned for selectContainer '
                    . 'that requires fulltext search and has both custom and visibility filters.'
            ],

        ];
    }
}
