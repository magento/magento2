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
namespace Magento\Framework\App;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $model;

    /**
     * @var \Magento\Framework\Config\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeMock;

    protected function setUp()
    {
        $this->scopeMock = $this->getMockForAbstractClass(
            'Magento\Framework\Config\ScopeInterface',
            array('setCurrentScope'),
            '',
            false
        );
        $this->model = new \Magento\Framework\App\State($this->scopeMock, '0');
    }

    public function testIsInstalled()
    {
        $this->assertFalse($this->model->isInstalled());
        $this->model->setInstallDate(strtotime('now'));
        $this->assertTrue($this->model->isInstalled());
    }

    public function testSetGetUpdateMode()
    {
        $this->assertFalse($this->model->getUpdateMode());
        $this->model->setUpdateMode(true);
        $this->assertTrue($this->model->getUpdateMode());
    }

    public function testSetAreaCode()
    {
        $areaCode = 'some code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->setExpectedException('Magento\Framework\Exception');
        $this->model->setAreaCode('any code');
    }

    public function testGetAreaCodeException()
    {
        $this->scopeMock->expects($this->never())->method('setCurrentScope');
        $this->setExpectedException('Magento\Framework\Exception');
        $this->model->getAreaCode();
    }

    public function testGetAreaCode()
    {
        $areaCode = 'some code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->assertEquals($areaCode, $this->model->getAreaCode());
    }

    public function testEmulateAreaCode()
    {
        $areaCode = 'original code';
        $emulatedCode = 'emulated code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->assertEquals(
            $emulatedCode,
            $this->model->emulateAreaCode($emulatedCode, array($this, 'emulateAreaCodeCallback'))
        );
        $this->assertEquals($this->model->getAreaCode(), $areaCode);
    }

    public function emulateAreaCodeCallback()
    {
        return $this->model->getAreaCode();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Some error
     */
    public function testEmulateAreaCodeException()
    {
        $areaCode = 'original code';
        $emulatedCode = 'emulated code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->model->emulateAreaCode($emulatedCode, array($this, 'emulateAreaCodeCallbackException'));
        $this->assertEquals($this->model->getAreaCode(), $areaCode);
    }

    public function emulateAreaCodeCallbackException()
    {
        throw new \Exception('Some error');
    }
}
