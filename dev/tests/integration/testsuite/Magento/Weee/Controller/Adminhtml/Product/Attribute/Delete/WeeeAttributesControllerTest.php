<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Controller\Adminhtml\Product\Attribute\Delete;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Delete\AbstractDeleteAttributeControllerTest;

/**
 * Delete catalog product attributes with input types like "weee".
 * Attributes from Magento_Weee module.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class WeeeAttributesControllerTest extends AbstractDeleteAttributeControllerTest
{
    /**
     * Assert that attribute with input type "weee" will be deleted
     * after dispatch delete product attribute action.
     *
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     *
     * @return void
     */
    public function testDeleteSwatchTextAttribute(): void
    {
        $this->dispatchDeleteAttribute('fixed_product_attribute');
        $this->assertAttributeIsDeleted('fixed_product_attribute');
    }
}
