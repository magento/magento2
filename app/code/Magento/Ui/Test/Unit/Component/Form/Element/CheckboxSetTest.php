<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\CheckboxSet;

/**
 * Class CheckboxSetTest
 */
class CheckboxSetTest extends AbstractOptionsFieldTest
{
    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return CheckboxSet::class;
    }

    public function testGetComponentName()
    {
        $this->assertSame(CheckboxSet::NAME, $this->getModel()->getComponentName());
    }
}
