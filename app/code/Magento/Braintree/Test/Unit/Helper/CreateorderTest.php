<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Braintree\Model\Adapter\BraintreeCustomer;

/**
 * Test for Createorder
 */
class CreateorderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Braintree\Helper\Createorder
     */
    private $model;

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    private $paymentHelper;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $sessionQuote;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeCustomer
     */
    private $braintreeCustomerAdapter;

    /**
     * @var \Magento\Framework\App\Config
     */
    protected $scopeConfig;

    /**
     * test setup
     */
    public function setUp()
    {

        $this->paymentHelper = $this->getMockBuilder('\Magento\Braintree\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreeCustomerAdapter = $this->getMockBuilder('\Magento\Braintree\Model\Adapter\BraintreeCustomer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionQuote = $this->getMockBuilder('\Magento\Backend\Model\Session\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getCustomerId', 'getQuote', 'getBillingAddress'])
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('\Magento\Framework\App\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            'Magento\Braintree\Helper\Createorder',
            [
                'paymentHelper' =>  $this->paymentHelper,
                'braintreeCustomerAdapter' =>  $this->braintreeCustomerAdapter,
                'sessionQuote' =>  $this->sessionQuote,
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * @param array $data
     * @param boolean|array $expected
     * @dataProvider getLoggedInCustomerCardsDataProvider
     */
    public function testGetLoggedInCustomerCards($data, $expected)
    {

        $this->sessionQuote->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($data['vault']);
        if ($data['vault']) {
            $this->sessionQuote->expects($this->any())
                ->method('getCustomerId')
                ->willReturn(1);

            $this->paymentHelper->expects($this->any())
                ->method('generateCustomerId')
                ->willReturn(1);

            $this->paymentHelper->expects($this->any())
                ->method('getCcAvailableCardTypes')
                ->willReturn([
                    'VI' => "VISA",
                    'MC' => "MasterCard"
                ]);

            $this->paymentHelper->expects($this->any())
                ->method('getCcTypeCodeByName')
                ->willReturn(count($expected) ? 'VI' : false);

            $ccobj = json_decode(json_encode(['creditCards' => null]));
            $ccobj->creditCards[] = json_decode(json_encode(['cardType' => 'AE']));
            $ccobj->creditCards[] = json_decode(json_encode(['cardType' => 'VI']));
            $ccobj->creditCards[] = json_decode(json_encode(['cardType' => 'MC']));

            $this->braintreeCustomerAdapter->expects($this->any())
                ->method('find')
                ->willReturn($ccobj);

            $billing = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address')
                ->disableOriginalConstructor()
                ->setMethods(['getCountryId'])
                ->getMock();

            $billing->expects($this->once())
                ->method('getCountryId')
                ->willReturn("US");

            $quote = $this->getMockBuilder('\Magento\Quote\Model\Quote')
                ->disableOriginalConstructor()
                ->setMethods(['getBillingAddress'])
                ->getMock();

            $this->sessionQuote->expects($this->any())
                ->method('getCustomerEmail')
                ->willReturn("email@email.com");

            $quote->expects($this->any())
                ->method('getBillingAddress')
                ->willReturn($billing);

            $this->sessionQuote->expects($this->any())
                ->method('getQuote')
                ->willReturn($quote);
        }

        $result = $this->model->getLoggedInCustomerCards();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getLoggedInCustomerCardsDataProvider()
    {
        return [
                [
                    'data' => [
                        'vault' => false
                    ],
                    'expected' => [],
                ],
                [
                    'data' => [
                        'vault' => true
                    ],
                    'expected' => [],
                ],
                [
                    'data' => [
                        'vault' => true
                    ],
                    'expected' => [
                        json_decode(json_encode(['cardType' => 'AE'])),
                        json_decode(json_encode(['cardType' => 'VI'])),
                        json_decode(json_encode(['cardType' => 'MC'])),
                    ],
                ],
            ];

    }

    public function testGetMerchantId()
    {
        $this->sessionQuote->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn(1);

        $result = $this->model->getMerchantId();
        $this->assertEquals(1, $result);
    }
}
