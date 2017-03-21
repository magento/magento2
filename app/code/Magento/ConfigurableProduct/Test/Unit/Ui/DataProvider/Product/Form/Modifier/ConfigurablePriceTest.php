<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePrice as ConfigurablePriceModifier;

class ConfigurablePriceTest extends AbstractModifierTest
{
    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(ConfigurablePriceModifier::class);
    }

    public function testModifyMeta()
    {
        $meta = ['initial' => 'meta'];

        $this->assertArrayHasKey('initial', $this->getModel()->modifyMeta($meta));
    }
}
