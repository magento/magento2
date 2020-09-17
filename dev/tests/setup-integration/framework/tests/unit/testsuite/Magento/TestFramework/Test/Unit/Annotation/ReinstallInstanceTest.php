<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Test\Unit\Annotation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for ReinstallInstance.
 *
 * @package Magento\TestFramework\Test\Unit\Annotation
 */
class ReinstallInstanceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Annotation\ReinstallInstance
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\TestFramework\Application|\PHPUnit\Framework\MockObject\MockObject
     */
    private $applicationMock;

    protected function setUp(): void
    {
        $this->applicationMock = $this
            ->getMockBuilder(\Magento\TestFramework\Application::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\TestFramework\Annotation\ReinstallInstance::class,
            [
                'application' => $this->applicationMock
            ]
        );
    }

    public function testEndTest()
    {
        $this->applicationMock->expects($this->once())
            ->method('cleanup');
        $this->model->endTest();
    }
}
