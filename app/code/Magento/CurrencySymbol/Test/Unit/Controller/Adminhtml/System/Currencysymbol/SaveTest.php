<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Controller\Adminhtml\System\Currencysymbol;

use Magento\Backend\Helper\Data;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\CurrencySymbol\Controller\Adminhtml\System\Currencysymbol\Save;
use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\CurrencySymbol\Model\System\CurrencysymbolFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test ot to save currency symbol controller
 */
class SaveTest extends TestCase
{
    /**
     * @var Save
     */
    protected $action;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var RedirectInterface|MockObject
     */
    protected $redirectMock;

    /**
     * @var Data|MockObject
     */
    protected $helperMock;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManager;

    /**
     * @var CurrencysymbolFactory|MockObject
     */
    private $currencySymbolFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->redirectMock = $this->createMock(RedirectInterface::class);
        $this->responseMock = $this->createPartialMock(
            ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $this->filterManager = $this->createPartialMock(
            FilterManager::class,
            ['stripTags']
        );
        $this->currencySymbolFactory = $this->createMock(CurrencysymbolFactory::class);

        $this->action = $objectManager->getObject(
            Save::class,
            [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'redirect' => $this->redirectMock,
                'helper' => $this->helperMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'filterManager' => $this->filterManager,
                'currencySymbolFactory' => $this->currencySymbolFactory,
            ]
        );
    }

    /**
     * Test to Save custom Currency symbol
     */
    public function testExecute()
    {
        $firstElement = 'firstElement';
        $symbolsDataArray = [$firstElement];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('custom_currency_symbol')
            ->willReturn($symbolsDataArray);

        $currencySymbol = $this->createMock(Currencysymbol::class);
        $currencySymbol->expects($this->once())->method('setCurrencySymbolsData')->with($symbolsDataArray);
        $this->currencySymbolFactory->method('create')->willReturn($currencySymbol);
        $this->filterManager->expects($this->once())
            ->method('stripTags')
            ->with($firstElement)
            ->willReturn($firstElement);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You applied the custom currency symbols.'));

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->once())->method('setPath')->with('*')->willReturnSelf();
        $this->resultRedirectFactory->method('create')->willReturn($redirect);

        $this->assertEquals($redirect, $this->action->execute());
    }
}
