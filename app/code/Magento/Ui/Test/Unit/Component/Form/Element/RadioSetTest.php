<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\RadioSet;

/**
 * Class RadioSetTest
 */
class RadioSetTest extends AbstractOptionsFieldTest
{
    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return RadioSet::class;
    }

    public function testGetComponentName()
    {
        $this->assertSame(RadioSet::NAME, $this->getModel()->getComponentName());
    }
}
