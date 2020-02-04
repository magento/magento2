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
use Magento\Paypal\Model\PayflowConfig;
use Magento\Quote\Model\Quote;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test class for \Magento\Paypal\Model\Payflow\Service\Request\SecureToken
 */
class SecureTokenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SecureToken
     */
    private $model;

    /**
     * @var Transparent|MockObject
     */
    private $transparent;

    /**
     * @var Random|MockObject
     */
    private $mathRandom;

    /**
     * @var UrlInterface|MockObject
     */
    private $url;

    /**
     * @inheritdoc
     */
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
        $quote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->transparent->expects($this->once())
            ->method('buildBasicRequest')
            ->willReturn($request);
        $this->transparent->expects($this->once())
            ->method('setStore')
            ->with($storeId);
        $this->transparent->expects($this->once())
            ->method('fillCustomerContacts');
        $this->transparent->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->createMock(\Magento\Paypal\Model\PayflowConfig::class));
        $this->transparent->expects($this->once())
            ->method('postRequest')
            ->willReturn($response);

        $this->mathRandom->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($secureTokenID);

        $this->url->expects($this->exactly(3))
            ->method('getUrl');

        $this->model->requestToken($quote);

        $this->assertEquals($secureTokenID, $request->getSecuretokenid());
    }
}
