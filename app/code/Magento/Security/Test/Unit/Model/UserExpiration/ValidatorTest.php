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
use Psr\Log\LoggerInterface;

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
     * @var MockObject|DateTime
     */
    private $dateTimeMock;

    /**
     * @var MockObject|TimezoneInterface
     */
    private $timezoneMock;

    /**
     * @var MockObject|LoggerInterface
     */
    private $loggerMock;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['error'])
            ->getMockForAbstractClass();
        $this->timezoneMock = $this->getMockBuilder(Timezone::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['date', 'getConfigTimezone'])
            ->getMock();
        $this->timezoneMock->method('getConfigTimezone')->willReturn('America/Chicago');
        $this->validator = new Validator($this->timezoneMock, $this->dateTimeMock, $this->loggerMock);
    }

    /**
     * Verify validation with invalid date format.
     *
     * @return void
     */
    public function testWithInvalidDate(): void
    {
        $exception = new \Exception('test');
        $this->timezoneMock->expects(self::once())->method('date')->willThrowException($exception);
        $this->loggerMock->expects(self::once())->method('error')->with($exception);
        $this->assertFalse($this->validator->isValid('invalidDate'));
        $this->assertStringContainsString(
            '"Expiration date" is not a valid date.',
            (string)current($this->validator->getMessages())
        );
    }

    /**
     * Verify validation with expiration date in the past.
     *
     * @return void
     */
    public function testWithPastDate(): void
    {
        $currentDate = new \DateTime();
        $currentDate = $currentDate->getTimestamp();
        $dateTime = new \DateTimeZone('America/Chicago');
        $expireDate = new \DateTime('now', $dateTime);
        $expireDate->modify('-10 days');
        $this->timezoneMock->expects(static::exactly(2))
            ->method('date')
            ->willReturn($expireDate);
        $this->dateTimeMock->expects(static::once())->method('gmtTimestamp')->willReturn($currentDate);

        $this->assertFalse($this->validator->isValid($expireDate->format('Y-m-d H:i:s')));
        $this->assertStringContainsString(
            '"Expiration date" must be later than the current date.',
            (string)current($this->validator->getMessages())
        );
    }

    /**
     * Verify validation with expiration date in the future.
     *
     * @return void
     */
    public function testWithFutureDate(): void
    {
        $currentDate = new \DateTime();
        $currentDate = $currentDate->getTimestamp();
        $dateTime = new \DateTimeZone('America/Chicago');
        $expireDate = new \DateTime('now', $dateTime);
        $expireDate->modify('+10 days');
        $this->timezoneMock->expects(static::exactly(2))
            ->method('date')
            ->willReturn($expireDate);
        $this->dateTimeMock->expects(static::once())->method('gmtTimestamp')->willReturn($currentDate);

        static::assertTrue($this->validator->isValid($expireDate->format('Y-m-d H:i:s')));
        static::assertEquals([], $this->validator->getMessages());
    }
}
