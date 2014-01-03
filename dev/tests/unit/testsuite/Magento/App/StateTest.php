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
namespace Magento\App;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\State
     */
    protected $_model;

    /**
     * @var \Magento\Config\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeMock;

    protected function setUp()
    {
        $this->_scopeMock = $this->getMockForAbstractClass(
            'Magento\Config\ScopeInterface',
            array('setCurrentScope'),
            '',
            false
        );
        $this->_model = new \Magento\App\State($this->_scopeMock, time());
    }

    public function testSetAreaCode()
    {
        $areaCode = 'some code';
        $this->_scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->_model->setAreaCode($areaCode);
        $this->setExpectedException('Magento\Exception');
        $this->_model->setAreaCode('any code');
    }

    public function testGetAreaCodeException()
    {
        $this->_scopeMock->expects($this->never())->method('setCurrentScope');
        $this->setExpectedException('Magento\Exception');
        $this->_model->getAreaCode();
    }

    public function testGetAreaCode()
    {
        $areaCode = 'some code';
        $this->_scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->_model->setAreaCode($areaCode);
        $this->assertEquals($areaCode, $this->_model->getAreaCode());
    }

    public function testEmulateAreaCode()
    {
        $areaCode = 'original code';
        $emulatedCode = 'emulated code';
        $this->_scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->_model->setAreaCode($areaCode);
        $this->assertEquals(
            $emulatedCode,
            $this->_model->emulateAreaCode($emulatedCode, array($this, 'emulateAreaCodeCallback'))
        );
        $this->assertEquals($this->_model->getAreaCode(), $areaCode);
    }

    public function emulateAreaCodeCallback()
    {
        return $this->_model->getAreaCode();
    }
}
