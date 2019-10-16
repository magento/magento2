<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Controller\Adminhtml\System\Currency;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class SaveRatesTest
 */
class SaveRatesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\SaveRates
     */
    protected $action;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     *
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );

        $this->action = $objectManager->getObject(
            \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\SaveRates::class,
            [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
            ]
        );
    }

    /**
     *
     */
    public function testWithNullRateExecute()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('rate')
            ->willReturn(null);

        $this->responseMock->expects($this->once())->method('setRedirect');

        $this->action->execute();
    }
}
