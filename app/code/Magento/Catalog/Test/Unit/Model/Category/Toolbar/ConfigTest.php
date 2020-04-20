<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Toolbar;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CurrentCategory;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
    private $currentCategory;

    /**
     * @var CategoryConfig|MockObject
     */
    private $categoryConfig;

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
    private $category;

    protected function setUp(): void
    {
        $this->currentCategory = $this->getMockBuilder(CurrentCategory::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->categoryConfig = $this->getMockBuilder(CategoryConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultSortField'])
            ->getMock();

        $this->category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultSortBy'])
            ->getMock();

        $this->defaultSortField = 'position';
        $this->categoryConfig->expects($this->any())
            ->method('getDefaultSortField')
            ->willReturn($this->defaultSortField);

        $this->expectedSortBy = 'price';


        $this->currentCategory->expects($this->any())
            ->method('get')
            ->willReturn($this->category);

        $this->object = new Config(
            $this->currentCategory,
            $this->categoryConfig
        );
    }

    public function testGetOrderFieldDefaultSortOrderException(): void
    {
        $this->currentCategory->expects($this->any())
            ->method('get')
            ->willThrowException(new NoSuchEntityException(__('No such entity.')));

        $result = $this->object->getOrderField();

        $this->assertEquals($this->defaultSortField, $result);
    }

    public function testGetOrderFieldDefaultSortOrder()
    {
        $this->category->expects($this->any())
            ->method('getDefaultSortBy')
            ->willReturn($this->expectedSortBy);
        $result = $this->object->getOrderField();

        $this->assertEquals($this->expectedSortBy, $result);
    }

    public function testGetOrderField(): void
    {
        $this->category->expects($this->any())
            ->method('getDefaultSortBy')
            ->willReturn(null);
        $result = $this->object->getOrderField();

        $this->assertEquals($this->defaultSortField, $result);
    }
}
