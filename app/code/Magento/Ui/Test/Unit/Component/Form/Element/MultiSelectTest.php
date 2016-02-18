<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\MultiSelect;

/**
 * Class MultiSelectTest
 */
class MultiSelectTest extends AbstractOptionsFieldTest
{
    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return MultiSelect::class;
    }

    public function testGetComponentName()
    {
        $this->assertSame(MultiSelect::NAME, $this->getModel()->getComponentName());
    }

    public function testPrepare()
    {
        $this->getModel()->prepare();

        $this->assertNotEmpty($this->getModel()->getData());
    }
}
