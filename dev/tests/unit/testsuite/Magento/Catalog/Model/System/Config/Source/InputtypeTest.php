<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\System\Config\Source;

class InputtypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\System\Config\Source\Inputtype
     */
    protected $_model;

    protected function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $this->_helper->getObject('Magento\Catalog\Model\System\Config\Source\Inputtype');
    }

    public function testToOptionArrayIsArray()
    {
        $this->assertInternalType('array', $this->_model->toOptionArray());
    }

    public function testToOptionArrayValid()
    {
        $expects = [
            ['value' => 'multiselect', 'label' => 'Multiple Select'],
            ['value' => 'select', 'label' => 'Dropdown'],
        ];
        $this->assertEquals($expects, $this->_model->toOptionArray());
    }
}
