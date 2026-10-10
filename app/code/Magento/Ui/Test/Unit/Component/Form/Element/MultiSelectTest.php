<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Form\Element\MultiSelect;

/**
 * @method MultiSelect getModel
 */
class MultiSelectTest extends AbstractElementTestCase
{
    /**
     * @inheritdoc
     */
    protected function getModelName()
    {
        return MultiSelect::class;
    }

    /**
     * @inheritdoc
     */
    public function testGetComponentName()
    {
        $this->contextMock->expects($this->never())->method('getProcessor');

        $this->assertSame(MultiSelect::NAME, $this->getModel()->getComponentName());
    }

    public function testPrepare()
    {
        $processorMock = $this->createPartialMock(Processor::class, ['register', 'notify']);
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        $this->getModel()->prepare();

        $this->assertNotEmpty($this->getModel()->getData());
    }
}
