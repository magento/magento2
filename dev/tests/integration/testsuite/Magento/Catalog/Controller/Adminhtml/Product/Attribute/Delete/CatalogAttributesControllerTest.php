<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute\Delete;

/**
 * Delete catalog product attributes with input type "media_image" and "price".
 * Attributes from Magento_Catalog module.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class CatalogAttributesControllerTest extends AbstractDeleteAttributeControllerTest
{
    /**
     * Assert that attribute with input type "media_image" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_image_attribute.php
     *
     * @return void
     */
    public function testDeleteMediaImageAttribute(): void
    {
        $this->dispatchDeleteAttribute('image_attribute');
    }

    /**
     * Assert that attribute with input type "price" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_decimal_attribute.php
     *
     * @return void
     */
    public function testDeletePriceAttribute(): void
    {
        $this->dispatchDeleteAttribute('decimal_attribute');
    }
}
