<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\MultiSelect;

/**
 * Class MultiSelectTest
 *
 * @method MultiSelect getModel
 */
class MultiSelectTest extends AbstractElementTest
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
        $processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->setMethods(['register', 'notify'])
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processorMock);
        $this->getModel()->prepare();

        $this->assertNotEmpty($this->getModel()->getData());
    }
}
