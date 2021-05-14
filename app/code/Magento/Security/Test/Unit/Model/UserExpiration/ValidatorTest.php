<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\UserExpiration;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Security\Model\UserExpiration\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\UserExpiration\Validator.
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var MockObject|TimezoneInterface
     */
    private $timezoneMock;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezoneMock = $this->getMockBuilder(Timezone::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['date'])
            ->getMock();
        $this->validator = new Validator($this->timezoneMock, $dateTimeMock);
    }

    /**
     * Verify invalid date format.
     */
    public function testWithInvalidDate(): void
    {
        $expireDate = 'invalid_date';
        $this->assertFalse($this->validator->isValid($expireDate));
        $this->assertStringContainsString(
            '"Expiration date" is not a valid date.',
            (string)current($this->validator->getMessages())
        );
    }

    /**
     * Verify invalid expire date.
     *
     * @return void
     */
    public function testWithPastDate(): void
    {
        $currentDate = new \DateTime();
        $expireDate = new \DateTime();
        $expireDate->modify('-10 days');
        $this->timezoneMock->expects(static::exactly(2))
            ->method('date')
            ->willReturnOnConsecutiveCalls(
                $currentDate,
                $expireDate
            );
        $this->assertFalse($this->validator->isValid($expireDate->format('Y-m-d H:i:s')));
        $this->assertStringContainsString(
            '"Expiration date" must be later than the current date.',
            (string)current($this->validator->getMessages())
        );
    }

    /**
     * Verify valid expire date.
     *
     * @return void
     */
    public function testWithFutureDate(): void
    {
        $currentDate = new \DateTime();
        $expireDate = new \DateTime();
        $expireDate->modify('+10 days');
        $this->timezoneMock->expects(static::exactly(2))
            ->method('date')
            ->willReturnOnConsecutiveCalls(
                $currentDate,
                $expireDate
            );
        $this->timezoneMock->expects(static::exactly(2))->method('date')
            ->willReturnOnConsecutiveCalls(
                $currentDate,
                $expireDate
            );
        static::assertTrue($this->validator->isValid($expireDate->format('Y-m-d H:i:s')));
        static::assertEquals([], $this->validator->getMessages());
    }
}
