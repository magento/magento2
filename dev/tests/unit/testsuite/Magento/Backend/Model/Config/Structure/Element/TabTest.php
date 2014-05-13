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
            array(),
            array(),
            '',
            false
        );
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);

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
        $this->_model->setData(array('showInStore' => 0, 'showInWebsite' => 0, 'showInDefault' => 0), 'store');
        $this->_iteratorMock->expects($this->once())->method('current')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('valid')->will($this->returnValue(true));
        $this->assertTrue($this->_model->isVisible());
    }
}
