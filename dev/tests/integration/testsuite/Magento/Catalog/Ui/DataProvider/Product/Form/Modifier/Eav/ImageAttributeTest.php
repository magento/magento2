<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Provides tests for product form eav modifier with custom image attribute.
 *
 * @magentoDbIsolation enabled
 */
class ImageAttributeTest extends AbstractEavTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_image_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyMeta(): void
    {
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($this->getProduct());
        $actualMeta = $this->eavModifier->modifyMeta([]);
        $this->assertArrayNotHasKey('image_attribute', $this->getUsedAttributes($actualMeta));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_image_attribute.php
     * @return void
     */
    public function testModifyMetaNewProduct(): void
    {
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($this->getNewProduct());
        $actualMeta = $this->eavModifier->modifyMeta([]);
        $this->assertArrayNotHasKey('image_attribute', $this->getUsedAttributes($actualMeta));
    }
}
