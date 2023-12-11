<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Isolation\WorkingDirectory.
 */
namespace Magento\Test\Isolation;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\ObjectManager;

class AppConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Isolation\WorkingDirectory
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new \Magento\TestFramework\Isolation\AppConfig();
    }

    protected function tearDown(): void
    {
        $this->model = null;
    }

    public function testStartTestEndTest()
    {
        $test = $this->getMockBuilder(\PHPUnit\Framework\TestCase::class)
            ->disableOriginalConstructor()
            ->getMock();
        $modelReflection = new \ReflectionClass($this->model);
        $testAppConfigProperty = $modelReflection->getProperty('testAppConfig');
        $testAppConfigProperty->setAccessible(true);
        $testAppConfigMock = $this->getMockBuilder(\Magento\TestFramework\App\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testAppConfigProperty->setValue($this->model, $testAppConfigMock);
        $testAppConfigMock->expects($this->once())
            ->method('clean');
        $this->model->startTest($test);
    }
}
