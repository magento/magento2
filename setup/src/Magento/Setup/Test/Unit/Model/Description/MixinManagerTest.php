<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Description;

class MixinManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\Description\MixinManager
     */
    private $mixinManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Description\Mixin\MixinFactory
     */
    private $mixinFactoryMock;

    public function setUp()
    {
        $this->mixinFactoryMock = $this->getMock(
            \Magento\Setup\Model\Description\Mixin\MixinFactory::class,
            [],
            [],
            '',
            false
        );
        $this->mixinManager = new \Magento\Setup\Model\Description\MixinManager($this->mixinFactoryMock);
    }

    public function testApply()
    {
        $description = '>o<';
        $mixinList = ['x', 'y', 'z'];

        $xMixinMock = $this->getMockForAbstractClass(
            \Magento\Setup\Model\Description\Mixin\DescriptionMixinInterface::class
        );
        $xMixinMock->expects($this->once())
            ->method('apply')
            ->with($description)
            ->willReturn($description . 'x');

        $yMixinMock = $this->getMockForAbstractClass(
            \Magento\Setup\Model\Description\Mixin\DescriptionMixinInterface::class
        );
        $yMixinMock->expects($this->once())
            ->method('apply')
            ->with($description . 'x')
            ->willReturn($description . 'xy');

        $zMixinMock = $this->getMockForAbstractClass(
            \Magento\Setup\Model\Description\Mixin\DescriptionMixinInterface::class
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
