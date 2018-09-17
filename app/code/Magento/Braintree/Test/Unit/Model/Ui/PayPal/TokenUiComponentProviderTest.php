<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Ui\PayPal;

use Magento\Braintree\Model\Ui\PayPal\TokenUiComponentProvider;
use Magento\Framework\UrlInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class TokenUiComponentProviderTest
 */
class TokenUiComponentProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    private $paymentToken;

    /**
     * @var TokenUiComponentInterface|MockObject
     */
    private $tokenComponent;

    /**
     * @var TokenUiComponentInterfaceFactory|MockObject
     */
    private $componentFactory;

    /**
     * @var TokenUiComponentProvider
     */
    private $componentProvider;

    protected function setUp()
    {
        $this->componentFactory = $this->getMockBuilder(TokenUiComponentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->tokenComponent = $this->getMockForAbstractClass(TokenUiComponentInterface::class);

        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        
        $this->paymentToken = $this->getMockForAbstractClass(PaymentTokenInterface::class);

        $this->componentProvider = new TokenUiComponentProvider(
            $this->componentFactory,
            $this->urlBuilder
        );
    }

    /**
     * @covers \Magento\Braintree\Model\Ui\PayPal\TokenUiComponentProvider::getComponentForToken
     */
    public function testGetComponentForToken()
    {
        $tokenDetails = [
            'payerEmail' => 'john.doe@example.com'
        ];
        $hash = '4g1mn4ew0vj23n2jf';

        $this->paymentToken->expects(static::once())
            ->method('getTokenDetails')
            ->willReturn(json_encode($tokenDetails));

        $this->componentFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->tokenComponent);
        
        $this->paymentToken->expects(static::once())
            ->method('getPublicHash')
            ->willReturn($hash);
        
        $this->urlBuilder->expects(static::once())
            ->method('getUrl');

        $actual = $this->componentProvider->getComponentForToken($this->paymentToken);
        static::assertEquals($this->tokenComponent, $actual);
    }
}
