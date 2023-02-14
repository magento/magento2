<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Sales\Model\ValidatorResultInterfaceFactory;
use Magento\Sales\Model\ValidatorResultMerger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Sales\Model\ValidatorResultMerger
 */
class ValidatorResultMergerTest extends TestCase
{
    /**
     * @var ValidatorResultMerger
     */
    private $validatorResultMerger;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ValidatorResultInterfaceFactory|MockObject
     */
    private $validatorResultFactoryMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->validatorResultFactoryMock = $this->getMockBuilder(ValidatorResultInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = new ObjectManager($this);
        $this->validatorResultMerger = $this->objectManager->getObject(
            ValidatorResultMerger::class,
            [
                'validatorResultInterfaceFactory' => $this->validatorResultFactoryMock
            ]
        );
    }

    /**
     * Test merge method
     *
     * @return void
     */
    public function testMerge(): void
    {
        $validatorResultMock = $this->getMockForAbstractClass(ValidatorResultInterface::class);
        $orderValidationResultMock = $this->getMockForAbstractClass(ValidatorResultInterface::class);
        $creditmemoValidationResultMock = $this->getMockForAbstractClass(ValidatorResultInterface::class);
        $itemsValidationMessages = [['test04', 'test05'], ['test06']];
        $this->validatorResultFactoryMock->expects($this->once())->method('create')
            ->willReturn($validatorResultMock);
        $orderValidationResultMock->expects($this->once())->method('getMessages')->willReturn(['test01', 'test02']);
        $creditmemoValidationResultMock->expects($this->once())->method('getMessages')->willReturn(['test03']);

        $validatorResultMock
            ->method('addMessage')
            ->withConsecutive(['test01'], ['test02'], ['test03'], ['test04'], ['test05'], ['test06']);
        $expected = $validatorResultMock;
        $actual = $this->validatorResultMerger->merge(
            $orderValidationResultMock,
            $creditmemoValidationResultMock,
            ...$itemsValidationMessages
        );
        $this->assertEquals($expected, $actual);
    }
}
