<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Annotation\AppIsolation.
 */
namespace Magento\Test\Annotation;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\Parser\AppIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;

class AppIsolationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\AppIsolation
     */
    protected $_object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_application;

    protected function setUp(): void
    {
        /** @var ObjectManagerInterface|MockObject $objectManager */
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['get', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $sharedInstances = [
            AppIsolation::class => $this->createConfiguredMock(AppIsolation::class, ['parse' => []])
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
        $this->_application = $this->createPartialMock(\Magento\TestFramework\Application::class, ['reinitialize']);
        $this->_object = new \Magento\TestFramework\Annotation\AppIsolation($this->_application);
    }

    protected function tearDown(): void
    {
        $this->_application = null;
        $this->_object = null;
    }

    public function testStartTestSuite()
    {
        $this->_application->expects($this->once())->method('reinitialize');
        $this->_object->startTestSuite();
    }

    /**
     * @magentoAppIsolation invalid
     */
    public function testEndTestIsolationInvalid()
    {
        $this->expectException(\PHPUnit\Framework\Exception::class);

        $this->_object->endTest($this);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppIsolation disabled
     */
    public function testEndTestIsolationAmbiguous()
    {
        $this->expectException(\PHPUnit\Framework\Exception::class);

        $this->_object->endTest($this);
    }

    public function testEndTestIsolationDefault()
    {
        $this->_application->expects($this->never())->method('reinitialize');
        $this->_object->endTest($this);
    }

    public function testEndTestIsolationController()
    {
        /** @var $controllerTest \Magento\TestFramework\TestCase\AbstractController */
        $controllerTest = $this->getMockForAbstractClass(\Magento\TestFramework\TestCase\AbstractController::class);
        $this->_application->expects($this->once())->method('reinitialize');
        $this->_object->endTest($controllerTest);
    }

    /**
     * @magentoAppIsolation disabled
     */
    public function testEndTestIsolationDisabled()
    {
        $this->_application->expects($this->never())->method('reinitialize');
        $this->_object->endTest($this);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testEndTestIsolationEnabled()
    {
        $this->_application->expects($this->once())->method('reinitialize');
        $this->_object->endTest($this);
    }
}
