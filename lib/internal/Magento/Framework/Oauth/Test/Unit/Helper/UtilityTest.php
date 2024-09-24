<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Test\Unit\Helper;

use Magento\Framework\Oauth\Helper\Utility as OauthUtility;
use Laminas\OAuth\Http\Utility as LaminasUtility;
use Magento\Framework\Oauth\Helper\Signature\Hmac;
use Magento\Framework\Oauth\Helper\Signature\HmacFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UtilityTest extends TestCase
{
    /**
     * @var LaminasUtility|MockObject
     */
    private LaminasUtility $httpUtility;

    /**
     * @var Hmac|MockObject
     */
    private Hmac $hmac;

    /**
     * @var HmacFactory|MockObject
     */
    private HmacFactory $hmacFactory;

    protected function setUp(): void
    {
        $this->httpUtility = $this->createMock(LaminasUtility::class);
        $this->hmac = $this->createMock(Hmac::class);
        $this->hmacFactory = $this->createMock(HmacFactory::class);
    }

    /**
     * @return void
     */
    public function testSignMethodUsesHmac256WhenSignatureMethodIsHmacSha256(): void
    {
        $params = ['param1' => 'value1'];
        $signatureMethod = 'HMACSHA256';
        $consumerSecret = 'secret';
        $tokenSecret = 'tokenSecret';
        $method = 'POST';
        $url = 'http://example.com';

        $expectedSignature = 'expectedSignature';

        $this->hmac->expects($this->once())
            ->method('sign')
            ->with($params, $method, $url)
            ->willReturn($expectedSignature);
        $this->hmacFactory->expects($this->once())
            ->method('create')
            ->with(['consumerSecret' => $consumerSecret, 'tokenSecret' => $tokenSecret, 'hashAlgo' => 'sha256'])
            ->willReturn($this->hmac);

        $utility = new OauthUtility($this->httpUtility, $this->hmacFactory);

        $signature = $utility->sign($params, $signatureMethod, $consumerSecret, $tokenSecret, $method, $url);

        $this->assertEquals($expectedSignature, $signature);
    }

    /**
     * @return void
     */
    public function testSignMethodUsesLaminasUtilityWhenSignatureMethodIsRsa(): void
    {
        $params = ['param1' => 'value1'];
        $signatureMethod = 'RSA';
        $consumerSecret = 'secret';
        $tokenSecret = 'tokenSecret';
        $method = 'POST';
        $url = 'http://example.com';

        $expectedSignature = 'expectedSignature';

        $this->httpUtility->expects($this->once())
            ->method('sign')
            ->with($params, $signatureMethod, $consumerSecret, $tokenSecret, $method, $url)
            ->willReturn($expectedSignature);

        $utility = new OauthUtility($this->httpUtility, $this->hmacFactory);

        $signature = $utility->sign($params, $signatureMethod, $consumerSecret, $tokenSecret, $method, $url);

        $this->assertEquals($expectedSignature, $signature);
    }
}
