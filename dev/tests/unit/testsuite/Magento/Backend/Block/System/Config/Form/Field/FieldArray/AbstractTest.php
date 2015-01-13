<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Config\Form\Field\FieldArray;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testGetArrayRows()
    {
        /** @var $block \Magento\Backend\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray */
        $block = $this->getMockForAbstractClass(
            'Magento\Backend\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray',
            [],
            '',
            false,
            true,
            true,
            ['escapeHtml']
        );
        $block->expects($this->any())->method('escapeHtml')->will($this->returnArgument(0));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $element = $objectManager->getObject('Magento\Framework\Data\Form\Element\Multiselect');
        $element->setValue([['test' => 'test', 'data1' => 'data1']]);
        $block->setElement($element);
        $this->assertEquals(
            [
                new \Magento\Framework\Object(
                    [
                        'test' => 'test',
                        'data1' => 'data1',
                        '_id' => 0,
                        'column_values' => ['0_test' => 'test', '0_data1' => 'data1'],
                    ]
                ),
            ],
            $block->getArrayRows()
        );
    }
}
