<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model\Search;

use Magento\Search\Model\EngineResolver;
use Magento\Search\Model\Search\PageSizeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageSizeProviderTest extends TestCase
{
    /**
     * @var PageSizeProvider
     */
    private $model;

    /**
     * @var EngineResolver|MockObject
     */
    private $pageSizeBySearchEngineMock;

    protected function setUp(): void
    {
        $this->pageSizeBySearchEngineMock = $this->getMockBuilder(EngineResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new PageSizeProvider(
            $this->pageSizeBySearchEngineMock,
            ['search' => 10,
                'catalogSearch3' => 11
            ]
        );
    }

    /**
     * @param string $searchEngine
     * @param int $size
     * @dataProvider getPageSizeDataProvider
     */
    public function testGetPageSize($searchEngine, $size)
    {
        $this->pageSizeBySearchEngineMock
            ->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($searchEngine);
        $this->assertEquals($size, $this->model->getMaxPageSize());
    }

    /**
     * @return array
     */
    public static function getPageSizeDataProvider()
    {
        return [
            ['search', 10],
            ['catalogSearch3', 11],
            ['newSearch', PHP_INT_MAX]
        ];
    }
}
