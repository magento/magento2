<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field\FieldArray;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testGetArrayRows()
    {
        /** @var $block \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray */
        $block = $this->getMockForAbstractClass(
            'Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray',
            [],
            '',
            false,
            true,
            true,
            ['escapeHtml']
        );
        $block->expects($this->any())->method('escapeHtml')->will($this->returnArgument(0));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $element = $objectManager->getObject('Magento\Framework\Data\Form\Element\Multiselect');
        $element->setValue([['te<s>t' => 't<e>st', 'data&1' => 'da&ta1']]);
        $block->setElement($element);
        $this->assertEquals(
            [
                new \Magento\Framework\DataObject(
                    [
                        'te<s>t' => 't<e>st',
                        'data&1' => 'da&ta1',
                        '_id' => 0,
                        'column_values' => ['0_te<s>t' => 't<e>st', '0_data&1' => 'da&ta1'],
                    ]
                ),
            ],
            $block->getArrayRows()
        );
    }
}
