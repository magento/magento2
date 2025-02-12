<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Annotation;

use Magento\Framework\App\Area;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Annotation\TestCaseAnnotation;
use Magento\TestFramework\Fixture\Parser\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
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
        /** @var ObjectManagerInterface|MockObject $objectManager */
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['get', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $sharedInstances = [
            AppArea::class => $this->createConfiguredMock(AppArea::class, ['parse' => []])
        ];
        $objectManager->method('get')
            ->willReturnCallback(
                function (string $type) use ($sharedInstances) {
                    return $sharedInstances[$type] ?? new $type();
                }
            );
        $objectManager->method('create')
            ->willReturnCallback(
                function (string $type, array $arguments = []) {
                    return new $type(...array_values($arguments));
                }
            );

        Bootstrap::setObjectManager($objectManager);
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
        $this->testCaseAnnotationsMock->method('getAnnotations')->willReturn($annotations);
        $this->_applicationMock->expects($this->any())->method('getArea')->willReturn(null);
        $this->_applicationMock->expects($this->once())->method('reinitialize');
        $this->_applicationMock->expects($this->once())->method('loadArea')->with($expectedArea);
        $this->_object->startTest($this->_testCaseMock);
    }

    public static function getTestAppAreaDataProvider()
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
        $this->expectException(\PHPUnit\Framework\Exception::class);

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
    public static function startTestWithDifferentAreaCodes()
    {
        return [
            [
                'areaCode' => Area::AREA_GLOBAL,
            ],
            [
                'areaCode' => Area::AREA_ADMINHTML,
            ],
            [
                'areaCode' => Area::AREA_FRONTEND,
            ],
            [
                'areaCode' => Area::AREA_WEBAPI_REST,
            ],
            [
                'areaCode' => Area::AREA_WEBAPI_SOAP,
            ],
            [
                'areaCode' => Area::AREA_CRONTAB,
            ],
            [
                'areaCode' => Area::AREA_GRAPHQL,
            ],
        ];
    }
}
