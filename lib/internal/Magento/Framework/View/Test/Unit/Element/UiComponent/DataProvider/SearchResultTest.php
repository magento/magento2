<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\UiComponent\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use PHPUnit\Framework\TestCase;

class SearchResultTest extends TestCase
{
    /**
     * @var SearchResult
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = $this->getMockBuilder(SearchResult::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    public function testSetTotalCount(): void
    {
        $this->model->setTotalCount(2);
        self::assertEquals(2, $this->model->getTotalCount());
        self::assertEquals(2, $this->model->getSize());
    }

    public function testSetItems(): void
    {
        $totalCount = new \ReflectionProperty($this->model, 'totalCount');
        $totalRecords = new \ReflectionProperty($this->model, '_totalRecords');
        $totalCount->setAccessible(true);
        $totalRecords->setAccessible(true);
        $this->model->setTotalCount(2);
        self::assertTrue($totalCount->isInitialized($this->model));
        self::assertTrue($totalRecords->isInitialized($this->model));
        $this->model->setItems([$this->createMock(Document::class)]);
        self::assertFalse($totalCount->isInitialized($this->model));
        self::assertFalse($totalRecords->isInitialized($this->model));
    }
}
