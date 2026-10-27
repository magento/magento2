<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test theme domain model
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme\Domain;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Design\Theme\Domain\Factory;
use Magento\Framework\View\Design\Theme\Domain\VirtualInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class FactoryTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @covers \Magento\Framework\View\Design\Theme\Domain\Factory::create
     */
    public function testCreate()
    {
        $themeMock = $this->createPartialMockWithReflection(
            Theme::class,
            ['getType', '__wakeup']
        );
        $themeMock->expects(
            $this->any()
        )->method(
            'getType'
        )->willReturn(
            ThemeInterface::TYPE_VIRTUAL
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
        )->willReturn(
            $newThemeMock
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
        $themeMock = $this->createPartialMockWithReflection(
            Theme::class,
            ['getType', '__wakeup']
        );
        $themeMock->expects($this->any())->method('getType')->willReturn($wrongThemeType);

        $objectManager = $this->createMock(ObjectManagerInterface::class);

        $themeDomainFactory = new Factory($objectManager);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(sprintf('Invalid type of theme domain model "%s"', $wrongThemeType));
        $themeDomainFactory->create($themeMock);
    }
}
