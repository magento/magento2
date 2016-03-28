<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\CatalogUrlRewrite\Ui\DataProvider\Product\Form\Modifier\ProductUrlRewrite;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ProductUrlRewriteTest
 */
class ProductUrlRewriteTest extends AbstractModifierTest
{
    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        parent::setUp();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
    }

    protected function createModel()
    {
        return $this->objectManager->getObject(ProductUrlRewrite::class, [
            'locator' => $this->locatorMock,
            'arrayManager' => $this->arrayManagerMock,
            'scopeConfig' => $this->scopeConfigMock,
        ]);
    }

    public function testModifyMeta()
    {
        $this->assertSame([], $this->getModel()->modifyMeta([]));

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->assertNotEmpty($this->getModel()->modifyMeta([
            'test_group_code' => [
                'children' => [
                    ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY => [
                        'label' => 'label',
                        'scopeLabel' => 'scopeLabel',
                    ],
                ],
            ],
        ]));
    }

    public function testModifyData()
    {
        $this->assertSame($this->getSampleData(), $this->getModel()->modifyData($this->getSampleData()));
    }
}
