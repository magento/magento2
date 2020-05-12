<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\UserExpiration;

use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
     * @var MockObject|\Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTimeMock;

    /**@var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    private $timezoneMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->dateTimeMock =
            $this->createPartialMock(\Magento\Framework\Stdlib\DateTime\DateTime::class, ['gmtTimestamp']);
        $this->timezoneMock =
            $this->createPartialMock(
                Timezone::class,
                ['date', 'convertConfigTimeToUtc']
            );
        $this->validator = $objectManager->getObject(
            Validator::class,
            ['dateTime' => $this->dateTimeMock, 'timezone' => $this->timezoneMock]
        );
    }

    public function testWithInvalidDate()
    {
        $expireDate = 'invalid_date';
        $this->assertFalse($this->validator->isValid($expireDate));
        $this->assertStringContainsString(
            '"Expiration date" is not a valid date.',
            (string)current($this->validator->getMessages())
        );
    }

    public function testWithPastDate()
    {
        /** @var \DateTime|MockObject $dateObject */
        $dateObject = $this->createMock(\DateTime::class);
        $this->timezoneMock->expects(static::once())
            ->method('date')
            ->willReturn($dateObject);

        $currentDate = new \DateTime();
        $currentDate = $currentDate->getTimestamp();
        $expireDate = new \DateTime();
        $expireDate->modify('-10 days');

        $this->dateTimeMock->expects(static::once())->method('gmtTimestamp')->willReturn($currentDate);
        $this->timezoneMock->expects(static::once())->method('date')->willReturn($expireDate);
        $dateObject->expects(static::once())->method('getTimestamp')->willReturn($expireDate->getTimestamp());
        $this->assertFalse($this->validator->isValid($expireDate->format('Y-m-d H:i:s')));
        $this->assertStringContainsString(
            '"Expiration date" must be later than the current date.',
            (string)current($this->validator->getMessages())
        );
    }

    public function testWithFutureDate()
    {
        /** @var \DateTime|MockObject $dateObject */
        $dateObject = $this->createMock(\DateTime::class);
        $this->timezoneMock->expects(static::once())
            ->method('date')
            ->willReturn($dateObject);
        $currentDate = new \DateTime();
        $currentDate = $currentDate->getTimestamp();
        $expireDate = new \DateTime();
        $expireDate->modify('+10 days');

        $this->dateTimeMock->expects(static::once())->method('gmtTimestamp')->willReturn($currentDate);
        $this->timezoneMock->expects(static::once())->method('date')->willReturn($expireDate);
        $dateObject->expects(static::once())->method('getTimestamp')->willReturn($expireDate->getTimestamp());
        static::assertTrue($this->validator->isValid($expireDate->format('Y-m-d H:i:s')));
        static::assertEquals([], $this->validator->getMessages());
    }
}
