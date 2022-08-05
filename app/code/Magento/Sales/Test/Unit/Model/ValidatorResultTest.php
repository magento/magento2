<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ValidatorResult;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Sales\Model\ValidatorResult
 */
class ValidatorResultTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var ValidatorResult
     */
    private $validatorResult;

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->validatorResult = $this->objectManager->getObject(ValidatorResult::class);
    }

    /**
     * Test addMessage method
     *
     * @return void
     */
    public function testAddMessages()
    {
        $messageFirst = 'Sample message 01.';
        $messageSecond = 'Sample messages 02.';
        $messageThird = 'Sample messages 03.';
        $expected = [$messageFirst, $messageSecond, $messageThird];
        $this->validatorResult->addMessage($messageFirst);
        $this->validatorResult->addMessage($messageSecond);
        $this->validatorResult->addMessage($messageThird);
        $actual = $this->validatorResult->getMessages();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test hasMessages method
     *
     * @return void
     */
    public function testHasMessages()
    {
        $this->assertFalse($this->validatorResult->hasMessages());
        $messageFirst = 'Sample message 01.';
        $messageSecond = 'Sample messages 02.';
        $this->validatorResult->addMessage($messageFirst);
        $this->validatorResult->addMessage($messageSecond);
        $this->assertTrue($this->validatorResult->hasMessages());
    }
}
