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

    /**@var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    private $timezoneMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->dateTimeMock =
            $this->createPartialMock(\Magento\Framework\Stdlib\DateTime\DateTime::class, ['gmtTimestamp']);
        $this->timezoneMock =
            $this->createPartialMock(
                \Magento\Framework\Stdlib\DateTime\Timezone::class,
                ['date', 'convertConfigTimeToUtc']
            );
        $this->validator = $objectManager->getObject(
            \Magento\Security\Model\UserExpiration\Validator::class,
            ['dateTime' => $this->dateTimeMock, 'timezone' => $this->timezoneMock]
        );
    }

    public function testWithPastDate()
    {
        /** @var \DateTime|\PHPUnit_Framework_MockObject_MockObject $dateObject */
        $dateObject = $this->createMock(\DateTime::class);
        $this->timezoneMock->expects(static::once())
                    ->method('date')
                    ->will(static::returnValue($dateObject));

        $currentDate = new \DateTime();
        $currentDate = $currentDate->getTimestamp();
        $expireDate = new \DateTime();
        $expireDate->modify('-10 days');

        $this->dateTimeMock->expects(static::once())->method('gmtTimestamp')->willReturn($currentDate);
        $this->timezoneMock->expects(static::once())->method('date')->willReturn($expireDate);
        $dateObject->expects(static::once())->method('getTimestamp')->willReturn($expireDate->getTimestamp());
        static::assertFalse($this->validator->isValid($expireDate->format('Y-m-d H:i:s')));
        static::assertContains(
            '"Expiration date" must be later than the current date.',
            $this->validator->getMessages()
        );
    }

    public function testWithFutureDate()
    {
        /** @var \DateTime|\PHPUnit_Framework_MockObject_MockObject $dateObject */
        $dateObject = $this->createMock(\DateTime::class);
        $this->timezoneMock->expects(static::once())
                    ->method('date')
                    ->will(static::returnValue($dateObject));
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
