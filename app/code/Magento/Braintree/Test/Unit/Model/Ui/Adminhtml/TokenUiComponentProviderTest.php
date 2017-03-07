<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Ui\Adminhtml;

use Magento\Braintree\Model\Ui\Adminhtml\TokenUiComponentProvider;
use Magento\Framework\UrlInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class TokenUiComponentProviderTest
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

        $this->tokenUiComponentProvider = new TokenUiComponentProvider(
            $this->componentFactory,
            $this->urlBuilder
        );
    }

    /**
     * @covers \Magento\Braintree\Model\Ui\Adminhtml\TokenUiComponentProvider::getComponentForToken
     */
    public function testGetComponentForToken()
    {
        $nonceUrl = 'https://payment/adminhtml/nonce/url';
        $type = 'VI';
        $maskedCC = '1111';
        $expirationDate = '12/2015';

        $expected = [
            'code' => 'vault',
            'nonceUrl' => $nonceUrl,
            'details' => [
                'type' => $type,
                'maskedCC' => $maskedCC,
                'expirationDate' => $expirationDate
            ],
            'template' => 'vault.phtml'
        ];

        $paymentToken = $this->getMock(PaymentTokenInterface::class);
        $paymentToken->expects(static::once())
            ->method('getTokenDetails')
            ->willReturn('{"type":"VI","maskedCC":"1111","expirationDate":"12\/2015"}');
        $paymentToken->expects(static::once())
            ->method('getPublicHash')
            ->willReturn('37du7ir5ed');

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
