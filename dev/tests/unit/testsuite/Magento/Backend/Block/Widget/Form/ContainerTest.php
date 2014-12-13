<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Block\Widget\Form;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetDataObject()
    {
        $form = new \Magento\Framework\Object();
        $dataObject = new \Magento\Framework\Object();

        // _prepateLayout() is blocked, because it is used by block to instantly add 'form' child
        $block = $this->getMock(
            'Magento\Backend\Block\Widget\Form\Container',
            ['getChildBlock'],
            [],
            '',
            false
        );
        $block->expects($this->once())->method('getChildBlock')->with('form')->will($this->returnValue($form));

        $block->setDataObject($dataObject);
        $this->assertSame($dataObject, $block->getDataObject());
        $this->assertSame($dataObject, $form->getDataObject());
    }
}
