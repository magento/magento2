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
<<<<<<< HEAD
     * @return mixed|void
=======
     * @inheritdoc
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function testGetComponentName()
    {
        $this->assertSame(CheckboxSet::NAME, $this->getModel()->getComponentName());
    }

    public function testGetIsSelected()
    {
        $this->assertSame(false, $this->getModel()->getIsSelected(''));
    }
}
