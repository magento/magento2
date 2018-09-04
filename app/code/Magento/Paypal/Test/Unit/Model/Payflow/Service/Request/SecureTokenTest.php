<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Request;

use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\UrlInterface;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Paypal\Model\PayflowConfig;
use Magento\Quote\Model\Quote;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class SecureTokenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SecureToken
     */
    private $service;

    /**
     * @var Transparent|MockObject
     */
    private $transparent;

    /**
     * @var Random|MockObject
     */
    private $mathRandom;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $url = $this->getMockForAbstractClass(UrlInterface::class);
        $this->mathRandom = $this->getMockBuilder(Random::class)
            ->getMock();
        $this->transparent = $this->getMockBuilder(Transparent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new SecureToken(
            $url,
            $this->mathRandom,
            $this->transparent
        );
    }

    public function testRequestToken()
    {
        $storeId = 1;
        $secureTokenID = 'Sdj46hDokds09c8k2klaGJdKLl032ekR';
        $response = new DataObject([
            'result' => '0',
            'respmsg' => 'Approved',
            'securetoken' => '80IgSbabyj0CtBDWHZZeQN3',
            'securetokenid' => $secureTokenID,
            'result_code' => '0',
        ]);

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote->method('getStoreId')
            ->willReturn($storeId);

        $this->transparent->expects(self::once())
            ->method('setStore')
            ->with($storeId);

        $this->transparent->method('buildBasicRequest')
            ->willReturn(new DataObject());

        $config = $this->getMockBuilder(PayflowConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transparent->method('getConfig')
            ->willReturn($config);
        $this->transparent->method('postRequest')
            ->with(self::callback(function ($request) use ($secureTokenID) {
                self::assertEquals($secureTokenID, $request->getSecuretokenid(), '{Secure Token} should match.');
                return true;
            }))
            ->willReturn($response);

        $this->mathRandom->method('getUniqueHash')
            ->willReturn($secureTokenID);

        $actual = $this->service->requestToken($quote);

        self::assertEquals($secureTokenID, $actual->getSecuretokenid());
    }
}
