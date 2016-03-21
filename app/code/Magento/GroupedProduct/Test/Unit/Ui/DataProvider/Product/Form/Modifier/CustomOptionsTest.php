<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;

class CustomOptionsTest extends AbstractModifierTest
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            CustomOptionsModifier::class,
            [
                'locator' => $this->locatorMock,
                'arrayManager' => $this->arrayManagerMock
            ]
        );
    }

    public function testModifyDataNotGrouped()
    {
        $data = ['data'];

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');
        $this->arrayManagerMock->expects($this->never())
            ->method('findPath');

        $this->assertSame($data, $this->getModel()->modifyData($data));
    }

    public function testModifyData()
    {
        $data = ['data'];

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(CustomOptionsModifier::PRODUCT_TYPE_GROUPED);
        $this->arrayManagerMock->expects($this->once())
            ->method('findPath');

        $this->assertSame($data, $this->getModel()->modifyData($data));
    }
}
