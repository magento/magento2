<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Model\PayflowConfig;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Paypal\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Class PayflowConfigTest
 */
class PayflowConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodInterfaceMock;

    /**
     * @var PayflowConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->setMethods(['getValue', 'isSetFlag'])
            ->getMockForAbstractClass();
        $this->methodInterfaceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();

        $om = new ObjectManager($this);
        $this->config = $om->getObject(
            \Magento\Paypal\Model\PayflowConfig::class,
            [
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * @param string $paymentAction
     * @param string|null $expectedValue
     *
     * @dataProvider getTrxTypeDataProvider
     */
    public function testGetTrxType($paymentAction, $expectedValue)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn($paymentAction);

        $this->assertEquals($expectedValue, $this->config->getTrxType());
    }

    /**
     * @return array
     */
    public function getTrxTypeDataProvider()
    {
        return [
            [PayflowConfig::PAYMENT_ACTION_AUTH, PayflowConfig::TRXTYPE_AUTH_ONLY],
            [PayflowConfig::PAYMENT_ACTION_SALE, PayflowConfig::TRXTYPE_SALE],
            ['other', null],
        ];
    }

    /**
     * @param string $paymentAction
     * @param string|null $expectedValue
     *
     * @dataProvider getPaymentActionDataProvider
     */
    public function testGetPaymentAction($paymentAction, $expectedValue)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn($paymentAction);

        $this->assertEquals($expectedValue, $this->config->getPaymentAction());
    }

    /**
     * @return array
     */
    public function getPaymentActionDataProvider()
    {
        return [
            [PayflowConfig::PAYMENT_ACTION_AUTH, AbstractMethod::ACTION_AUTHORIZE],
            [PayflowConfig::PAYMENT_ACTION_SALE, AbstractMethod::ACTION_AUTHORIZE_CAPTURE],
            ['other', null],
        ];
    }

    public function testGetTransactionUrlWithTestModeOn()
    {
        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');
        $this->methodInterfaceMock->expects($this->once())
            ->method('getConfigData')
            ->with('transaction_url_test_mode')
            ->willReturn('transaction_url_test_mode');

        $this->config->setMethodInstance($this->methodInterfaceMock);
        $this->assertEquals('transaction_url_test_mode', $this->config->getTransactionUrl(1));
    }

    public function testGetTransactionUrlWithTestModeOff()
    {
        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');
        $this->methodInterfaceMock->expects($this->once())
            ->method('getConfigData')
            ->with('transaction_url')
            ->willReturn('transaction_url');

        $this->config->setMethodInstance($this->methodInterfaceMock);
        $this->assertEquals('transaction_url', $this->config->getTransactionUrl(0));
    }

    public function testGetTransactionUrlWithTestModeEmptyAndSandboxOn()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->methodInterfaceMock->expects($this->once())
            ->method('getConfigData')
            ->with('transaction_url_test_mode')
            ->willReturn('transaction_url_test_mode');

        $this->config->setMethodInstance($this->methodInterfaceMock);
        $this->assertEquals('transaction_url_test_mode', $this->config->getTransactionUrl());
    }

    public function testGetTransactionUrlWithTestModeEmptyAndSandboxOff()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(0);
        $this->methodInterfaceMock->expects($this->once())
            ->method('getConfigData')
            ->with('transaction_url')
            ->willReturn('transaction_url');

        $this->config->setMethodInstance($this->methodInterfaceMock);
        $this->assertEquals('transaction_url', $this->config->getTransactionUrl());
    }

    /**
     * @param array $expectsMethods
     * @param string $currentMethod
     * @param bool $result
     *
     * @dataProvider dataProviderForTestIsMethodActive
     */
    public function testIsMethodActive(array $expectsMethods, $currentMethod, $result)
    {
        $this->config->setStoreId(5);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with('paypal/general/merchant_country')
            ->will($this->returnValue('US'));

        $i = 0;
        foreach ($expectsMethods as $method => $isActive) {
            $this->scopeConfigMock->expects($this->at($i++))
                ->method('isSetFlag')
                ->with(
                    "payment/{$method}/active",
                    ScopeInterface::SCOPE_STORE,
                    5
                )->willReturn($isActive);
        }

        $this->assertEquals($result, $this->config->isMethodActive($currentMethod));
    }

    /**
     * @return array
     */
    public function dataProviderForTestIsMethodActive()
    {
        return [
            [
                'expectsMethods' => [
                    Config::METHOD_PAYMENT_PRO => 0,
                    Config::METHOD_PAYFLOWPRO => 1,
                ],
                'currentMethod' => Config::METHOD_PAYMENT_PRO,
                'result' => true,
            ],
            [
                'expectsMethods' => [
                    Config::METHOD_PAYMENT_PRO => 1
                ],
                'currentMethod' => Config::METHOD_PAYFLOWPRO,
                'result' => true,
            ],
            [
                'expectsMethods' => [
                    Config::METHOD_PAYMENT_PRO => 0,
                    Config::METHOD_PAYFLOWPRO => 0,
                ],
                'currentMethod' => 777,
                'result' => false,
            ],
        ];
    }
}
