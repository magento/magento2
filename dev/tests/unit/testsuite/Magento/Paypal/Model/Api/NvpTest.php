<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Paypal\Model\Api;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class NvpTest extends \PHPUnit_Framework_TestCase
{
    /** @var Nvp */
    protected $model;

    /** @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerAddressHelper;

    /** @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $resolver;

    /** @var \Magento\Directory\Model\RegionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $regionFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $adapterFactory;

    /** @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $countryFactory;

    /** @var \Magento\Paypal\Model\Api\ProcessableException|\PHPUnit_Framework_MockObject_MockObject */
    protected $processableException;

    /** @var \Magento\Framework\Model\Exception|\PHPUnit_Framework_MockObject_MockObject */
    protected $exception;

    /** @var \Magento\Framework\HTTP\Adapter\Curl|\PHPUnit_Framework_MockObject_MockObject */
    protected $curl;

    /** @var \Magento\Paypal\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    protected function setUp()
    {
        $this->customerAddressHelper = $this->getMock('Magento\Customer\Helper\Address', [], [], '', false);
        $this->logger = $this->getMock('Magento\Framework\Logger', [], [], '', false);
        $this->resolver = $this->getMock('Magento\Framework\Locale\ResolverInterface');
        $this->regionFactory = $this->getMock('Magento\Directory\Model\RegionFactory', [], [], '', false);
        $this->adapterFactory = $this->getMock('Magento\Framework\Logger\AdapterFactory');
        $this->countryFactory = $this->getMock('Magento\Directory\Model\CountryFactory', [], [], '', false);
        $processableExceptionFactory = $this->getMock(
            'Magento\Paypal\Model\Api\ProcessableExceptionFactory',
            ['create']
        );
        $processableExceptionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function ($arguments) {
                $this->processableException = $this->getMock(
                    'Magento\Paypal\Model\Api\ProcessableException',
                    null,
                    [$arguments['message'], $arguments['code']]
                );
                return $this->processableException;
            }));
        $exceptionFactory = $this->getMock('Magento\Framework\Model\ExceptionFactory', ['create']);
        $exceptionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function ($arguments) {
                $this->exception = $this->getMock(
                    'Magento\Paypal\Model\Api\ProcessableException',
                    null,
                    [$arguments['message'], $arguments['code']]
                );
                return $this->exception;
            }));
        $this->curl = $this->getMock('Magento\Framework\HTTP\Adapter\Curl', [], [], '', false);
        $curlFactory = $this->getMock('Magento\Framework\HTTP\Adapter\CurlFactory', ['create']);
        $curlFactory->expects($this->any())->method('create')->will($this->returnValue($this->curl));
        $this->config = $this->getMock('Magento\Paypal\Model\Config', [], [], '', false);

        $helper = new ObjectManagerHelper($this);
        $this->model = $helper->getObject(
            'Magento\Paypal\Model\Api\Nvp',
            [
                'customerAddress' => $this->customerAddressHelper,
                'logger' => $this->logger,
                'localeResolver' => $this->resolver,
                'regionFactory' => $this->regionFactory,
                'logAdapterFactory' => $this->adapterFactory,
                'countryFactory' => $this->countryFactory,
                'processableExceptionFactory' => $processableExceptionFactory,
                'frameworkExceptionFactory' => $exceptionFactory,
                'curlFactory' => $curlFactory,
            ]
        );
        $this->model->setConfigObject($this->config);
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
        $this->model->call('some method', ['data' => 'some data']);
    }

    public function callDataProvider()
    {
        return [
            ['', [], null],
            [
                "\r\n" . 'ACK=Failure&L_ERRORCODE0=10417&L_SHORTMESSAGE0=Message.&L_LONGMESSAGE0=Long%20Message.',
                [],
                'Magento\Framework\Model\Exception',
                'PayPal gateway has rejected request. Long Message (#10417: Message).',
                0
            ],
            [
                "\r\n" . 'ACK=Failure&L_ERRORCODE0=10417&L_SHORTMESSAGE0=Message.&L_LONGMESSAGE0=Long%20Message.',
                [10417, 10422],
                'Magento\Paypal\Model\Api\ProcessableException',
                'PayPal gateway has rejected request. Long Message (#10417: Message).',
                10417
            ],
            [
                "\r\n" . 'ACK[7]=Failure&L_ERRORCODE0[5]=10417'
                    . '&L_SHORTMESSAGE0[8]=Message.&L_LONGMESSAGE0[15]=Long%20Message.',
                [10417, 10422],
                'Magento\Paypal\Model\Api\ProcessableException',
                'PayPal gateway has rejected request. Long Message (#10417: Message).',
                10417
            ],
            [
                "\r\n" . 'ACK[7]=Failure&L_ERRORCODE0[5]=10417&L_SHORTMESSAGE0[8]=Message.',
                [10417, 10422],
                'Magento\Paypal\Model\Api\ProcessableException',
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
            ));
        $this->model->callGetExpressCheckoutDetails();
        $this->assertEquals('Ship To Name', $this->model->getExportedShippingAddress()->getData('firstname'));
    }
}
