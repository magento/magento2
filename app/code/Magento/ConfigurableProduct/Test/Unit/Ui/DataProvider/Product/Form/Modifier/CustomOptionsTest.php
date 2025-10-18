<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTestCase;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;

class CustomOptionsTest extends AbstractModifierTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->arrayManagerMock->expects($this->any())
            ->method('merge')
            ->willReturnArgument(1);
    }

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
