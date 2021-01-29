<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ThemeDependencyCheckerFactory;

class ThemeDependencyCheckerFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ThemeDependencyCheckerFactory
     */
    private $themeDependencyCheckerFactory;

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
            ->with(\Magento\Theme\Model\Theme\ThemeDependencyChecker::class);
        $this->themeDependencyCheckerFactory = new ThemeDependencyCheckerFactory($this->objectManagerProvider);
        $this->themeDependencyCheckerFactory->create();
    }
}
