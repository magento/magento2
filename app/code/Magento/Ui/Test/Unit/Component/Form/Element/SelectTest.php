<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\Select;

/**
 * Class SelectTest
 *
 * @method Select getModel
 */
class SelectTest extends AbstractElementTest
{
    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return Select::class;
    }

    public function testGetComponentName()
    {
        $this->assertSame(Select::NAME, $this->getModel()->getComponentName());
    }
}
