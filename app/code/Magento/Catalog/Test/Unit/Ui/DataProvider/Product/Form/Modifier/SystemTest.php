<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\System;
use Magento\Framework\UrlInterface;

/**
 * Class SystemTest
 *
 * @method System getModel
 */
class SystemTest extends AbstractModifierTest
{
    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    protected function setUp()
    {
        parent::setUp();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(System::class, [
            'locator' => $this->locatorMock,
            'urlBuilder' => $this->urlBuilderMock,
            'productUrls' => []
        ]);
    }

    public function testModifyData()
    {
        $submitUrl = 'http://submit.url';
        $validateUrl = 'http://validate.url';
        $reloadUrl = 'http://reload.url';
        $productId = 1;
        $storeId = 1;
        $attributeSetId = 1;

        $parameters = [
            'id' => $productId,
            'type' => Type::TYPE_SIMPLE,
            'store' => $storeId,
        ];
        $actionParameters = array_merge($parameters, ['set' => $attributeSetId]);
        $reloadParameters = array_merge(
            $parameters,
            [
                'popup' => 1,
                'componentJson' => 1,
                'prev_set_id' => $attributeSetId,
            ]
        );

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->productMock->expects($this->exactly(2))
            ->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->productMock->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn($attributeSetId);

        $this->urlBuilderMock->expects($this->exactly(3))
            ->method('getUrl')
            ->willReturnMap([
                ['catalog/product/save', $actionParameters, $submitUrl],
                ['catalog/product/validate', $actionParameters, $validateUrl],
                ['catalog/product/reload', $reloadParameters, $reloadUrl],
            ]);

        $expectedData = [
            'config' => [
                System::KEY_SUBMIT_URL => $submitUrl,
                System::KEY_VALIDATE_URL => $validateUrl,
                System::KEY_RELOAD_URL => $reloadUrl,
            ]
        ];

        $this->assertSame($expectedData, $this->getModel()->modifyData([]));
    }

    public function testModifyMeta()
    {
        $this->assertSame(['meta_key' => 'meta_value'], $this->getModel()->modifyMeta(['meta_key' => 'meta_value']));
    }
}
