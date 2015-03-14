<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Test\Unit\Model;

use Magento\DesignEditor\Model\AreaEmulator;

class AreaEmulatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var AreaEmulator
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_model = new \Magento\DesignEditor\Model\AreaEmulator($this->_objectManager);
    }

    public function testEmulateLayoutArea()
    {
        $configuration = [
            'Magento\Framework\View\Layout' => [
                'arguments' => [
                    'area' => 'test_area',
                ],
            ],
        ];
        $this->_objectManager->expects($this->once())->method('configure')->with($configuration);
        $this->_model->emulateLayoutArea('test_area');
    }
}
