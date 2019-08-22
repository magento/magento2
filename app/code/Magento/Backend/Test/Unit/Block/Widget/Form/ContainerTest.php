<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Form;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testSetDataObject()
    {
        $form = new \Magento\Framework\DataObject();
        $dataObject = new \Magento\Framework\DataObject();

        // _prepateLayout() is blocked, because it is used by block to instantly add 'form' child
        $block = $this->createPartialMock(\Magento\Backend\Block\Widget\Form\Container::class, ['getChildBlock']);
        $block->expects($this->once())->method('getChildBlock')->with('form')->will($this->returnValue($form));

        $block->setDataObject($dataObject);
        $this->assertSame($dataObject, $block->getDataObject());
        $this->assertSame($dataObject, $form->getDataObject());
    }
}
