<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\Catalog\Test\Unit\ViewModel\Product;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\ViewModel\Product\Breadcrumbs;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for Magento\Catalog\ViewModel\Product\Breadcrumbs.
 */
class BreadcrumbsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Breadcrumbs
     */
    private $viewModel;

    /**
     * @var CatalogHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogHelper;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
<<<<<<< HEAD
    protected function setUp()
=======
    protected function setUp() : void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $this->catalogHelper = $this->getMockBuilder(CatalogHelper::class)
            ->setMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['getValue', 'isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

<<<<<<< HEAD
=======
        $escaper = $this->getObjectManager()->getObject(\Magento\Framework\Escaper::class);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->viewModel = $this->getObjectManager()->getObject(
            Breadcrumbs::class,
            [
                'catalogData' => $this->catalogHelper,
                'scopeConfig' => $this->scopeConfig,
<<<<<<< HEAD
=======
                'escaper' => $escaper
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ]
        );
    }

    /**
     * @return void
     */
<<<<<<< HEAD
    public function testGetCategoryUrlSuffix()
=======
    public function testGetCategoryUrlSuffix() : void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('catalog/seo/category_url_suffix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn('.html');

        $this->assertEquals('.html', $this->viewModel->getCategoryUrlSuffix());
    }

    /**
     * @return void
     */
<<<<<<< HEAD
    public function testIsCategoryUsedInProductUrl()
=======
    public function testIsCategoryUsedInProductUrl() : void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('catalog/seo/product_use_categories', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $this->assertFalse($this->viewModel->isCategoryUsedInProductUrl());
    }

    /**
     * @dataProvider productDataProvider
     *
     * @param Product|null $product
     * @param string $expectedName
     * @return void
     */
<<<<<<< HEAD
    public function testGetProductName($product, $expectedName)
=======
    public function testGetProductName($product, string $expectedName) : void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $this->catalogHelper->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($product);

        $this->assertEquals($expectedName, $this->viewModel->getProductName());
    }

    /**
     * @return array
     */
<<<<<<< HEAD
    public function productDataProvider()
=======
    public function productDataProvider() : array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return [
            [$this->getObjectManager()->getObject(Product::class, ['data' => ['name' => 'Test']]), 'Test'],
            [null, ''],
        ];
    }

    /**
<<<<<<< HEAD
     * @return ObjectManager
     */
    private function getObjectManager()
=======
     * @dataProvider productJsonEncodeDataProvider
     *
     * @param Product|null $product
     * @param string $expectedJson
     * @return void
     */
    public function testGetJsonConfiguration($product, string $expectedJson) : void
    {
        $this->catalogHelper->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($product);

        $this->scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->with('catalog/seo/product_use_categories', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with('catalog/seo/category_url_suffix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn('."html');

        $this->assertEquals($expectedJson, $this->viewModel->getJsonConfiguration());
    }

    /**
     * @return array
     */
    public function productJsonEncodeDataProvider() : array
    {
        return [
            [
                $this->getObjectManager()->getObject(Product::class, ['data' => ['name' => 'Test ™']]),
                '{"breadcrumbs":{"categoryUrlSuffix":".&quot;html","useCategoryPathInUrl":0,"product":"Test \u2122"}}',
            ],
            [
                $this->getObjectManager()->getObject(Product::class, ['data' => ['name' => 'Test "']]),
                '{"breadcrumbs":{"categoryUrlSuffix":".&quot;html","useCategoryPathInUrl":0,"product":"Test &quot;"}}',
            ],
            [
                $this->getObjectManager()->getObject(Product::class, ['data' => ['name' => 'Test <b>x</b>']]),
                '{"breadcrumbs":{"categoryUrlSuffix":".&quot;html","useCategoryPathInUrl":0,"product":'
                . '"Test &lt;b&gt;x&lt;\/b&gt;"}}',
            ],
            [
                $this->getObjectManager()->getObject(Product::class, ['data' => ['name' => 'Test \'abc\'']]),
                '{"breadcrumbs":'
                . '{"categoryUrlSuffix":".&quot;html","useCategoryPathInUrl":0,"product":"Test &#039;abc&#039;"}}'
            ],
        ];
    }

    /**
     * @return ObjectManager
     */
    private function getObjectManager() : ObjectManager
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        if (null === $this->objectManager) {
            $this->objectManager = new ObjectManager($this);
        }

        return $this->objectManager;
    }
}
