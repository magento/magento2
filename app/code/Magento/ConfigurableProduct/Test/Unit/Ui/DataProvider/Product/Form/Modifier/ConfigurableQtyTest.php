<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurableQty as ConfigurableQtyModifier;

class ConfigurableQtyTest extends AbstractModifierTest
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(ConfigurableQtyModifier::class);
    }

    public function testModifyMeta()
    {
        $meta = ['initial' => 'meta'];

        $this->assertArrayHasKey('initial', $this->getModel()->modifyMeta($meta));
    }
}
