<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Controller\Adminhtml\System\Currencysymbol;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class SaveTest
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Save
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
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\CurrencySymbol\Model\System\Currencysymbol|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencySymbolMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->requestMock->expects($this->any())->method('isPost')->willReturn(true);
        $this->helperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['setRedirect', 'sendResponse'])
            ->getMockForAbstractClass();
        $this->currencySymbolMock = $this->getMockBuilder(\Magento\CurrencySymbol\Model\System\Currencysymbol::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterManagerMock = $this->getMockBuilder(\Magento\Framework\Filter\FilterManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['stripTags'])
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->action = $objectManager->getObject(
            \Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Save::class,
            [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'objectManager' => $this->objectManagerMock,
                'redirect' => $this->redirectMock,
                'helper' => $this->helperMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );
    }

    public function testExecute()
    {
        $firstElement = 'firstElement';
        $symbolsDataArray = [$firstElement];
        $redirectUrl = 'redirectUrl';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('custom_currency_symbol')
            ->willReturn($symbolsDataArray);

        $this->helperMock->expects($this->once())->method('getUrl')->with('*');
        $this->redirectMock->expects($this->once())->method('getRedirectUrl')->willReturn($redirectUrl);

        $this->currencySymbolMock->expects($this->once())->method('setCurrencySymbolsData')->with($symbolsDataArray);
        $this->responseMock->expects($this->once())->method('setRedirect');

        $this->filterManagerMock->expects($this->once())
            ->method('stripTags')
            ->with($firstElement)
            ->willReturn($firstElement);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\CurrencySymbol\Model\System\Currencysymbol::class)
            ->willReturn($this->currencySymbolMock);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Filter\FilterManager::class)
            ->willReturn($this->filterManagerMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You applied the custom currency symbols.'));

        $this->action->execute();
    }
}
