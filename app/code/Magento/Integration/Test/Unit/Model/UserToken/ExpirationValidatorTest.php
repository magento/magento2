<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\UserToken;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\Data\UserTokenDataInterface;
use Magento\Integration\Model\UserToken\ExpirationValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Stdlib\DateTime\DateTime as DtUtil;

class ExpirationValidatorTest extends TestCase
{
    /**
     * @var ExpirationValidator
     */
    private $model;

    /**
     * @var DtUtil|MockObject
     */
    private $datetimeUtilMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->datetimeUtilMock = $this->createMock(DtUtil::class);
        $this->model = new ExpirationValidator($this->datetimeUtilMock);
    }

    public function getUserTokens(): array
    {
        $currentTs = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-04-07 14:00:00')
            ->getTimestamp();

        $pastToken = $this->createMock(UserToken::class);
        $pastData = $this->createMock(UserTokenDataInterface::class);
        $pastData->method('getExpires')
            ->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-04-07 12:00:00'));
        $pastToken->method('getData')->willReturn($pastData);

        $exactToken = $this->createMock(UserToken::class);
        $exactData = $this->createMock(UserTokenDataInterface::class);
        $exactData->method('getExpires')
            ->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-04-07 14:00:00'));
        $exactToken->method('getData')->willReturn($exactData);

        $futureToken = $this->createMock(UserToken::class);
        $futureData = $this->createMock(UserTokenDataInterface::class);
        $futureData->method('getExpires')
            ->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-04-07 16:00:00'));
        $futureToken->method('getData')->willReturn($futureData);

        $integrationToken = $this->createMock(UserToken::class);
        $userContext = $this->createMock(UserContextInterface::class);
        $userContext->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_INTEGRATION);
        $integrationData = $this->createMock(UserTokenDataInterface::class);
        $integrationData->method('getExpires')
            ->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-04-07 12:00:00'));
        $integrationToken->method('getData')->willReturn($pastData);
        $integrationToken->method('getUserContext')
            ->willReturn($userContext);

        return [
            'past' => [$pastToken, false, $currentTs],
            'exact' => [$exactToken, false, $currentTs],
            'future' => [$futureToken, true, $currentTs],
            'integration' => [$integrationToken, true, $currentTs],
        ];
    }

    /**
     * Test "validate" method.
     *
     * @param UserToken $userToken
     * @param bool $isValid
     * @param int $currentTimestamp
     * @throws AuthorizationException
     * @dataProvider getUserTokens
     */
    public function testValidate(UserToken $userToken, bool $isValid, int $currentTimestamp): void
    {
        if (!$isValid) {
            $this->expectException(AuthorizationException::class);
        }
        $this->datetimeUtilMock->method('gmtTimestamp')->willReturn($currentTimestamp);

        $this->model->validate($userToken);
    }
}
