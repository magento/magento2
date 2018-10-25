<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model\Search;

use Magento\Search\Model\Search\PageSizeProvider;

class PageSizeProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageSizeProvider
     */
    private $model;

    /**
     * @var \Magento\Search\Model\EngineResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageSizeBySearchEngineMock;

    public function setUp()
    {
        $this->pageSizeBySearchEngineMock = $this->getMockBuilder(\Magento\Search\Model\EngineResolver::class)
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

    public function getPageSizeDataProvider()
    {
        return [
            ['search', 10],
            ['catalogSearch3', 11],
            ['newSearch', PHP_INT_MAX]
        ];
    }
}
