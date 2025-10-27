<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Test\Unit\Helper;

use Magento\Framework\Oauth\Helper\Utility as OauthUtility;
use Magento\Framework\Oauth\Helper\Signature\HmacInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UtilityTest extends TestCase
{
    /**
     * @var HmacInterface|MockObject
     */
    private $hmacInterface;

    /**
     * @var OauthUtility
     */
    private $oauthUtility;

    /**
     * @throws Exception
     */
    public function setup(): void
    {
        $this->hmacInterface = $this->createMock(HmacInterface::class);

        $this->oauthUtility = new OauthUtility(
            $this->hmacInterface
        );
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
        $expectedSignature = 'signature';

        $this->hmacInterface->method('sign')->willReturn($expectedSignature);

        $signature = $this->oauthUtility->sign($params, $signatureMethod, $consumerSecret, $tokenSecret, $method, $url);

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
        $expectedSignature = 'signature';

        $this->hmacInterface->method('sign')->willReturn($expectedSignature);

        $signature = $this->oauthUtility->sign($params, $signatureMethod, $consumerSecret, $tokenSecret, $method, $url);

        $this->assertEquals($expectedSignature, $signature);
    }
}
