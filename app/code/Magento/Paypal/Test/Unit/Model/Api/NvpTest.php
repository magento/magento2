<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Api;

use Magento\Customer\Helper\Address;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\LocalizedExceptionFactory;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Api\ProcessableException;
use Magento\Paypal\Model\Api\ProcessableExceptionFactory;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Info;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NvpTest extends TestCase
{
    /** @var Nvp */
    protected $model;

    /** @var Address|MockObject */
    protected $customerAddressHelper;

    /** @var LoggerInterface|MockObject */
    protected $logger;

    /** @var ResolverInterface|MockObject */
    protected $resolver;

    /** @var RegionFactory|MockObject */
    protected $regionFactory;

    /** @var CountryFactory|MockObject */
    protected $countryFactory;

    /** @var ProcessableException|MockObject */
    protected $processableException;

    /** @var LocalizedException|MockObject */
    protected $exception;

    /** @var Curl|MockObject */
    protected $curl;

    /** @var Config|MockObject */
    protected $config;

    /** @var Logger|MockObject */
    protected $customLoggerMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->customerAddressHelper = $this->createMock(Address::class);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->customLoggerMock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(LoggerInterface::class)])
            ->setMethods(['debug'])
            ->getMock();
        $this->resolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->regionFactory = $this->createMock(RegionFactory::class);
        $this->countryFactory = $this->createMock(CountryFactory::class);
        $processableExceptionFactory = $this->createPartialMock(
            ProcessableExceptionFactory::class,
            ['create']
        );
        $processableExceptionFactory->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function ($arguments) {
                    $this->processableException = $this->getMockBuilder(
                        ProcessableException::class
                    )->setConstructorArgs([$arguments['phrase'], null, $arguments['code']])->getMock();
                    return $this->processableException;
                }
            );
        $exceptionFactory = $this->createPartialMock(
            LocalizedExceptionFactory::class,
            ['create']
        );
        $exceptionFactory->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function ($arguments) {
                    $this->exception = $this->getMockBuilder(LocalizedException::class)
                        ->setConstructorArgs([$arguments['phrase']])
                        ->getMock();
                    return $this->exception;
                }
            );
        $this->curl = $this->createMock(Curl::class);
        $curlFactory = $this->createPartialMock(CurlFactory::class, ['create']);
        $curlFactory->expects($this->any())->method('create')->willReturn($this->curl);
        $this->config = $this->createMock(Config::class);

        $helper = new ObjectManagerHelper($this);
        $this->model = $helper->getObject(
            Nvp::class,
            [
                'customerAddress' => $this->customerAddressHelper,
                'logger' => $this->logger,
                'customLogger' => $this->customLoggerMock,
                'localeResolver' => $this->resolver,
                'regionFactory' => $this->regionFactory,
                'countryFactory' => $this->countryFactory,
                'processableExceptionFactory' => $processableExceptionFactory,
                'frameworkExceptionFactory' => $exceptionFactory,
                'curlFactory' => $curlFactory,
            ]
        );
        $this->model->setConfigObject($this->config);
    }

    /**
     * @param Nvp $nvpObject
     * @param string $property
     * @return mixed
     */
    protected function _invokeNvpProperty(Nvp $nvpObject, $property)
    {
        $object = new \ReflectionClass($nvpObject);
        $property = $object->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($nvpObject);
    }

    /**
     * @param string $response
     * @param array $processableErrors
     * @param null|string $exception
     * @param string $exceptionMessage
     * @param null|int $exceptionCode
     * @dataProvider callDataProvider
     */
    public function testCall($response, $processableErrors, $exception, $exceptionMessage = '', $exceptionCode = null)
    {
        if (isset($exception)) {
            $this->expectException($exception);
            $this->expectExceptionMessage($exceptionMessage);
            $this->expectExceptionCode($exceptionCode);
        }
        $this->curl->expects($this->once())
            ->method('read')
            ->willReturn($response);
        $this->model->setProcessableErrors($processableErrors);
        $this->customLoggerMock->expects($this->once())
            ->method('debug');
        $this->model->call('some method', ['data' => 'some data']);
    }

    /**
     * @return array
     */
    public function callDataProvider()
    {
        return [
            ['', [], null],
            [
                "\r\n" . 'ACK=Failure&L_ERRORCODE0=10417&L_SHORTMESSAGE0=Message.&L_LONGMESSAGE0=Long%20Message.',
                [],
                LocalizedException::class,
                'PayPal gateway has rejected request. Long Message (#10417: Message).',
                0
            ],
            [
                "\r\n" . 'ACK=Failure&L_ERRORCODE0=10417&L_SHORTMESSAGE0=Message.&L_LONGMESSAGE0=Long%20Message.',
                [10417, 10422],
                ProcessableException::class,
                'PayPal gateway has rejected request. Long Message (#10417: Message).',
                10417
            ],
            [
                "\r\n" . 'ACK[7]=Failure&L_ERRORCODE0[5]=10417'
                    . '&L_SHORTMESSAGE0[8]=Message.&L_LONGMESSAGE0[15]=Long%20Message.',
                [10417, 10422],
                ProcessableException::class,
                'PayPal gateway has rejected request. Long Message (#10417: Message).',
                10417
            ],
            [
                "\r\n" . 'ACK[7]=Failure&L_ERRORCODE0[5]=10417&L_SHORTMESSAGE0[8]=Message.&L_LONGMESSAGE0[15]=',
                [10417, 10422],
                ProcessableException::class,
                'PayPal gateway has rejected request. #10417: Message.',
                10417
            ],
        ];
    }

    /**
     * Test getting of the ExpressCheckout details
     *
     * @param $input
     * @param $expected
     * @dataProvider callGetExpressCheckoutDetailsDataProvider
     */
    public function testCallGetExpressCheckoutDetails($input, $expected)
    {
        $this->curl->expects($this->once())
            ->method('read')
            ->willReturn($input);
        $this->model->callGetExpressCheckoutDetails();
        $address = $this->model->getExportedShippingAddress();
        $this->assertEquals($expected['firstName'], $address->getData('firstname'));
        $this->assertEquals($expected['lastName'], $address->getData('lastname'));
        $this->assertEquals($expected['street'], $address->getStreet());
        $this->assertEquals($expected['company'], $address->getCompany());
        $this->assertEquals($expected['city'], $address->getCity());
        $this->assertEquals($expected['telephone'], $address->getTelephone());
        $this->assertEquals($expected['region'], $address->getRegion());
    }

    /**
     * Data Provider
     *
     * @return array
     */
    public function callGetExpressCheckoutDetailsDataProvider()
    {
        return [
            [
                "\r\n" . 'ACK=Success&SHIPTONAME=Jane%20Doe'
                . '&SHIPTOSTREET=testStreet'
                . '&SHIPTOSTREET2=testApartment'
                . '&BUSINESS=testCompany'
                . '&SHIPTOCITY=testCity'
                . '&PHONENUM=223322'
                . '&STATE=testSTATE',
                [
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'street' => 'testStreet' . "\n" . 'testApartment',
                    'company' => 'testCompany',
                    'city' => 'testCity',
                    'telephone' => '223322',
                    'region' => 'testSTATE',
                ]
            ]
        ];
    }

    /**
     * Tests that callDoReauthorization method is called without errors and
     * needed data is imported from response.
     */
    public function testCallDoReauthorization()
    {
        $authorizationId = 555;
        $paymentStatus = 'Completed';
        $pendingReason = 'none';
        $protectionEligibility = 'Eligible';
        $protectionEligibilityType = 'ItemNotReceivedEligible';

        $this->curl->expects($this->once())
            ->method('read')
            ->willReturn(
                "\r\n" . 'ACK=Success'
                . '&AUTHORIZATIONID=' . $authorizationId
                . '&PAYMENTSTATUS=' . $paymentStatus
                . '&PENDINGREASON=' . $pendingReason
                . '&PROTECTIONELIGIBILITY=' . $protectionEligibility
                . '&PROTECTIONELIGIBILITYTYPE=' . $protectionEligibilityType
            );

        $this->model->callDoReauthorization();

        $expectedImportedData = [
            'authorization_id' => $authorizationId,
            'payment_status' => Info::PAYMENTSTATUS_COMPLETED,
            'pending_reason' => $pendingReason,
            'protection_eligibility' => $protectionEligibility
        ];

        $this->assertNotContains($protectionEligibilityType, $this->model->getData());
        $this->assertEquals($expectedImportedData, $this->model->getData());
    }

    /**
     * Test replace keys for debug data
     */
    public function testGetDebugReplacePrivateDataKeys()
    {
        $debugReplacePrivateDataKeys = $this->_invokeNvpProperty($this->model, '_debugReplacePrivateDataKeys');
        $this->assertEquals($debugReplacePrivateDataKeys, $this->model->getDebugReplacePrivateDataKeys());
    }

    /**
     * Tests case if obtained response with code 10415 'Transaction has already
     * been completed for this token'. It must throw the ProcessableException.
     */
    public function testCallTransactionHasBeenCompleted()
    {
        $response =    "\r\n" . 'ACK[7]=Failure&L_ERRORCODE0[5]=10415'
            . '&L_SHORTMESSAGE0[8]=Message.&L_LONGMESSAGE0[15]=Long%20Message.';
        $processableErrors =[10415];
        $this->curl->expects($this->once())
            ->method('read')
            ->willReturn($response);
        $this->model->setProcessableErrors($processableErrors);

        $this->expectExceptionMessageMatches('/PayPal gateway has rejected request/');
        $this->expectException(ProcessableException::class);

        $this->model->call('DoExpressCheckout', ['data' => 'some data']);
    }
}
