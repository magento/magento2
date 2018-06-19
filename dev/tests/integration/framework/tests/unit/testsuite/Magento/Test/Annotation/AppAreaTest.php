<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Annotation;

use Magento\Framework\App\Area;

class AppAreaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\AppArea
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_applicationMock;

    /**
     * @var \PHPUnit\Framework\TestCase|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_testCaseMock;

    protected function setUp()
    {
        $this->_testCaseMock = $this->createMock(\PHPUnit\Framework\TestCase::class);
        $this->_applicationMock = $this->createMock(\Magento\TestFramework\Application::class);
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
        return [
            'method scope' => [['method' => ['magentoAppArea' => ['adminhtml']]], 'adminhtml'],
            'class scope' => [['class' => ['magentoAppArea' => ['frontend']]], 'frontend'],
            'mixed scope' => [
                [
                    'class' => ['magentoAppArea' => ['adminhtml']],
                    'method' => ['magentoAppArea' => ['frontend']],
                ],
                'frontend',
            ],
            'default area' => [[], 'global']
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetTestAppAreaWithInvalidArea()
    {
        $annotations = ['method' => ['magentoAppArea' => ['some_invalid_area']]];
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_object->startTest($this->_testCaseMock);
    }

    /**
     * Check startTest() with different allowed area codes.
     *
     * @dataProvider startTestWithDifferentAreaCodes
     * @param string $areaCode
     */
    public function testStartTestWithDifferentAreaCodes(string $areaCode)
    {
        $annotations = ['method' => ['magentoAppArea' => [$areaCode]]];
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_applicationMock->expects($this->any())->method('getArea')->willReturn(null);
        $this->_applicationMock->expects($this->once())->method('reinitialize');
        $this->_applicationMock->expects($this->once())->method('loadArea')->with($areaCode);
        $this->_object->startTest($this->_testCaseMock);
    }

    public function testStartTestPreventDoubleAreaLoadingAfterReinitialization()
    {
        $annotations = ['method' => ['magentoAppArea' => ['global']]];
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_applicationMock->expects($this->at(0))->method('getArea')->will($this->returnValue('adminhtml'));
        $this->_applicationMock->expects($this->once())->method('reinitialize');
        $this->_applicationMock->expects($this->at(2))->method('getArea')->will($this->returnValue('global'));
        $this->_applicationMock->expects($this->never())->method('loadArea');
        $this->_object->startTest($this->_testCaseMock);
    }

    public function testStartTestPreventDoubleAreaLoading()
    {
        $annotations = ['method' => ['magentoAppArea' => ['adminhtml']]];
        $this->_testCaseMock->expects($this->once())->method('getAnnotations')->will($this->returnValue($annotations));
        $this->_applicationMock->expects($this->once())->method('getArea')->will($this->returnValue('adminhtml'));
        $this->_applicationMock->expects($this->never())->method('reinitialize');
        $this->_applicationMock->expects($this->never())->method('loadArea');
        $this->_object->startTest($this->_testCaseMock);
    }

    /**
     *  Provide test data for testStartTestWithDifferentAreaCodes().
     *
     * @return array
     */
    public function startTestWithDifferentAreaCodes()
    {
        return [
            [
                'area_code' => Area::AREA_GLOBAL,
            ],
            [
                'area_code' => Area::AREA_ADMINHTML,
            ],
            [
                'area_code' => Area::AREA_FRONTEND,
            ],
            [
                'area_code' => Area::AREA_WEBAPI_REST,
            ],
            [
                'area_code' => Area::AREA_WEBAPI_SOAP,
            ],
            [
                'area_code' => Area::AREA_CRONTAB,
            ],
        ];
    }
}
