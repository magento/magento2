<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Structure\Element;

class AbstractCompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\AbstractComposite
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

    /**
     * Test element data
     *
     * @var array
     */
    protected $_testData = array(
        'id' => 'elementId',
        'label' => 'Element Label',
        'someAttribute' => 'Some attribute value',
        'children' => array('someGroup' => array())
    );

    protected function setUp()
    {
        $this->_iteratorMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Iterator',
            array(),
            array(),
            '',
            false
        );
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Backend\Model\Config\Structure\Element\AbstractComposite',
            array($this->_storeManagerMock, $this->_iteratorMock)
        );
    }

    protected function tearDown()
    {
        unset($this->_iteratorMock);
        unset($this->_storeManagerMock);
        unset($this->_model);
    }

    public function testSetDataInitializesChildIterator()
    {
        $this->_iteratorMock->expects(
            $this->once()
        )->method(
            'setElements'
        )->with(
            array('someGroup' => array()),
            'scope'
        );
        $this->_model->setData($this->_testData, 'scope');
    }

    public function testSetDataInitializesChildIteratorWithEmptyArrayIfNoChildrenArePresent()
    {
        $this->_iteratorMock->expects($this->once())->method('setElements')->with(array(), 'scope');
        $this->_model->setData(array(), 'scope');
    }

    public function testHasChildrenReturnsFalseIfThereAreNoChildren()
    {
        $this->assertFalse($this->_model->hasChildren());
    }

    public function testHasChildrenReturnsTrueIfThereAreVisibleChildren()
    {
        $this->_iteratorMock->expects($this->once())->method('current')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('valid')->will($this->returnValue(true));
        $this->assertTrue($this->_model->hasChildren());
    }

    public function testIsVisibleReturnsTrueIfThereAreVisibleChildren()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('current')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('valid')->will($this->returnValue(true));
        $this->_model->setData(array('showInDefault' => 'true'), 'default');
        $this->assertTrue($this->_model->isVisible());
    }

    public function testIsVisibleReturnsTrueIfElementHasFrontEndModel()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(array('showInDefault' => 'true', 'frontend_model' => 'Model_Name'), 'default');
        $this->assertTrue($this->_model->isVisible());
    }

    public function testIsVisibleReturnsFalseIfElementHasNoChildrenAndFrontendModel()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(array('showInDefault' => 'true'), 'default');
        $this->assertFalse($this->_model->isVisible());
    }
}
