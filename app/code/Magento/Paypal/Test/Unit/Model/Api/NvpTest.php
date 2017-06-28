<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Test\Unit\Model\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Paypal\Model\Info;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NvpTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Model\Api\Nvp */
    protected $model;

    /** @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerAddressHelper;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $resolver;

    /** @var \Magento\Directory\Model\RegionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $regionFactory;

    /** @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $countryFactory;

    /** @var \Magento\Paypal\Model\Api\ProcessableException|\PHPUnit_Framework_MockObject_MockObject */
    protected $processableException;

    /** @var \Magento\Framework\Exception\LocalizedException|\PHPUnit_Framework_MockObject_MockObject */
    protected $exception;

    /** @var \Magento\Framework\HTTP\Adapter\Curl|\PHPUnit_Framework_MockObject_MockObject */
    protected $curl;

    /** @var \Magento\Paypal\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\Payment\Model\Method\Logger|\PHPUnit_Framework_MockObject_MockObject */
    protected $customLoggerMock;

    protected function setUp()
    {
        $this->customerAddressHelper = $this->getMock(\Magento\Customer\Helper\Address::class, [], [], '', false);
        $this->logger = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->customLoggerMock = $this->getMockBuilder(\Magento\Payment\Model\Method\Logger::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class)])
            ->setMethods(['debug'])
            ->getMock();
        $this->resolver = $this->getMock(\Magento\Framework\Locale\ResolverInterface::class);
        $this->regionFactory = $this->getMock(\Magento\Directory\Model\RegionFactory::class, [], [], '', false);
        $this->countryFactory = $this->getMock(\Magento\Directory\Model\CountryFactory::class, [], [], '', false);
        $processableExceptionFactory = $this->getMock(
            \Magento\Paypal\Model\Api\ProcessableExceptionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $processableExceptionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function ($arguments) {
                $this->processableException = $this->getMock(
                    \Magento\Paypal\Model\Api\ProcessableException::class,
                    null,
                    [$arguments['phrase'], null, $arguments['code']]
                );
                return $this->processableException;
            }));
        $exceptionFactory = $this->getMock(
            \Magento\Framework\Exception\LocalizedExceptionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $exceptionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function ($arguments) {
                $this->exception = $this->getMock(
                    \Magento\Framework\Exception\LocalizedException::class,
                    null,
                    [$arguments['phrase']]
                );
                return $this->exception;
            }));
        $this->curl = $this->getMock(\Magento\Framework\HTTP\Adapter\Curl::class, [], [], '', false);
        $curlFactory = $this->getMock(\Magento\Framework\HTTP\Adapter\CurlFactory::class, ['create'], [], '', false);
        $curlFactory->expects($this->any())->method('create')->will($this->returnValue($this->curl));
        $this->config = $this->getMock(\Magento\Paypal\Model\Config::class, [], [], '', false);

        $helper = new ObjectManagerHelper($this);
        $this->model = $helper->getObject(
            \Magento\Paypal\Model\Api\Nvp::class,
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
     * @param \Magento\Paypal\Model\Api\Nvp $nvpObject
     * @param string $property
     * @return mixed
     */
    protected function _invokeNvpProperty(\Magento\Paypal\Model\Api\Nvp $nvpObject, $property)
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
            $this->setExpectedException($exception, $exceptionMessage, $exceptionCode);
        }
        $this->curl->expects($this->once())
            ->method('read')
            ->will($this->returnValue($response));
        $this->model->setProcessableErrors($processableErrors);
        $this->customLoggerMock->expects($this->once())
            ->method('debug');
        $this->model->call('some method', ['data' => 'some data']);
    }

    public function callDataProvider()
    {
        return [
            ['', [], null],
            [
                "\r\n" . 'ACK=Failure&L_ERRORCODE0=10417&L_SHORTMESSAGE0=Message.&L_LONGMESSAGE0=Long%20Message.',
                [],
                \Magento\Framework\Exception\LocalizedException::class,
                'PayPal gateway has rejected request. Long Message (#10417: Message).',
                0
            ],
            [
                "\r\n" . 'ACK=Failure&L_ERRORCODE0=10417&L_SHORTMESSAGE0=Message.&L_LONGMESSAGE0=Long%20Message.',
                [10417, 10422],
                \Magento\Paypal\Model\Api\ProcessableException::class,
                'PayPal gateway has rejected request. Long Message (#10417: Message).',
                10417
            ],
            [
                "\r\n" . 'ACK[7]=Failure&L_ERRORCODE0[5]=10417'
                    . '&L_SHORTMESSAGE0[8]=Message.&L_LONGMESSAGE0[15]=Long%20Message.',
                [10417, 10422],
                \Magento\Paypal\Model\Api\ProcessableException::class,
                'PayPal gateway has rejected request. Long Message (#10417: Message).',
                10417
            ],
            [
                "\r\n" . 'ACK[7]=Failure&L_ERRORCODE0[5]=10417&L_SHORTMESSAGE0[8]=Message.',
                [10417, 10422],
                \Magento\Paypal\Model\Api\ProcessableException::class,
                'PayPal gateway has rejected request. #10417: Message.',
                10417
            ],
        ];
    }

    public function testCallGetExpressCheckoutDetails()
    {
        $this->curl->expects($this->once())
            ->method('read')
            ->will($this->returnValue(
                "\r\n" . 'ACK=Success&SHIPTONAME=Ship%20To%20Name'
                . '&SHIPTOSTREET=testStreet'
                . '&SHIPTOSTREET2=testApartment'
                . '&BUSINESS=testCompany'
                . '&SHIPTOCITY=testCity'
                . '&PHONENUM=223322'
                . '&STATE=testSTATE'
            ));
        $this->model->callGetExpressCheckoutDetails();
        $address = $this->model->getExportedShippingAddress();
        $this->assertEquals('Ship To Name', $address->getData('firstname'));
        $this->assertEquals(implode("\n", ['testStreet','testApartment']), $address->getStreet());
        $this->assertEquals('testCompany', $address->getCompany());
        $this->assertEquals('testCity', $address->getCity());
        $this->assertEquals('223322', $address->getTelephone());
        $this->assertEquals('testSTATE', $address->getRegion());
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

    public function testGetDebugReplacePrivateDataKeys()
    {
        $debugReplacePrivateDataKeys = $this->_invokeNvpProperty($this->model, '_debugReplacePrivateDataKeys');
        $this->assertEquals($debugReplacePrivateDataKeys, $this->model->getDebugReplacePrivateDataKeys());
    }
}
