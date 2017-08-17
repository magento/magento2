<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Request;

use Magento\Framework\Math\Random;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * Test class for \Magento\Paypal\Model\Payflow\Service\Request\SecureToken
 */
class SecureTokenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SecureToken
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Transparent
     */
    protected $transparent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Random
     */
    protected $mathRandom;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlInterface
     */
    protected $url;

    protected function setUp()
    {
        $this->url = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->mathRandom = $this->createMock(\Magento\Framework\Math\Random::class);
        $this->transparent = $this->createMock(\Magento\Paypal\Model\Payflow\Transparent::class);

        $this->model = new SecureToken(
            $this->url,
            $this->mathRandom,
            $this->transparent
        );
    }

    public function testRequestToken()
    {
        $request = new DataObject();
        $secureTokenID = 'Sdj46hDokds09c8k2klaGJdKLl032ekR';

        $this->transparent->expects($this->once())
            ->method('buildBasicRequest')
            ->willReturn($request);
        $this->transparent->expects($this->once())
            ->method('fillCustomerContacts');
        $this->transparent->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->createMock(\Magento\Paypal\Model\PayflowConfig::class));
        $this->transparent->expects($this->once())
            ->method('postRequest')
            ->willReturn(new DataObject());

        $this->mathRandom->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($secureTokenID);

        $this->url->expects($this->exactly(3))
            ->method('getUrl');

        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);

        $this->model->requestToken($quote);

        $this->assertEquals($secureTokenID, $request->getSecuretokenid());
    }
}
