<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ThemeDependencyCheckerFactory;

class ThemeDependencyCheckerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThemeDependencyCheckerFactory
     */
    private $themeDependencyCheckerFactory;

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
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
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
            ->with('Magento\Theme\Model\Theme\ThemeDependencyChecker');
        $this->themeDependencyCheckerFactory = new ThemeDependencyCheckerFactory($this->objectManagerProvider);
        $this->themeDependencyCheckerFactory->create();
    }
}
