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
class SecureTokenTest extends \PHPUnit_Framework_TestCase
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
        $this->url = $this->getMock(\Magento\Framework\UrlInterface::class, [], [], '', false);
        $this->mathRandom = $this->getMock(\Magento\Framework\Math\Random::class, [], [], '', false);
        $this->transparent = $this->getMock(\Magento\Paypal\Model\Payflow\Transparent::class, [], [], '', false);

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
            ->willReturn($this->getMock(\Magento\Paypal\Model\PayflowConfig::class, [], [], '', false));
        $this->transparent->expects($this->once())
            ->method('postRequest')
            ->willReturn(new DataObject());

        $this->mathRandom->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($secureTokenID);

        $this->url->expects($this->exactly(3))
            ->method('getUrl');

        $quote = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);

        $this->model->requestToken($quote);

        $this->assertEquals($secureTokenID, $request->getSecuretokenid());
    }
}
