<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ModuleStatusFactory;

class ModuleStatusFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleStatusFactory
     */
    private $moduleStatusFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManagerProvider = $this->getMock(
            \Magento\Setup\Model\ObjectManagerProvider::class,
            [],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
    }

    public function testCreate()
    {
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Module\Status::class);
        $this->moduleStatusFactory = new ModuleStatusFactory($this->objectManagerProvider);
        $this->moduleStatusFactory->create();
    }
}
