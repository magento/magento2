<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Plugin;

use Magento\Framework\Data\Collection;
use Magento\Theme\Plugin\Data\Collection as CollectionPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /** @var MockObject|Collection */
    private $dataCollectionMock;

    protected function setUp(): void
    {
        $this->dataCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLastPageNumber'])
            ->getMock();
    }

    public function testCurrentPageIsNotOverriddenIfFirstPage()
    {
        // Given
        $currentPagePlugin = new CollectionPlugin();

        // Expects
        $this->dataCollectionMock->expects($this->never())
            ->method('getLastPageNumber');

        // When
        $currentPagePlugin->afterGetCurPage($this->dataCollectionMock, 1);
    }

    public function testCurrentPageIsOverriddenIfNotAFirstPage()
    {
        // Given
        $currentPagePlugin = new CollectionPlugin();

        // Expects
        $this->dataCollectionMock->expects($this->once())
            ->method('getLastPageNumber');

        // When
        $currentPagePlugin->afterGetCurPage($this->dataCollectionMock, 2);
    }
}
