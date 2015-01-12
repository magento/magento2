<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Structure\Element;

class TabTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Tab
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_iteratorMock;

    protected function setUp()
    {
        $this->_iteratorMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Iterator\Field',
            [],
            [],
            '',
            false
        );
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);

        $this->_model = new \Magento\Backend\Model\Config\Structure\Element\Tab(
            $this->_storeManagerMock,
            $this->_iteratorMock
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_iteratorMock);
        unset($this->_storeManagerMock);
    }

    public function testIsVisibleOnlyChecksPresenceOfChildren()
    {
        $this->_model->setData(['showInStore' => 0, 'showInWebsite' => 0, 'showInDefault' => 0], 'store');
        $this->_iteratorMock->expects($this->once())->method('current')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('valid')->will($this->returnValue(true));
        $this->assertTrue($this->_model->isVisible());
    }
}
