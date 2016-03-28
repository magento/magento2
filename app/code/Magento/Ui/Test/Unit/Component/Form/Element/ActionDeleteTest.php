<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\ActionDelete;

/**
 * Class ActionDeleteTest
 */
class ActionDeleteTest extends AbstractElementTest
{
    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return ActionDelete::class;
    }

    public function testGetComponentName()
    {
        $this->assertSame(ActionDelete::NAME, $this->getModel()->getComponentName());
    }
}
