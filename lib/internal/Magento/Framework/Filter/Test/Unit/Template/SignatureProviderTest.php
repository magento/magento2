<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit\Template;

use Magento\Framework\Filter\Template\SignatureProvider;
use Magento\Framework\Math\Random;
use PHPUnit\Framework\MockObject\MockObject;

class SignatureProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SignatureProvider
     */
    protected $signatureProvider;

    /**
     * @var Random|MockObject
     */
    protected $random;

    protected function setUp(): void
    {
        $this->random = $this->createPartialMock(
            Random::class,
            ['getRandomString']
        );

        $this->signatureProvider = new SignatureProvider(
            $this->random
        );
    }

    public function testGet()
    {
        $expectedResult = 'Z0FFbeCU2R8bsVGJuTdkXyiiZBzsaceV';

        $this->random->expects($this->once())
            ->method('getRandomString')
            ->with(32)
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->signatureProvider->get());

        $this->random->expects($this->never())
            ->method('getRandomString');

        $this->assertEquals($expectedResult, $this->signatureProvider->get());
    }
}
