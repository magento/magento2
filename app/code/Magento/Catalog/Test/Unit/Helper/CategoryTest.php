<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *  Test class for checking category helper
 */
class CategoryTest extends TestCase
{
    /**
     * @var Category
     */
    private $categoryHelper;

    /**
     * @var CategoryFactory|MockObject
     */
    private $categoryFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepository;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    protected function setUp(): void
    {
        $this->mockContext();
        $this->categoryFactory = $this->createMock(CategoryFactory::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->categoryHelper = new Category(
            $this->context,
            $this->categoryFactory,
            $this->storeManager,
            $this->collectionFactory,
            $this->categoryRepository,
            $this->escaper
        );
    }

    /**
     * Test case for checking CanonicalUrl with different data
     *
     * @param mixed $params
     * @param string $categoryUrl
     * @param string $expectedCategoryUrl
     */
    #[DataProvider('getData')]
    public function testGetCanonicalUrl(mixed $params, string $categoryUrl, string $expectedCategoryUrl): void
    {
        $this->requestMock->method('getParams')->willReturn($params);
        $this->escaper->method('escapeUrl')->willReturn($expectedCategoryUrl);
        $actualCategoryUrl = $this->categoryHelper->getCanonicalUrl($categoryUrl);
        $this->assertEquals($actualCategoryUrl, $expectedCategoryUrl);
    }

    /**
     * Data provider for testGetCanonicalUrl
     *
     * @return array
     */
    public static function getData(): array
    {
        return [
            'test cases with valid product params' => [
                ['id' => 1, 'p' => 'test'],
                'http://localhost/catalog/category',
                'http://localhost/catalog/category?p=test'
            ],
            'test cases with no params' => [
                ['id' => 1],
                'http://localhost/catalog/category',
                'http://localhost/catalog/category'
            ],
            'test cases with empty params' => [
                null,
                'http://localhost/catalog/category',
                'http://localhost/catalog/category'
            ],
        ];
    }

    /**
     * Mock object for Context
     *
     * @return void
     */
    private function mockContext(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->context = $this->createPartialMock(Context::class, ['getRequest']);
        $this->context->method('getRequest')->willReturn($this->requestMock);
    }
}
