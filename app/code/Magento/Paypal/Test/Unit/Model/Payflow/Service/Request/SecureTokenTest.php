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

    /** @var DataObject */
    private $request;

    protected function setUp()
    {
        $this->url = $this->buildMock(UrlInterface::class);
        $this->mathRandom = $this->buildMock(Random::class);
        $this->request = new DataObject();

        $this->transparent = $this->buildPaymentService($this->request);

        $this->model = new SecureToken(
            $this->url,
            $this->mathRandom,
            $this->transparent
        );
    }

    /**
     * Test Request Token
     */
    public function testRequestToken()
    {
        $secureTokenID = 'Sdj46hDokds09c8k2klaGJdKLl032ekR';

        $this->mathRandom->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($secureTokenID);

        $this->url->expects($this->exactly(3))
            ->method('getUrl');

        /** @var Quote | \PHPUnit_Framework_MockObject_MockObject $quote */
        $quote = $this->buildMock(Quote::class);

        $this->model->requestToken($quote);

        $this->assertEquals($secureTokenID, $this->request->getSecuretokenid());
    }

    /**
     * Test request currency
     *
     * @dataProvider currencyProvider
     * @param $currency
     */
    public function testCurrency($currency)
    {
        /** @var Quote | \PHPUnit_Framework_MockObject_MockObject $quote */
        $quote = $this->buildMock(Quote::class, ['getBaseCurrencyCode']);
        $quote->expects(self::atLeastOnce())
            ->method('getBaseCurrencyCode')
            ->willReturn($currency);

        $this->model->requestToken($quote);

        $this->assertEquals($currency, $this->request->getCurrency());
    }

    /**
     * Builds default mock object
     *
     * @param string $class className
     * @param array|null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildMock($class, array $methods = [])
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Creates payment method service
     *
     * @param DataObject $request
     * @return Transparent | \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildPaymentService(DataObject $request)
    {
        $service = $this->buildMock(Transparent::class);
        $service->expects($this->once())
            ->method('buildBasicRequest')
            ->willReturn($request);
        $service->expects($this->once())
            ->method('fillCustomerContacts');
        $service->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->buildMock(PayflowConfig::class));
        $service->expects($this->once())
            ->method('postRequest')
            ->willReturn(new DataObject());

        return $service;
    }

    /**
     * DataProvider for testing currency
     *
     * @return array
     */
    public function currencyProvider()
    {
        return [['GBP'], [null], ['USD']];
    }
}
