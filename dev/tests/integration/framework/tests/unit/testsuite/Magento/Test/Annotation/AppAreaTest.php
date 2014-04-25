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
namespace Magento\Test\Annotation;

class AppAreaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\AppArea
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_applicationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_testCaseMock;

    protected function setUp()
    {
        $this->_testCaseMock = $this->getMock('PHPUnit_Framework_TestCase', array(), array(), '', false);
        $this->_applicationMock = $this->getMock('Magento\TestFramework\Application', array(), array(), '', false);
        $this->_object = new \Magento\TestFramework\Annotation\AppArea($this->_applicationMock);
    }

    /**
     * @param array $annotations
     * @param string $expectedArea
     * @dataProvider getTestAppAreaDataProvider
     */
    public function testGetTestAppArea($annotations, $expectedArea)
    {
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_applicationMock->expects($this->any())->method('getArea')->will($this->returnValue(null));
        $this->_applicationMock->expects($this->once())->method('reinitialize');
        $this->_applicationMock->expects($this->once())->method('loadArea')->with($expectedArea);
        $this->_object->startTest($this->_testCaseMock);
    }

    public function getTestAppAreaDataProvider()
    {
        return array(
            'method scope' => array(array('method' => array('magentoAppArea' => array('adminhtml'))), 'adminhtml'),
            'class scope' => array(array('class' => array('magentoAppArea' => array('frontend'))), 'frontend'),
            'mixed scope' => array(
                array(
                    'class' => array('magentoAppArea' => array('adminhtml')),
                    'method' => array('magentoAppArea' => array('frontend'))
                ),
                'frontend'
            ),
            'default area' => array(array(), 'global')
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception
     */
    public function testGetTestAppAreaWithInvalidArea()
    {
        $annotations = array('method' => array('magentoAppArea' => array('some_invalid_area')));
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_object->startTest($this->_testCaseMock);
    }

    public function testStartTestPreventDoubleAreaLoadingAfterReinitialization()
    {
        $annotations = array('method' => array('magentoAppArea' => array('global')));
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_applicationMock->expects($this->at(0))->method('getArea')->will($this->returnValue('adminhtml'));
        $this->_applicationMock->expects($this->once())->method('reinitialize');
        $this->_applicationMock->expects($this->at(2))->method('getArea')->will($this->returnValue('global'));
        $this->_applicationMock->expects($this->never())->method('loadArea');
        $this->_object->startTest($this->_testCaseMock);
    }

    public function testStartTestPreventDoubleAreaLoading()
    {
        $annotations = array('method' => array('magentoAppArea' => array('adminhtml')));
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_applicationMock->expects($this->once())->method('getArea')->will($this->returnValue('adminhtml'));
        $this->_applicationMock->expects($this->never())->method('reinitialize');
        $this->_applicationMock->expects($this->never())->method('loadArea');
        $this->_object->startTest($this->_testCaseMock);
    }
}
