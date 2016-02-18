<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\AbstractOptionsField;

/**
 * Class AbstractOptionsFieldTest
 *
 * @method AbstractOptionsField getModel
 */
abstract class AbstractOptionsFieldTest extends AbstractElementTest
{
    public function testGetIsSelected()
    {
        $this->assertSame(true, $this->getModel()->getIsSelected(''));
    }
}
