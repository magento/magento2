<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ModuleStatusFactory;

class ModuleStatusFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ModuleStatusFactory
     */
    private $moduleStatusFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
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
