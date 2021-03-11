<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\CheckboxSet;

/**
 * Class CheckboxSetTest
 *
 * @method CheckboxSet getModel
 */
class CheckboxSetTest extends AbstractElementTest
{
    /**
     * @inheritdoc
     */
    protected function getModelName()
    {
        return CheckboxSet::class;
    }

    /**
     * @inheritdoc
     */
    public function testGetComponentName()
    {
        $this->assertSame(CheckboxSet::NAME, $this->getModel()->getComponentName());
    }

    public function testGetIsSelected()
    {
        $this->assertFalse($this->getModel()->getIsSelected(''));
    }
}
