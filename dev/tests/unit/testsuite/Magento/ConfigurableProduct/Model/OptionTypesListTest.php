<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model;

class OptionTypesListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionTypesList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceMock;

    protected function setUp()
    {
        $this->sourceMock = $this->getMock('\Magento\Catalog\Model\System\Config\Source\Inputtype', [], [], '', false);
        $this->model = new OptionTypesList($this->sourceMock);
    }

    public function testGetItems()
    {
        $data = [
            ['value' => 'multiselect', 'label' => __('Multiple Select')],
            ['value' => 'select', 'label' => __('Dropdown')]
        ];
        $this->sourceMock->expects($this->once())->method('toOptionArray')->willReturn($data);
        $expected = ['multiselect', 'select'];
        $this->assertEquals($expected, $this->model->getItems());
    }
}
