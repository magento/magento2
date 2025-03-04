<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin\Model;

use Magento\Catalog\Plugin\Model\CategoryRepositoryPlugin;
use Magento\Catalog\Model\CategoryRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Api\AttributeInterface;
use Magento\Catalog\Model\Category;

class CategoryRepositoryPluginTest extends TestCase
{
    /**
     * @var CategoryRepositoryPlugin
     */
    private $categoryRepositoryPluginMock;

    /**
     * @var CategoryRepository|MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var Category|MockObject
     */
    private $categoryMock;

    protected function setUp(): void
    {
        $this->categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $this->categoryMock = $this->createMock(Category::class);
        $this->categoryRepositoryPluginMock = new CategoryRepositoryPlugin();
    }

    /**
     * Test beforeSave method
     */
    public function testBeforeSave()
    {
        $attributeMock = $this->createMock(AttributeInterface::class);
        $urlKey = 'new test Cat (1)!';
        $formattedUrlKey = 'new-test-cat-1';
        $this->categoryMock->method('getCustomAttribute')
            ->willReturnMap([
                ['url_key', $attributeMock],
                ['url_path', $attributeMock],
            ]);

        $this->categoryMock->method('getData')
            ->willReturn($urlKey);

        $this->categoryMock->method('formatUrlKey')
            ->willReturn($formattedUrlKey);

        $result = $this->categoryRepositoryPluginMock->beforeSave($this->categoryRepositoryMock, $this->categoryMock);
        $this->assertSame([$this->categoryMock], $result);
    }
}
