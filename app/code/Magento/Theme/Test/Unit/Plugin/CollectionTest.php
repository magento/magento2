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

/**
 * This Unit Test covers a Plugin (not Collection), overriding the `curPage` (current page)
 *
 * @see \Magento\Framework\Data\Collection
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection|MockObject
     */
    private $dataCollectionMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->dataCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLastPageNumber'])
            ->getMock();
    }

    /**
     * Test covers use-case for the first page of results. We don't expect calculation of the last page to be executed.
     *
     * @return void
     */
    public function testCurrentPageIsNotOverriddenIfFirstPage(): void
    {
        // Given
        $currentPagePlugin = new CollectionPlugin();

        // Expects
        $this->dataCollectionMock->expects($this->never())
            ->method('getLastPageNumber');

        // When
        $currentPagePlugin->afterGetCurPage($this->dataCollectionMock, 1);
    }

    /**
     * Test covers use-case for non-first page of results. We expect calculation of the last page to be executed.
     *
     * @return void
     */
    public function testCurrentPageIsOverriddenIfNotAFirstPage(): void
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
