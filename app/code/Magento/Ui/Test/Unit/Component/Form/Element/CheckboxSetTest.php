<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\CheckboxSet;

/**
 * @method CheckboxSet getModel
 */
class CheckboxSetTest extends AbstractElementTestCase
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
