<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\RadioSet;

/**
 * @method RadioSet getModel
 */
class RadioSetTest extends AbstractElementTestCase
{
    /**
     * @inheritdoc
     */
    protected function getModelName()
    {
        return RadioSet::class;
    }

    /**
     * @inheritdoc
     */
    public function testGetComponentName()
    {
        $this->assertSame(RadioSet::NAME, $this->getModel()->getComponentName());
    }
}
