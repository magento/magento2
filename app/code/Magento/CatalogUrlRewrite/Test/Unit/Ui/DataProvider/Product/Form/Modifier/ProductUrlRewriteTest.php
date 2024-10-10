<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTestCase;
use Magento\CatalogUrlRewrite\Ui\DataProvider\Product\Form\Modifier\ProductUrlRewrite;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ProductUrlRewriteTest extends AbstractModifierTestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * @return ModifierInterface|object
     */
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
