<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Description;

use Magento\Setup\Model\Description\Mixin\DescriptionMixinInterface;
use Magento\Setup\Model\Description\Mixin\MixinFactory;
use Magento\Setup\Model\Description\MixinManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MixinManagerTest extends TestCase
{
    /**
     * @var MixinManager
     */
    private $mixinManager;

    /**
     * @var MockObject|MixinFactory
     */
    private $mixinFactoryMock;

    protected function setUp(): void
    {
        $this->mixinFactoryMock = $this->createMock(MixinFactory::class);
        $this->mixinManager = new MixinManager($this->mixinFactoryMock);
    }

    public function testApply()
    {
        $description = '>o<';
        $mixinList = ['x', 'y', 'z'];

        $xMixinMock = $this->getMockForAbstractClass(
            DescriptionMixinInterface::class
        );
        $xMixinMock->expects($this->once())
            ->method('apply')
            ->with($description)
            ->willReturn($description . 'x');

        $yMixinMock = $this->getMockForAbstractClass(
            DescriptionMixinInterface::class
        );
        $yMixinMock->expects($this->once())
            ->method('apply')
            ->with($description . 'x')
            ->willReturn($description . 'xy');

        $zMixinMock = $this->getMockForAbstractClass(
            DescriptionMixinInterface::class
        );
        $zMixinMock->expects($this->once())
            ->method('apply')
            ->with($description . 'xy')
            ->willReturn($description . 'xyz');

        $this->mixinFactoryMock
            ->expects($this->exactly(count($mixinList)))
            ->method('create')
            ->withConsecutive(
                [$mixinList[0]],
                [$mixinList[1]],
                [$mixinList[2]]
            )
            ->will(
                $this->onConsecutiveCalls(
                    $xMixinMock,
                    $yMixinMock,
                    $zMixinMock
                )
            );

        $this->assertEquals(
            $description . 'xyz',
            $this->mixinManager->apply($description, $mixinList)
        );
    }
}
