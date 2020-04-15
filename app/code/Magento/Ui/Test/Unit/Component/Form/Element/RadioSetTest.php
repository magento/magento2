<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\RadioSet;

/**
 * @method RadioSet getModel
 */
class RadioSetTest extends AbstractElementTest
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
