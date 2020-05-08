<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\ThemeDependencyCheckerFactory;
use Magento\Theme\Model\Theme\ThemeDependencyChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeDependencyCheckerFactoryTest extends TestCase
{
    /**
     * @var ThemeDependencyCheckerFactory
     */
    private $themeDependencyCheckerFactory;

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
            ->with(ThemeDependencyChecker::class);
        $this->themeDependencyCheckerFactory = new ThemeDependencyCheckerFactory($this->objectManagerProvider);
        $this->themeDependencyCheckerFactory->create();
    }
}
