<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Exception\LocalizedException;
use \Braintree_Result_Error;
use \Braintree_Exception;
use \Braintree_CreditCard;

/**
 * Class VaultTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\Vault
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Model\Config\Cc|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Payment\Model\InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $infoInstanceMock;

    /**
     * @var \Magento\Payment\Model\Method\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Braintree\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var  \Magento\Directory\Model\ResourceModel\Country\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryFactoryMock;

    /**
     * @var \Magento\Framework\App\Cache\Type\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Braintree\Helper\Error|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $errorHelperMock;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $psrLoggerMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Customer\Model\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeCustomerMock;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeCreditCard|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreeCreditCardMock;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreePaymentMethod|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $braintreePaymentMethodMock;

    /**
     * @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;


    protected function setUp()
    {

        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesTransactionCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->cacheMock = $this->getMockBuilder('\Magento\Framework\App\Cache\Type\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->errorHelperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Error')
            ->disableOriginalConstructor()
            ->getMock();
        $this->countryFactoryMock =
            $this->getMockBuilder('\Magento\Directory\Model\ResourceModel\Country\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder('\Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerFactoryMock = $this->getMockBuilder('\Magento\Customer\Model\CustomerFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->braintreeCustomerMock = $this->getMockBuilder('\Magento\Braintree\Model\Adapter\BraintreeCustomer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeCreditCardMock = $this->getMockBuilder('\Magento\Braintree\Model\Adapter\BraintreeCreditCard')
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreePaymentMethodMock = $this->getMockBuilder(
            '\Magento\Braintree\Model\Adapter\BraintreePaymentMethod'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMock = $this->getMockBuilder('\Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Model\Vault',
            [
                'braintreeHelper' => $this->helperMock,
                'config' => $this->configMock,
                'logger' => $this->loggerMock,
                'errorHelper' => $this->errorHelperMock,
                'cache' => $this->cacheMock,
                'customerSession'=> $this->customerSessionMock,
                'customerFactory'=> $this->customerFactoryMock,
                'braintreeCustomer'=> $this->braintreeCustomerMock,
                'braintreeCreditCard'=> $this->braintreeCreditCardMock,
                'braintreePaymentMethod'=> $this->braintreePaymentMethodMock,
                'countryFactory' => $this->countryFactoryMock,
            ]
        );
    }

    public function testExists()
    {
        $customerId = 1;
        $this->braintreeCustomerMock->expects($this->once())
            ->method('find')
            ->with($customerId)
            ->willReturnSelf();
        $result = $this->model->exists($customerId);
        $this->assertEquals(true, $result);
    }

    public function testExistsException()
    {
        $exception = new \Braintree_Exception();
        $customerId = 1;
        $this->braintreeCustomerMock->expects($this->once())
            ->method('find')
            ->with($customerId)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $result = $this->model->exists($customerId);
        $this->assertEquals(false, $result);
    }

    public function testDeleteCustomer()
    {
        $customerID = 1;
        $this->braintreeCustomerMock->expects($this->once())
            ->method('delete')
            ->with($customerID)
            ->willReturnSelf();
        $result = $this->model->deleteCustomer($customerID);
        $this->assertEquals(true, is_object($result));
    }

    public function testDeleteCustomerException()
    {
        $exception = new \Braintree_Exception();
        $customerID = 1;
        $this->braintreeCustomerMock->expects($this->once())
            ->method('delete')
            ->with($customerID)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->model->deleteCustomer($customerID);
    }

    public function testStoredCard()
    {
        $cardToken = 1;
        $this->braintreeCreditCardMock->expects($this->once())
            ->method('find')
            ->with($cardToken)
            ->willReturnSelf();
        $result = $this->model->storedCard($cardToken);
        $this->assertEquals(true, is_object($result));
    }

    public function testStoredCardException()
    {
        $exception = new \Braintree_Exception();
        $cardToken = 1;
        $this->braintreeCreditCardMock->expects($this->once())
            ->method('find')
            ->with($cardToken)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->model->storedCard($cardToken);
    }

    public function testDeleteCard()
    {
        $cardToken = 1;
        $this->braintreeCreditCardMock->expects($this->once())
            ->method('delete')
            ->with($cardToken)
            ->willReturnSelf();
        $result = $this->model->deleteCard($cardToken);
        $this->assertEquals(true, is_object($result));
    }

    public function testDeleteException()
    {
        $exception = new \Braintree_Exception();
        $cardToken = 1;
        $this->braintreeCreditCardMock->expects($this->once())
            ->method('delete')
            ->with($cardToken)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $return = $this->model->deleteCard($cardToken);

        $this->assertEquals(false, $return);
    }

    public function testGeneratePaymentMethodToken()
    {
        $cardToken = 1;
        $nonce = 'nonce';
        $paymentMethodObj=json_decode(json_encode(
            [
                'success' => true,
                'paymentMethodNonce'=> [
                    'nonce' => $nonce,
                ],
            ]
        ));
        $this->braintreePaymentMethodMock->expects($this->once())
            ->method('createNonce')
            ->with($cardToken)
            ->willReturn($paymentMethodObj);
        $result = $this->model->generatePaymentMethodToken($cardToken);
        $this->assertEquals($nonce, $result);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage exception
     */
    public function testGeneratePaymentMethodTokenException()
    {
        $cardToken = 1;
        $paymentMethodObj=json_decode(json_encode(
            [
                'success' => false,
            ]
        ));

        $this->braintreePaymentMethodMock->expects($this->once())
            ->method('createNonce')
            ->with($cardToken)
            ->willReturn($paymentMethodObj);

        $this->errorHelperMock->expects($this->once())
            ->method('parseBraintreeError')
            ->willReturn(new \Magento\Framework\Phrase('exception'));

        $this->model->generatePaymentMethodToken($cardToken);
    }

    /**
     * @param array $creditCardsArray
     * @param boolean $useVault
     * @param stdClass $braintreeCustomerObject
     * @dataProvider dataProviderCurrentCustomerStoredCards
     */
    public function testCurrentCustomerStoredCards(
        $creditCardsArray = [],
        $useVault = false,
        $braintreeCustomerObject = null
    ) {
        $customerId = 1;
        $this->configMock->expects($this->once())
            ->method('useVault')
            ->willReturn($useVault);

        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->customerFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);

        $this->helperMock->expects($this->any())
            ->method('generateCustomerId')
            ->willReturn($customerId);

        $this->customerMock->expects($this->any())
            ->method('load')
            ->willReturn($this->customerMock);


        if ($braintreeCustomerObject != null) {
            $this->braintreeCustomerMock->expects($this->any())
                ->method('find')
                ->with($customerId)
                ->willReturn($braintreeCustomerObject);
        } else {
            $this->braintreeCustomerMock->expects($this->any())
                ->method('find')
                ->with($customerId)
                ->willThrowException(new \Braintree_Exception());
        }

        $result = $this->model->currentCustomerStoredCards();

        $this->assertEquals($creditCardsArray, $result);
    }

    /**
     * @return array
     */
    public function dataProviderCurrentCustomerStoredCards()
    {
        $creditCardsArray = [
            '4111411141114111',
            '4111411141114111',
            '4111411141114111',
        ];

        return [
            [
                'creditCardsArray' => $creditCardsArray,
                'useVault' => true,
                'braintreeCustomerObject' => json_decode(json_encode(['creditCards' => $creditCardsArray])),
            ],
            [
                'creditCardsArray' => [],
                'useVault' => true,
                'braintreeCustomerObject' => null,
            ],
            [
                'creditCardsArray' => [],
                'useVault' => false,
                'braintreeCustomerObject' => null,
            ],
        ];
    }

    /**
     * @param string $params
     * @dataProvider dataProcessNonce
     */
    public function testProcessNonceException($params = null, $exceptionMessage = null)
    {
        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($params['customerId']);

        $countryCollectionMock = $this->getMockBuilder('\Magento\Directory\Model\ResourceModel\Country\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $countryCollectionMock->expects($this->any())
            ->method('addCountryCodeFilter')
            ->willReturn($countryCollectionMock);

        $this->countryFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($countryCollectionMock);

        $this->configMock->expects($this->any())
            ->method('canUseForCountry')
            ->willReturn($params['canUseForCountry']);

        $this->configMock->expects($this->any())
            ->method('canUseCcTypeForCountry')
            ->willReturn($params['canUseCcTypeForCountry']);


        $this->helperMock->expects($this->any())
            ->method('generateCustomerId')
            ->willReturn($params['customerId']);

        $this->customerMock->expects($this->any())
            ->method('load')
            ->willReturn($this->customerMock);

        $this->customerFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);

        if (is_object($params['paymentMethodObj'])) {
            if (!$params['optionsArray']['update']) {
                $this->braintreePaymentMethodMock->expects($this->once())
                    ->method('create')
                    ->willReturn($params['paymentMethodObj']);
            } else {
                $this->braintreePaymentMethodMock->expects($this->once())
                    ->method('update')
                    ->willReturn($params['paymentMethodObj']);
            }

            if (!$params['paymentMethodObj']->success) {
                $this->errorHelperMock->expects($this->once())
                    ->method('parseBraintreeError')
                    ->willReturn(new \Magento\Framework\Phrase($exceptionMessage));
            } else {
                $this->errorHelperMock->expects($this->never())->method('parseBraintreeError');
            }
        }

        try {
            $this->model->processNonce($params['nonce'], $params['optionsArray'], $params['billingAddress']);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->assertEquals($exceptionMessage, $e->getMessage());
        }

    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProcessNonce()
    {
        $optionsArray = [
            'default' => true,
            'token' => "",
            'update' => false,
            'ccType' => 'VI',
            'device_data' => "",
        ];
        $billingAddress = [
            "firstName" => 'firstName',
            "lastName" => 'lastName',
            "company" => '',
            "streetAddress" => '130 Street',
            "extendedAddress" => 'apt 114',
            "locality" => 'Austin',
            "region" => 'TX',
            "postalCode" => '78232',
            "countryCodeAlpha2" => 'US',
        ];

        return [
            [
                'params' => [
                    'customerId' => false,
                    'nonce' => null,
                    'optionsArray' => null,
                    'billingAddress' => null,
                    'canUseForCountry' => false,
                    'canUseCcTypeForCountry' => false,
                    'paymentMethodObj' => false,
                ],
                'exceptionMessage' => 'Invalid Customer ID provided'
            ],
            [
                'params' => [
                    'customerId' => '1',
                    'nonce' => 'nonce',
                    'optionsArray' => $optionsArray,
                    'billingAddress' => $billingAddress,
                    'canUseForCountry' => false,
                    'canUseCcTypeForCountry' => false,
                    'paymentMethodObj' => false,
                ],
                'exceptionMessage' => 'Selected payment type is not allowed for billing country.'
            ],
            [
                'params' => [
                    'customerId' => '1',
                    'nonce' => 'nonce',
                    'optionsArray' => $optionsArray,
                    'billingAddress' => $billingAddress,
                    'canUseForCountry' => true,
                    'canUseCcTypeForCountry' => new \Magento\Framework\Phrase(
                        'Credit card type is not allowed for your country.'
                    ),
                    'paymentMethodObj' => false,
                ],
                'exceptionMessage' => 'Credit card type is not allowed for your country.'
            ],
            [
                'params' => [
                    'customerId' => '1',
                    'nonce' => 'nonce',
                    'optionsArray' => $optionsArray,
                    'billingAddress' => $billingAddress,
                    'canUseForCountry' => true,
                    'canUseCcTypeForCountry' => false,
                    'paymentMethodObj' => json_decode(json_encode(['success' => false]))
                ],
                'exceptionMessage' => 'Braintree api error'
            ],
            [
                'params' => [
                    'customerId' => '1',
                    'nonce' => 'nonce',
                    'optionsArray' => $optionsArray,
                    'billingAddress' => $billingAddress,
                    'canUseForCountry' => true,
                    'canUseCcTypeForCountry' => false,
                    'paymentMethodObj' => json_decode(json_encode(['success' => true]))
                ],
                'exceptionMessage' => 'Braintree api error'
            ],
            [
                'params' => [
                    'customerId' => '1',
                    'nonce' => 'nonce',
                    'optionsArray' => [
                        'default' => true,
                        'token' => "",
                        'update' => 'true',
                        'ccType' => 'VI',
                        'device_data' => "",
                    ],
                    'billingAddress' => $billingAddress,
                    'canUseForCountry' => true,
                    'canUseCcTypeForCountry' => false,
                    'paymentMethodObj' => json_decode(json_encode(['success' => true]))
                ],
                'exceptionMessage' => 'Braintree api error'
            ],
        ];
    }
}
