<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\ViewModel\Product;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\ViewModel\Product\Breadcrumbs;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Catalog\ViewModel\Product\Breadcrumbs.
 */
class BreadcrumbsTest extends TestCase
{
    private const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';
    private const XML_PATH_PRODUCT_USE_CATEGORIES = 'catalog/seo/product_use_categories';

    /**
     * @var Breadcrumbs
     */
    private $viewModel;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CatalogHelper|MockObject
     */
    private $catalogHelperMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var JsonHexTag|MockObject
     */
    private $serializerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->catalogHelperMock = $this->getMockBuilder(CatalogHelper::class)
            ->onlyMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->onlyMethods(['getValue', 'isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $escaper = $this->getObjectManager()->getObject(Escaper::class);

        $this->serializerMock = $this->createMock(JsonHexTag::class);

        $this->viewModel = $this->getObjectManager()->getObject(
            Breadcrumbs::class,
            [
                'catalogData' => $this->catalogHelperMock,
                'scopeConfig' => $this->scopeConfigMock,
                'escaper' => $escaper,
                'jsonSerializer' => $this->serializerMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetCategoryUrlSuffix() : void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(static::XML_PATH_CATEGORY_URL_SUFFIX, ScopeInterface::SCOPE_STORE)
            ->willReturn('.html');

        $this->assertEquals('.html', $this->viewModel->getCategoryUrlSuffix());
    }

    /**
     * @return void
     */
    public function testIsCategoryUsedInProductUrl() : void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(static::XML_PATH_PRODUCT_USE_CATEGORIES, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $this->assertFalse($this->viewModel->isCategoryUsedInProductUrl());
    }

    /**
     * @dataProvider productDataProvider
     *
     * @param $product
     * @param string $expectedName
     *
     * @return void
     */
    public function testGetProductName($product, string $expectedName) : void
    {
        if ($product!=null) {
            $product = $product($this);
        }
        $this->catalogHelperMock->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($product);

        $this->assertEquals($expectedName, $this->viewModel->getProductName());
    }

    /**
     * @return array
     */
    public static function productDataProvider() : array
    {
        return [
            [
                static fn (self $testCase) => $testCase->getObjectManager()->getObject(
                    Product::class,
                    ['data' => ['name' => 'Test']]
                ),
                'Test'
            ],
            [null, ''],
        ];
    }

    /**
     * @dataProvider productJsonEncodeDataProvider
     *
     * @param \Closure $product
     * @param string $expectedJson
     *
     * @return void
     */
    public function testGetJsonConfigurationHtmlEscaped($product, string $expectedJson) : void
    {
        if ($product!=null) {
            $product = $product($this);
        }
        $this->catalogHelperMock->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($product);

        $this->scopeConfigMock->method('isSetFlag')
            ->with(static::XML_PATH_PRODUCT_USE_CATEGORIES, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $this->scopeConfigMock->method('getValue')
            ->with(static::XML_PATH_CATEGORY_URL_SUFFIX, ScopeInterface::SCOPE_STORE)
            ->willReturn('."html');

        $this->serializerMock->expects($this->once())->method('serialize')->willReturn($expectedJson);

        $this->assertEquals($expectedJson, $this->viewModel->getJsonConfigurationHtmlEscaped());
    }

    /**
     * @return array
     */
    public static function productJsonEncodeDataProvider() : array
    {
        return [
            [
                static fn (self $testCase) => $testCase->getObjectManager()->getObject(
                    Product::class,
                    ['data' => ['name' => 'Test ™']]
                ),
                '{"breadcrumbs":{"categoryUrlSuffix":".&quot;html","useCategoryPathInUrl":0,"product":"Test \u2122"}}',
            ],
            [
                static fn (self $testCase) => $testCase->getObjectManager()->getObject(
                    Product::class,
                    ['data' => ['name' => 'Test "']]
                ),
                '{"breadcrumbs":{"categoryUrlSuffix":".&quot;html","useCategoryPathInUrl":0,"product":"Test &quot;"}}',
            ],
            [
                static fn (self $testCase) => $testCase->getObjectManager()->getObject(
                    Product::class,
                    ['data' => ['name' => 'Test <b>x</b>']]
                ),
                '{"breadcrumbs":{"categoryUrlSuffix":".&quot;html","useCategoryPathInUrl":0,"product":'
                . '"Test &lt;b&gt;x&lt;\/b&gt;"}}',
            ],
            [
                static fn (self $testCase) => $testCase->getObjectManager()->getObject(
                    Product::class,
                    ['data' => ['name' => 'Test \'abc\'']]
                ),
                '{"breadcrumbs":'
                . '{"categoryUrlSuffix":".&quot;html","useCategoryPathInUrl":0,"product":"Test &#039;abc&#039;"}}'
            ],
        ];
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager() : ObjectManager
    {
        if (null === $this->objectManager) {
            $this->objectManager = new ObjectManager($this);
        }

        return $this->objectManager;
    }
}
