<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme domain model
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme\Domain;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Framework\View\Design\Theme\Domain\Factory::create
     */
    public function testCreate()
    {
        $themeMock = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup', 'getType']);
        $themeMock->expects(
            $this->any()
        )->method(
            'getType'
        )->willReturn(
            \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
        );

        $newThemeMock = $this->createMock(\Magento\Theme\Model\Theme::class);

        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Framework\View\Design\Theme\Domain\VirtualInterface::class,
            ['theme' => $themeMock]
        )->willReturn(
            $newThemeMock
        );

        $themeDomainFactory = new \Magento\Framework\View\Design\Theme\Domain\Factory($objectManager);
        $this->assertEquals($newThemeMock, $themeDomainFactory->create($themeMock));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Domain\Factory::create
     */
    public function testCreateWithWrongThemeType()
    {
        $wrongThemeType = 'wrong_theme_type';
        $themeMock = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup', 'getType']);
        $themeMock->expects($this->any())->method('getType')->willReturn($wrongThemeType);

        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $themeDomainFactory = new \Magento\Framework\View\Design\Theme\Domain\Factory($objectManager);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(sprintf('Invalid type of theme domain model "%s"', $wrongThemeType));
        $themeDomainFactory->create($themeMock);
    }
}
