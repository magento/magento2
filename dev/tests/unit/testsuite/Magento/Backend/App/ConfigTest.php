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
namespace Magento\Backend\App;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopePool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sectionPool;

    /**
     * @var Config
     */
    protected $model;

    protected function setUp()
    {
        $this->sectionPool = $this->getMock(
            'Magento\Framework\App\Config\ScopePool',
            array('getScope', 'clean'),
            array(),
            '',
            false
        );
        $this->model = new Config($this->sectionPool);
    }

    public function testGetValue()
    {
        $expectedValue = 'some value';
        $path = 'some path';
        $configData = $this->getConfigDataMock('getValue');
        $configData->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            $this->equalTo($path)
        )->will(
            $this->returnValue($expectedValue)
        );
        $this->sectionPool->expects(
            $this->once()
        )->method(
            'getScope'
        )->with(
            $this->equalTo('default'),
            $this->isNull()
        )->will(
            $this->returnValue($configData)
        );
        $this->assertEquals($expectedValue, $this->model->getValue($path));
    }

    public function testSetValue()
    {
        $value = 'some value';
        $path = 'some path';
        $configData = $this->getConfigDataMock('setValue');
        $configData->expects($this->once())->method('setValue')->with($this->equalTo($path), $this->equalTo($value));
        $this->sectionPool->expects(
            $this->once()
        )->method(
            'getScope'
        )->with(
            $this->equalTo('default'),
            $this->isNull()
        )->will(
            $this->returnValue($configData)
        );
        $this->model->setValue($path, $value);
    }

    /**
     * @param mixed $configValue
     * @param bool $expectedResult
     * @dataProvider isSetFlagDataProvider
     */
    public function testIsSetFlag($configValue, $expectedResult)
    {
        $path = 'some path';
        $configData = $this->getConfigDataMock('getValue');
        $configData->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            $this->equalTo($path)
        )->will(
            $this->returnValue($configValue)
        );
        $this->sectionPool->expects(
            $this->once()
        )->method(
            'getScope'
        )->with(
            $this->equalTo('default'),
            $this->isNull()
        )->will(
            $this->returnValue($configData)
        );
        $this->assertEquals($expectedResult, $this->model->isSetFlag($path));
    }

    public function isSetFlagDataProvider()
    {
        return array(
            array(0, false),
            array(true, true),
            array('0', false),
            array('', false),
            array('some string', true),
            array(1, true)
        );
    }

    /**
     * Get ConfigData mock
     *
     * @param $mockedMethod
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\Data
     */
    protected function getConfigDataMock($mockedMethod)
    {
        return $this->getMock('Magento\Framework\App\Config\Data', array($mockedMethod), array(), '', false);
    }
}
