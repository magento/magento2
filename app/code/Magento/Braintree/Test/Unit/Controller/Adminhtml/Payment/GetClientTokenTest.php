<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Braintree\Controller\Adminhtml\Payment\GetClientToken;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Controller\Adminhtml\Payment\GetClientToken
 */
class GetClientTokenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetClientToken
     */
    private $action;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var BraintreeAdapterFactory|MockObject
     */
    private $adapterFactoryMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteSessionMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    protected function setUp()
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResultFactory'])
            ->getMock();
        $context->expects(static::any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMerchantAccountId'])
            ->getMock();
        $this->adapterFactoryMock = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quoteSessionMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();

        $managerHelper = new ObjectManager($this);
        $this->action = $managerHelper->getObject(GetClientToken::class, [
            'context' => $context,
            'config' => $this->configMock,
            'adapterFactory' => $this->adapterFactoryMock,
            'quoteSession' => $this->quoteSessionMock,
        ]);
    }

    public function testExecute()
    {
        $storeId = '1';
        $clientToken = 'client_token';
        $responseMock = $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['setHttpResponseCode', 'renderResult', 'setHeader', 'setData'])
            ->getMock();
        $responseMock->expects(static::once())
            ->method('setData')
            ->with(['clientToken' => $clientToken])
            ->willReturn($responseMock);
        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($responseMock);
        $this->quoteSessionMock->expects(static::once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->configMock->expects(static::once())
            ->method('getMerchantAccountId')
            ->with($storeId)
            ->willReturn(null);
        $adapterMock = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $adapterMock->expects(static::once())
            ->method('generate')
            ->willReturn($clientToken);
        $this->adapterFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($adapterMock);

        $this->action->execute();
    }
}
