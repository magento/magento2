<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Controller\Adminhtml\Product\Attribute\Delete;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Delete\AbstractDeleteAttributeControllerTest;

/**
 * Delete catalog product attributes with input types like "swatch_text" and "swatch_visual".
 * Attributes from Magento_Swatches module.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class SwatchesAttributesControllerTest extends AbstractDeleteAttributeControllerTest
{
    /**
     * Assert that attribute with input type "swatch_text" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Swatches/_files/product_text_swatch_attribute.php
     *
     * @return void
     */
    public function testDeleteSwatchTextAttribute(): void
    {
        $this->dispatchDeleteAttribute('text_swatch_attribute');
        $this->assertAttributeIsDeleted('text_swatch_attribute');
    }

    /**
     * Assert that attribute with input type "swatch_visual" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Swatches/_files/swatch_attribute.php
     *
     * @return void
     */
    public function testDeleteSwatchVisualAttribute(): void
    {
        $this->dispatchDeleteAttribute('color_swatch');
        $this->assertAttributeIsDeleted('color_swatch');
    }
}
