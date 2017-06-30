<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Ui\Adminhtml\PayPal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Ui\Adminhtml\PayPal\TokenUiComponentProvider;
use Magento\Framework\UrlInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Contains methods to test PayPal token Ui component provider
 */
class TokenUiComponentProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenUiComponentInterfaceFactory|MockObject
     */
    private $componentFactory;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var TokenUiComponentProvider
     */
    private $tokenUiComponentProvider;

    protected function setUp()
    {
        $this->componentFactory = $this->getMockBuilder(TokenUiComponentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->urlBuilder = $this->getMock(UrlInterface::class);

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPayPalIcon'])
            ->getMock();

        $this->tokenUiComponentProvider = new TokenUiComponentProvider(
            $this->componentFactory,
            $this->urlBuilder,
            $this->config
        );
    }

    /**
     * @covers \Magento\Braintree\Model\Ui\Adminhtml\PayPal\TokenUiComponentProvider::getComponentForToken
     */
    public function testGetComponentForToken()
    {
        $nonceUrl = 'https://payment/adminhtml/nonce/url';
        $payerEmail = 'john.doe@test.com';
        $icon = [
            'url' => 'https://payment/adminhtml/icon.png',
            'width' => 48,
            'height' => 32
        ];

        $expected = [
            'code' => 'vault',
            'nonceUrl' => $nonceUrl,
            'details' => [
                'payerEmail' => $payerEmail,
                'icon' => $icon
            ],
            'template' => 'vault.phtml'
        ];

        $this->config->expects(static::once())
            ->method('getPayPalIcon')
            ->willReturn($icon);

        $paymentToken = $this->getMock(PaymentTokenInterface::class);
        $paymentToken->expects(static::once())
            ->method('getTokenDetails')
            ->willReturn('{"payerEmail":" ' . $payerEmail . '"}');
        $paymentToken->expects(static::once())
            ->method('getPublicHash')
            ->willReturn('cmk32dl21l');

        $this->urlBuilder->expects(static::once())
            ->method('getUrl')
            ->willReturn($nonceUrl);

        $tokenComponent = $this->getMock(TokenUiComponentInterface::class);
        $tokenComponent->expects(static::once())
            ->method('getConfig')
            ->willReturn($expected);

        $this->componentFactory->expects(static::once())
            ->method('create')
            ->willReturn($tokenComponent);

        $component = $this->tokenUiComponentProvider->getComponentForToken($paymentToken);
        static::assertEquals($tokenComponent, $component);
        static::assertEquals($expected, $component->getConfig());
    }
}
