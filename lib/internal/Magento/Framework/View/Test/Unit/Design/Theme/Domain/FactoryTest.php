<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme domain model
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme\Domain;

use PHPUnit\Framework\TestCase;
use Magento\Theme\Model\Theme;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Design\Theme\Domain\VirtualInterface;
use Magento\Framework\View\Design\Theme\Domain\Factory;
use Magento\Framework\Exception\LocalizedException;

class FactoryTest extends TestCase
{
    /**
     * @covers \Magento\Framework\View\Design\Theme\Domain\Factory::create
     */
    public function testCreate()
    {
        $themeMock = $this->createPartialMock(Theme::class, ['__wakeup', 'getType']);
        $themeMock->expects(
            $this->any()
        )->method(
            'getType'
        )->will(
            $this->returnValue(ThemeInterface::TYPE_VIRTUAL)
        );

        $newThemeMock = $this->createMock(Theme::class);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            VirtualInterface::class,
            ['theme' => $themeMock]
        )->will(
            $this->returnValue($newThemeMock)
        );

        $themeDomainFactory = new Factory($objectManager);
        $this->assertEquals($newThemeMock, $themeDomainFactory->create($themeMock));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Domain\Factory::create
     */
    public function testCreateWithWrongThemeType()
    {
        $wrongThemeType = 'wrong_theme_type';
        $themeMock = $this->createPartialMock(Theme::class, ['__wakeup', 'getType']);
        $themeMock->expects($this->any())->method('getType')->will($this->returnValue($wrongThemeType));

        $objectManager = $this->createMock(ObjectManagerInterface::class);

        $themeDomainFactory = new Factory($objectManager);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(sprintf('Invalid type of theme domain model "%s"', $wrongThemeType));
        $themeDomainFactory->create($themeMock);
    }
}
