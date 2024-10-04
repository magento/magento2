<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\ActionDelete;

class ActionDeleteTest extends AbstractElementTestCase
{
    /**
     * @inheritdoc
     */
    protected function getModelName()
    {
        return ActionDelete::class;
    }

    /**
     * @inheritdoc
     */
    public function testGetComponentName()
    {
        $this->assertSame(ActionDelete::NAME, $this->getModel()->getComponentName());
    }
}
