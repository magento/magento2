<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Module\Status;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ModuleStatusFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModuleStatusFactoryTest extends TestCase
{
    /**
     * @var ModuleStatusFactory
     */
    private $moduleStatusFactory;

    /**
     * @var MockObject|ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $this->objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
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
            ->with(Status::class);
        $this->moduleStatusFactory = new ModuleStatusFactory($this->objectManagerProvider);
        $this->moduleStatusFactory->create();
    }
}
