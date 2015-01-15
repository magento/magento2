<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Structure\Element;

class SectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Section
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authorizationMock;

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
        $this->_authorizationMock = $this->getMock('Magento\Framework\AuthorizationInterface');

        $this->_model = new \Magento\Backend\Model\Config\Structure\Element\Section(
            $this->_storeManagerMock,
            $this->_iteratorMock,
            $this->_authorizationMock
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_iteratorMock);
        unset($this->_storeManagerMock);
        unset($this->_authorizationMock);
    }

    public function testIsAllowedReturnsFalseIfNoResourceIsSpecified()
    {
        $this->assertFalse($this->_model->isAllowed());
    }

    public function testIsAllowedReturnsTrueIfResourcesIsValidAndAllowed()
    {
        $this->_authorizationMock->expects(
            $this->once()
        )->method(
            'isAllowed'
        )->with(
            'someResource'
        )->will(
            $this->returnValue(true)
        );

        $this->_model->setData(['resource' => 'someResource'], 'store');
        $this->assertTrue($this->_model->isAllowed());
    }

    public function testIsVisibleFirstChecksIfSectionIsAllowed()
    {
        $this->_storeManagerMock->expects($this->never())->method('isSingleStoreMode');
        $this->assertFalse($this->_model->isVisible());
    }

    public function testIsVisibleProceedsWithVisibilityCheckIfSectionIsAllowed()
    {
        $this->_authorizationMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(['resource' => 'Magento_Adminhtml::all'], 'scope');
        $this->_model->isVisible();
    }
}
