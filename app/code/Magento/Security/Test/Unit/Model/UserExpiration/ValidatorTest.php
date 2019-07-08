<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\UserExpiration;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ExpiresAtValidatorTest
 *
 * @package Magento\User\Test\Unit\Model
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Security\Model\UserExpiration\Validator
     */
    private $validator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTimeMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->dateTimeMock =
            $this->createPartialMock(\Magento\Framework\Stdlib\DateTime\DateTime::class, ['gmtTimestamp']);
        $this->validator = $objectManager->getObject(
            \Magento\Security\Model\UserExpiration\Validator::class,
            ['dateTime' => $this->dateTimeMock]
        );
    }

    public function testWithPastDate()
    {
        $currentTime = '1562447687';
        $expireTime = '1561324487';
        $testDate = new \DateTime();
        $testDate->modify('-10 days');
        $this->dateTimeMock->expects(static::exactly(2))->method('gmtTimestamp')
            ->willReturnOnConsecutiveCalls(
                $currentTime,
                $expireTime
            );
        static::assertFalse($this->validator->isValid($testDate->format('Y-m-d H:i:s')));
        static::assertContains(
            '"Expiration date" must be later than the current date.',
            $this->validator->getMessages()
        );
    }

    public function testWithFutureDate()
    {
        $currentTime = '1562447687';
        $expireTime = '1563290841';
        $testDate = new \DateTime();
        $testDate->modify('+10 days');
        $this->dateTimeMock->expects(static::exactly(2))->method('gmtTimestamp')
            ->willReturnOnConsecutiveCalls(
                $currentTime,
                $expireTime
            );
        static::assertTrue($this->validator->isValid($testDate->format('Y-m-d H:i:s')));
        static::assertEquals([], $this->validator->getMessages());
    }
}
