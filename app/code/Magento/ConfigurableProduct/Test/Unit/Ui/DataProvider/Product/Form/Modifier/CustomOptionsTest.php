<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;

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

    public function testModifyMetaNotConfigurable()
    {
        $meta = ['meta'];

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');
        $this->arrayManagerMock->expects($this->never())
            ->method('findPaths');

        $this->assertSame($meta, $this->getModel()->modifyMeta($meta));
    }

    public function testModifyMeta()
    {
        $meta = ['meta'];
        $paths = ['path1', 'path2'];

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(CustomOptionsModifier::PRODUCT_TYPE_CONFIGURABLE);
        $this->arrayManagerMock->expects($this->once())
            ->method('findPaths')
            ->willReturn($paths);

        $this->assertSame($meta, $this->getModel()->modifyMeta($meta));
    }
}
