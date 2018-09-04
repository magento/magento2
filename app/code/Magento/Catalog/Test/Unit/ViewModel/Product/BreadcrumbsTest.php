<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
    protected function setUp()
    {
        $this->catalogHelper = $this->getMockBuilder(CatalogHelper::class)
            ->setMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['getValue', 'isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->viewModel = $this->getObjectManager()->getObject(
            Breadcrumbs::class,
            [
                'catalogData' => $this->catalogHelper,
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetCategoryUrlSuffix()
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
    public function testIsCategoryUsedInProductUrl()
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
    public function testGetProductName($product, $expectedName)
    {
        $this->catalogHelper->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($product);

        $this->assertEquals($expectedName, $this->viewModel->getProductName());
    }

    /**
     * @return array
     */
    public function productDataProvider()
    {
        return [
            [$this->getObjectManager()->getObject(Product::class, ['data' => ['name' => 'Test']]), 'Test'],
            [null, ''],
        ];
    }

    /**
     * @return ObjectManager
     */
    private function getObjectManager()
    {
        if (null === $this->objectManager) {
            $this->objectManager = new ObjectManager($this);
        }

        return $this->objectManager;
    }
}
