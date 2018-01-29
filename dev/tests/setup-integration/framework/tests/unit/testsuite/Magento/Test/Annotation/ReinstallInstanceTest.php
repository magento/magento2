<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Test\Unit\Annotation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ReinstallInstanceTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\TestFramework\Annotation\ReinstallInstance */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\TestFramework\Application|\PHPUnit_Framework_MockObject_MockObject */
    protected $applicationMock;

    protected function setUp()
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

    public function testEndTestOnReinstall()
    {
        $this->applicationMock->expects($this->once())
            ->method('isInstalled')
            ->willReturn(true);
        $this->applicationMock->expects($this->once())
            ->method('cleanup');
        $this->model->endTest();
    }

    public function testEndTestWithoutCleanup()
    {
        $this->applicationMock->expects($this->once())
            ->method('isInstalled')
            ->willReturn(false);
        $this->applicationMock->expects(self::at(0))
            ->method('cleanup');
        $this->model->endTest();
    }
}
