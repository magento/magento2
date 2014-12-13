<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Model;

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
        $this->_model = new AreaEmulator($this->_objectManager);
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
