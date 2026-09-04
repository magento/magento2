<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTestCase;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurableQty as ConfigurableQtyModifier;

class ConfigurableQtyTest extends AbstractModifierTestCase
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
