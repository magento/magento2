<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Bundle\Helper\Data
     */
    protected $helper;

    protected function setUp()
    {
        $this->config = $this->getMock('Magento\Catalog\Model\ProductTypes\ConfigInterface');
        $this->helper = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Bundle\Helper\Data',
            ['config' => $this->config]
        );
    }

    public function testGetAllowedSelectionTypes()
    {
        $configData = ['allowed_selection_types' => ['foo', 'bar', 'baz']];
        $this->config->expects($this->once())->method('getType')->with('bundle')->will($this->returnValue($configData));

        $this->assertEquals($configData['allowed_selection_types'], $this->helper->getAllowedSelectionTypes());
    }

    public function testGetAllowedSelectionTypesIfTypesIsNotSet()
    {
        $configData = [];
        $this->config->expects($this->once())->method('getType')
            ->with(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE)
            ->will($this->returnValue($configData));

        $this->assertEquals([], $this->helper->getAllowedSelectionTypes());
    }
}
