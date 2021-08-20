<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Annotation;

use Magento\Framework\App\Area;
use Magento\TestFramework\Annotation\TestCaseAnnotation;
use ReflectionProperty;

class AppAreaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\AppArea
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\Application|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_applicationMock;

    /**
     * @var \PHPUnit\Framework\TestCase|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_testCaseMock;

    /**
     * @var TestCaseAnnotation
     */
    private $testCaseAnnotationsMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_testCaseMock = $this->createMock(\PHPUnit\Framework\TestCase::class);
        $this->testCaseAnnotationsMock = $this->createMock(TestCaseAnnotation::class);
        $this->_applicationMock = $this->createMock(\Magento\TestFramework\Application::class);
        $this->_object = new \Magento\TestFramework\Annotation\AppArea($this->_applicationMock);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $property = new ReflectionProperty(TestCaseAnnotation::class, 'instance');
        $property->setAccessible(true);
        $property->setValue(null);
    }

    /**
     * @param array $annotations
     * @param string $expectedArea
     * @dataProvider getTestAppAreaDataProvider
     */
    public function testGetTestAppArea($annotations, $expectedArea)
    {
        $property = new ReflectionProperty(TestCaseAnnotation::class, 'instance');
        $property->setAccessible(true);
        $property->setValue($this->testCaseAnnotationsMock);
        $this->testCaseAnnotationsMock->expects($this->once())->method('getAnnotations')->willReturn($annotations);
        $this->_applicationMock->expects($this->any())->method('getArea')->willReturn(null);
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
     */
    public function testGetTestAppAreaWithInvalidArea()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $annotations = ['method' => ['magentoAppArea' => ['some_invalid_area']]];
        $property = new ReflectionProperty(TestCaseAnnotation::class, 'instance');
        $property->setAccessible(true);
        $property->setValue($this->testCaseAnnotationsMock);
        $this->testCaseAnnotationsMock->expects($this->once())->method('getAnnotations')->willReturn($annotations);

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
        $property = new ReflectionProperty(TestCaseAnnotation::class, 'instance');
        $property->setAccessible(true);
        $property->setValue($this->testCaseAnnotationsMock);
        $this->testCaseAnnotationsMock->expects($this->once())->method('getAnnotations')->willReturn($annotations);
        $this->_applicationMock->expects($this->any())->method('getArea')->willReturn(null);
        $this->_applicationMock->expects($this->once())->method('reinitialize');
        $this->_applicationMock->expects($this->once())->method('loadArea')->with($areaCode);

        $this->_object->startTest($this->_testCaseMock);
    }

    public function testStartTestPreventDoubleAreaLoadingAfterReinitialization()
    {
        $annotations = ['method' => ['magentoAppArea' => ['global']]];
        $property = new ReflectionProperty(TestCaseAnnotation::class, 'instance');
        $property->setAccessible(true);
        $property->setValue($this->testCaseAnnotationsMock);
        $this->testCaseAnnotationsMock->expects($this->once())->method('getAnnotations')->willReturn($annotations);
        $this->_applicationMock->expects($this->once())->method('reinitialize');
        $this->_applicationMock
            ->method('getArea')
            ->willReturnOnConsecutiveCalls('adminhtml', 'global');
        $this->_applicationMock->expects($this->never())->method('loadArea');
        $this->_object->startTest($this->_testCaseMock);
    }

    public function testStartTestPreventDoubleAreaLoading()
    {
        $annotations = ['method' => ['magentoAppArea' => ['adminhtml']]];
        $property = new ReflectionProperty(TestCaseAnnotation::class, 'instance');
        $property->setAccessible(true);
        $property->setValue($this->testCaseAnnotationsMock);
        $this->testCaseAnnotationsMock->expects($this->once())->method('getAnnotations')->willReturn($annotations);
        $this->_applicationMock->expects($this->once())->method('getArea')->willReturn('adminhtml');
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
            [
                'area_code' => Area::AREA_GRAPHQL,
            ],
        ];
    }
}
