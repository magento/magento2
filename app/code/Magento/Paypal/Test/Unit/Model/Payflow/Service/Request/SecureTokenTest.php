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
<<<<<<< HEAD
    private $service;
=======
    private $model;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    /**
     * @var Transparent|MockObject
     */
    private $transparent;

    /**
     * @var Random|MockObject
     */
    private $mathRandom;

    /**
<<<<<<< HEAD
     * @inheritdoc
     */
=======
     * @var UrlInterface|MockObject
     */
    private $url;

    /**
     * @inheritdoc
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======
        $request = new DataObject();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $storeId = 1;
        $secureTokenID = 'Sdj46hDokds09c8k2klaGJdKLl032ekR';
        $response = new DataObject([
            'result' => '0',
            'respmsg' => 'Approved',
            'securetoken' => '80IgSbabyj0CtBDWHZZeQN3',
            'securetokenid' => $secureTokenID,
            'result_code' => '0',
        ]);
<<<<<<< HEAD

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote->method('getStoreId')
            ->willReturn($storeId);
=======

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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $this->transparent->expects(self::once())
            ->method('setStore')
            ->with($storeId);

        $this->transparent->method('buildBasicRequest')
            ->willReturn(new DataObject());

<<<<<<< HEAD
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
=======
        $this->model->requestToken($quote);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        self::assertEquals($secureTokenID, $actual->getSecuretokenid());
    }
}
