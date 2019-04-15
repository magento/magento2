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

/**
 * @covers \Magento\Sales\Model\ValidatorResultMerger
 */
class ValidatorResultMergerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var ValidatorResultMerger
     */
    private $validatorResultMerger;

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ValidatorResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorResultFactoryMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->validatorResultFactoryMock = $this->getMockBuilder(ValidatorResultInterfaceFactory::class)
        ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->objectManager = new ObjectManager($this);
        $this->validatorResultMerger = $this->objectManager->getObject(
            ValidatorResultMerger::class,
            [
                'validatorResultInterfaceFactory' => $this->validatorResultFactoryMock,
            ]
        );
    }

    /**
     * Test merge method
     *
     * @return void
     */
    public function testMerge()
    {
        $validatorResultMock = $this->createMock(ValidatorResultInterface::class);
        $orderValidationResultMock = $this->createMock(ValidatorResultInterface::class);
        $creditmemoValidationResultMock = $this->createMock(ValidatorResultInterface::class);
        $itemsValidationMessages = [['test04', 'test05'], ['test06']];
        $this->validatorResultFactoryMock->expects($this->once())->method('create')
            ->willReturn($validatorResultMock);
        $orderValidationResultMock->expects($this->once())->method('getMessages')->willReturn(['test01', 'test02']);
        $creditmemoValidationResultMock->expects($this->once())->method('getMessages')->willReturn(['test03']);

        $validatorResultMock->expects($this->at(0))->method('addMessage')->with('test01');
        $validatorResultMock->expects($this->at(1))->method('addMessage')->with('test02');
        $validatorResultMock->expects($this->at(2))->method('addMessage')->with('test03');
        $validatorResultMock->expects($this->at(3))->method('addMessage')->with('test04');
        $validatorResultMock->expects($this->at(4))->method('addMessage')->with('test05');
        $validatorResultMock->expects($this->at(5))->method('addMessage')->with('test06');
        $expected = $validatorResultMock;
        $actual = $this->validatorResultMerger->merge(
            $orderValidationResultMock,
            $creditmemoValidationResultMock,
            ...$itemsValidationMessages
        );
        $this->assertEquals($expected, $actual);
    }
}
