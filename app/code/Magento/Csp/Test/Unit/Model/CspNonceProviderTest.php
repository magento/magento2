<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Csp\Model\Test;

use Magento\Csp\Model\CspNonceProvider;
use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Framework\Math\Random;
use Magento\Csp\Model\Policy\FetchPolicy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CspNonceProviderTest extends TestCase
{
    private Random|MockObject $randomMock;
    private MockObject|DynamicCollector $collectorMock;
    private CspNonceProvider $provider;

    protected function setUp(): void
    {
        $this->randomMock = $this->createMock(Random::class);
        $this->collectorMock = $this->createMock(DynamicCollector::class);

        $this->provider = new CspNonceProvider(
            $this->randomMock,
            $this->collectorMock
        );
    }

    public function testGenerateNonce(): void
    {
        $nonce = 'abc123nonce';
        $this->randomMock
            ->method('getRandomString')
            ->willReturn($nonce);
        $this->collectorMock
            ->expects($this->once())
            ->method('add')
            ->with($this->callback(function ($policy) use ($nonce) {
                return $policy instanceof FetchPolicy
                    && in_array($nonce, $policy->getNonceValues());
            }));

        $result = $this->provider->generateNonce();
        $this->assertEquals(base64_encode($nonce), $result);
    }

    public function testGenerateNonceCalledTwiceReturnsSameValue(): void
    {
        $nonce = 'firstnonce';

        $this->randomMock
            ->method('getRandomString')
            ->willReturn($nonce);

        $this->collectorMock
            ->expects($this->once())
            ->method('add');

        $firstCall = $this->provider->generateNonce();
        $secondCall = $this->provider->generateNonce();

        $this->assertSame($firstCall, $secondCall);
    }
}
