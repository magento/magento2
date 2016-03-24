<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

    public function testModifyMeta()
    {
        $meta = ['meta'];
        $paths = ['path1', 'path2'];

        $this->arrayManagerMock->expects($this->once())
            ->method('findPaths')
            ->willReturn($paths);

        $this->assertSame($meta, $this->getModel()->modifyMeta($meta));
    }
}
