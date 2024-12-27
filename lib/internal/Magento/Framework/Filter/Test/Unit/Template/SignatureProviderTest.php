<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit\Template;

class SignatureProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Filter\Template\SignatureProvider
     */
    protected $signatureProvider;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $random;

    protected function setUp(): void
    {
        $this->random = $this->createPartialMock(
            \Magento\Framework\Math\Random::class,
            ['getRandomString']
        );

        $this->signatureProvider = new \Magento\Framework\Filter\Template\SignatureProvider(
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
