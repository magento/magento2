<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\UserTokenValidatorInterface;
use Magento\Integration\Model\CompositeUserTokenValidator;
use PHPUnit\Framework\TestCase;

class CompositeUserTokenValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        $userToken = $this->createMock(UserToken::class);

        $validator1 = $this->createMock(UserTokenValidatorInterface::class);
        $validator1->expects($this->once())->method('validate')->with($userToken);

        $validator2 = $this->createMock(UserTokenValidatorInterface::class);
        $validator2->expects($this->once())->method('validate')->with($userToken);

        $model = new CompositeUserTokenValidator([$validator1, $validator2]);
        $model->validate($userToken);
    }
}
