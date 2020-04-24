<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Toolbar;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CurrentCategory;
use Magento\Catalog\Model\Category\Toolbar\Config;
use Magento\Catalog\Model\Category\Config as CategoryConfig;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $object;

    /**
     * @var CurrentCategory|MockObject
     */
    private $currentCategoryMock;

    /**
     * @var CategoryConfig|MockObject
     */
    private $categoryConfigMock;

    /**
     * @var string
     */
    private $defaultSortField;

    /**
     * @var string
     */
    private $expectedSortBy;

    /**
     * @var CategoryInterface|MockObject
     */
    private $categoryMock;

    protected function setUp(): void
    {
        $this->currentCategoryMock = $this->getMockBuilder(CurrentCategory::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->categoryConfigMock = $this->getMockBuilder(CategoryConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultSortField'])
            ->getMock();

        $this->categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultSortBy'])
            ->getMock();

        $this->defaultSortField = 'position';
        $this->categoryConfigMock->expects($this->any())
            ->method('getDefaultSortField')
            ->willReturn($this->defaultSortField);

        $this->expectedSortBy = 'price';

        $this->currentCategoryMock->expects($this->any())
            ->method('get')
            ->willReturn($this->categoryMock);

        $this->object = new Config(
            $this->currentCategoryMock,
            $this->categoryConfigMock
        );
    }

    public function testGetOrderFieldDefaultSortOrderException(): void
    {
        $this->currentCategoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(new NoSuchEntityException(__('No such entity.')));

        $result = $this->object->getOrderField();

        $this->assertEquals($this->defaultSortField, $result);
    }

    public function testGetOrderFieldDefaultSortOrder(): void
    {
        $this->categoryMock->expects($this->any())
            ->method('getDefaultSortBy')
            ->willReturn($this->expectedSortBy);
        $result = $this->object->getOrderField();

        $this->assertEquals($this->expectedSortBy, $result);
    }

    public function testGetOrderField(): void
    {
        $this->categoryMock->expects($this->any())
            ->method('getDefaultSortBy')
            ->willReturn(null);
        $result = $this->object->getOrderField();

        $this->assertEquals($this->defaultSortField, $result);
    }
}
