<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Tests for eav product form modifier for attribute set groups.
 */
class AttributeSetGroupsTest extends AbstractEavTest
{
    /**
     * Check that custom group for custom attribute set not added to product form modifier meta data.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_test_attribute_set.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testGroupDoesNotAddToProductFormMeta(): void
    {
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($this->getProduct());
        $meta = $this->eavModifier->modifyMeta([]);
        $this->assertArrayNotHasKey(
            'test-attribute-group-name',
            $meta,
            'Attribute set group without attributes appear on product page in admin panel'
        );
    }
}
