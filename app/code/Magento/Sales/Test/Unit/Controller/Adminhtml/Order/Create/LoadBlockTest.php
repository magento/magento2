<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Catalog\Helper\Product;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\RegexValidator;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Controller\Adminhtml\Order\Create\LoadBlock;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order\Create\ValidateCoupon;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Unit test for LoadBlock controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class LoadBlockTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var LoadBlock
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var PageFactory|MockObject
     */
    private $resultPageFactory;

    /**
     * @var RawFactory|MockObject
     */
    private $resultRawFactory;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var Product|MockObject
     */
    private $productHelper;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var RegexValidator|MockObject
     */
    private $regexValidator;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var Page|MockObject
     */
    private $resultPage;

    /**
     * @var Layout|MockObject
     */
    private $layout;

    /**
     * @var ValidateCoupon|MockObject
     */
    private $validateCoupon;

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->request = $this->createPartialMock(Http::class, ['getPost', 'getPostValue', 'has', 'getParam']);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->resultPageFactory = $this->createMock(PageFactory::class);
        $this->resultRawFactory = $this->createMock(RawFactory::class);
        $this->resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $this->productHelper = $this->createMock(Product::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->regexValidator = $this->createMock(RegexValidator::class);
        $this->session = $this->createPartialMockWithReflection(
            Session::class,
            ['setUpdateResult', 'hasUpdateResult', 'getUpdateResult']
        );
        $this->resultPage = $this->createMock(Page::class);
        $this->layout = $this->createMock(Layout::class);
        $this->validateCoupon = $this->createMock(ValidateCoupon::class);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        // Mock event manager to prevent null errors
        $eventManager = $this->createMock(ManagerInterface::class);
        $eventManager->expects($this->any())
            ->method('dispatch')
            ->willReturn(true);
        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);

        // Mock message manager for exception handling
        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects($this->any())
            ->method('addErrorMessage')
            ->willReturnSelf();
        $messageManager->expects($this->any())
            ->method('addExceptionMessage')
            ->willReturnSelf();
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($messageManager);

        // ProductHelper needs to allow setSkipSaleableCheck method call
        $this->productHelper->expects($this->any())
            ->method('setSkipSaleableCheck')
            ->with(true)
            ->willReturnSelf();

        // Mock store for setCurrentStore calls
        $store = $this->createMock(Store::class);
        $this->storeManager->expects($this->any())
            ->method('setCurrentStore')
            ->willReturnSelf();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->resultPage->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);

        $resultForwardFactory = $this->createMock(ForwardFactory::class);

        // Mock ObjectManager to return dependencies when parent class requests them
        $quoteSession = $this->createMock(QuoteSession::class);
        $registry = $this->createMock(Registry::class);
        $store = $this->createMock(Store::class);
        $quote = $this->createMock(Quote::class);

        // Mock customer with addMethods since getCustomerGroupId may not exist
        $customer = $this->createPartialMockWithReflection(Customer::class, ['getCustomerGroupId']);

        // Mock registry methods
        $registry->expects($this->any())
            ->method('unregister')
            ->willReturnSelf();
        $registry->expects($this->any())
            ->method('register')
            ->willReturnSelf();

        // Mock store
        $store->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        // Mock customer
        $customer->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);

        // Mock shipping address
        $shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSameAsBilling'])
            ->getMock();
        $shippingAddress->expects($this->any())
            ->method('getSameAsBilling')
            ->willReturn(false);

        // Mock quote
        $quote->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $quote->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);

        // Mock quote session methods
        $quoteSession->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $quoteSession->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);

        $adminOrderCreate = $this->createPartialMockWithReflection(Create::class, ['setStoreId']);

        // AdminOrderCreate uses _coreRegistry and _session properties internally
        // Since we disabled the constructor, we need to inject them via reflection
        $reflection = new \ReflectionClass($adminOrderCreate);

        $registryProperty = $reflection->getProperty('_coreRegistry');
        $registryProperty->setAccessible(true);
        $registryProperty->setValue($adminOrderCreate, $registry);

        $sessionProperty = $reflection->getProperty('_session');
        $sessionProperty->setAccessible(true);
        $sessionProperty->setValue($adminOrderCreate, $quoteSession);

        // Mock adminOrderCreate methods that might be called
        $adminOrderCreate->expects($this->any())
            ->method('setStoreId')
            ->willReturnSelf();

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($className) use ($quoteSession, $adminOrderCreate, $registry) {
                switch ($className) {
                    case ValidateCoupon::class:
                        return $this->validateCoupon;
                    case Session::class:
                        return $this->session;
                    case QuoteSession::class:
                        return $quoteSession;
                    case Create::class:
                        return $adminOrderCreate;
                    case Registry::class:
                        return $registry;
                    default:
                        return $this->createMock($className);
                }
            });

        // Bootstrap ObjectManager with our mock so getInstance() returns it
        ObjectManager::setInstance($this->objectManager);

        // Now create LoadBlock - parent will use our mocked ObjectManager
        $this->controller = new LoadBlock(
            $this->context,
            $this->productHelper,
            $this->escaper,
            $this->resultPageFactory,
            $resultForwardFactory,
            $this->resultRawFactory,
            $this->storeManager,
            $this->regexValidator
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        // Clean up ObjectManager instance - create a fresh mock to pass to setInstance
        // (setInstance doesn't accept null, so we pass a new mock)
        try {
            $cleanObjectManager = $this->createMock(ObjectManagerInterface::class);
            ObjectManager::setInstance($cleanObjectManager);
        } catch (\Exception $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            // Ignore any errors during cleanup
        }
        parent::tearDown();
    }

    /**
     * Test that JSON response with as_js_varname stores compressed data to prevent session bloat
     *
     * This fix maintains the redirect pattern (for PAT compatibility) but compresses the session data
     * to reduce session bloat by ~90%. The redirect ensures PAT sees expected 2 XHProf records.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithJsonAndAsJsVarnameStoresCompressedData(): void
    {
        $renderedContent = '{"sidebar":"test content"}';

        // Setup request parameters: json=true, as_js_varname=true
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['store_id', null, '1'],
                ['json', null, true],
                ['block', null, 'sidebar'],
                ['as_js_varname', null, 'true']
            ]);

        // Setup page factory and layout
        $this->resultPageFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPage);

        // Parent class may also call addHandle, so use any() instead of once()
        $this->resultPage->expects($this->any())
            ->method('addHandle')
            ->willReturn($this->resultPage);

        $this->layout->expects($this->once())
            ->method('renderElement')
            ->with('content')
            ->willReturn($renderedContent);

        $this->regexValidator->expects($this->once())
            ->method('validateParamRegex')
            ->with('sidebar')
            ->willReturn(true);

        // Key assertion: Session SHOULD store compressed data for JSON responses
        $this->session->expects($this->once())
            ->method('setUpdateResult')
            ->with($this->callback(function ($data) {
                // Verify data is compressed array format
                return is_array($data)
                    && isset($data['compressed'])
                    && $data['compressed'] === true
                    && isset($data['data']);
            }));

        // Should create and return redirect (maintains 2-request pattern for PAT)
        $resultRedirect = $this->createMock(Redirect::class);
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/*/showUpdateResult')
            ->willReturnSelf();

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);

        // Raw result should NOT be created (using redirect instead)
        $this->resultRawFactory->expects($this->never())
            ->method('create');

        $result = $this->controller->execute();
        $this->assertInstanceOf(Redirect::class, $result);
    }

    /**
     * Test that plain response with as_js_varname stores in session and redirects
     *
     * This is the original behavior that should be preserved for configurable products.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithPlainAndAsJsVarnameStoresInSessionAndRedirects(): void
    {
        $renderedContent = '<div>test content</div>';

        // Setup request parameters: json=false, as_js_varname=true
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['store_id', null, '1'],
                ['json', null, false],
                ['block', null, 'sidebar'],
                ['as_js_varname', null, 'true']
            ]);

        // Setup page factory and layout
        $this->resultPageFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPage);

        // Parent class may also call addHandle, so use any() instead of once()
        $this->resultPage->expects($this->any())
            ->method('addHandle')
            ->willReturn($this->resultPage);

        $this->layout->expects($this->once())
            ->method('renderElement')
            ->with('content')
            ->willReturn($renderedContent);

        $this->regexValidator->expects($this->once())
            ->method('validateParamRegex')
            ->with('sidebar')
            ->willReturn(true);

        // Key assertion: Session SHOULD call setUpdateResult when json=false with as_js_varname
        $this->session->expects($this->once())
            ->method('setUpdateResult')
            ->with($renderedContent);

        // Should create and return redirect
        $resultRedirect = $this->createMock(Redirect::class);
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/*/showUpdateResult')
            ->willReturnSelf();

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);

        // Raw result should NOT be created
        $this->resultRawFactory->expects($this->never())
            ->method('create');

        $result = $this->controller->execute();
        $this->assertInstanceOf(Redirect::class, $result);
    }

    /**
     * Test that JSON response without as_js_varname returns directly
     *
     * @return void
     */
    public function testExecuteWithJsonOnlyReturnsDirectly(): void
    {
        $renderedContent = '{"sidebar":"test content"}';

        // Setup request parameters: json=true, as_js_varname=false
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['store_id', null, '1'],
                ['json', null, true],
                ['block', null, 'sidebar'],
                ['as_js_varname', null, false]
            ]);

        // Setup page factory and layout
        $this->resultPageFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->layout->expects($this->once())
            ->method('renderElement')
            ->with('content')
            ->willReturn($renderedContent);

        $this->regexValidator->expects($this->once())
            ->method('validateParamRegex')
            ->with('sidebar')
            ->willReturn(true);

        // Session should NOT call setUpdateResult when json=true without as_js_varname
        $this->session->expects($this->never())
            ->method('setUpdateResult');

        // Should return Raw result
        $resultRaw = $this->createMock(Raw::class);
        $resultRaw->expects($this->once())
            ->method('setContents')
            ->with($renderedContent)
            ->willReturnSelf();

        $this->resultRawFactory->expects($this->once())
            ->method('create')
            ->willReturn($resultRaw);

        $result = $this->controller->execute();
        $this->assertInstanceOf(Raw::class, $result);
    }

    /**
     * Test that invalid block parameter throws exception
     *
     * @return void
     */
    public function testExecuteWithInvalidBlockParameterThrowsException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The url has invalid characters. Please correct and try again.');

        // Setup request with invalid block parameter
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['store_id', null, '1'],
                ['json', null, true],
                ['block', null, 'sidebar'],
                ['as_js_varname', null, false]
            ]);

        // RegexValidator should reject the block parameter
        $this->regexValidator->expects($this->once())
            ->method('validateParamRegex')
            ->with('sidebar')
            ->willReturn(false);

        // Exception is thrown before page creation, so create should never be called
        $this->resultPageFactory->expects($this->never())
            ->method('create');

        $this->controller->execute();
    }

    /**
     * Test that multiple blocks are properly handled
     *
     * @return void
     */
    public function testExecuteWithMultipleBlocks(): void
    {
        $renderedContent = '{"blocks":"test content"}';
        $blocks = 'sidebar,items,totals';

        // Setup request with multiple blocks
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['store_id', null, '1'],
                ['json', null, true],
                ['block', null, $blocks],
                ['as_js_varname', null, false]
            ]);

        $this->resultPageFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->regexValidator->expects($this->once())
            ->method('validateParamRegex')
            ->with($blocks)
            ->willReturn(true);

        // Parent class may also call addHandle, so use any() instead of once()
        $this->resultPage->expects($this->any())
            ->method('addHandle')
            ->willReturn($this->resultPage);

        $this->layout->expects($this->once())
            ->method('renderElement')
            ->with('content')
            ->willReturn($renderedContent);

        $resultRaw = $this->createMock(Raw::class);
        $resultRaw->expects($this->once())
            ->method('setContents')
            ->with($renderedContent)
            ->willReturnSelf();

        $this->resultRawFactory->expects($this->once())
            ->method('create')
            ->willReturn($resultRaw);

        $result = $this->controller->execute();
        $this->assertInstanceOf(Raw::class, $result);
    }

    /**
     * Test constructor with null regexValidator (fallback to ObjectManager)
     *
     * @return void
     */
    public function testConstructorWithNullRegexValidator(): void
    {
        // Create controller without passing regexValidator (null)
        $forwardFactory = $this->createMock(ForwardFactory::class);
        $controller = new LoadBlock(
            $this->context,
            $this->productHelper,
            $this->escaper,
            $this->resultPageFactory,
            $forwardFactory,
            $this->resultRawFactory,
            $this->storeManager,
            null  // regexValidator is null, should use ObjectManager fallback
        );

        $this->assertInstanceOf(LoadBlock::class, $controller);
    }

    /**
     * Test LocalizedException during process data
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithLocalizedExceptionDuringProcessing(): void
    {
        // Create a partial mock that will throw LocalizedException
        $forwardFactory = $this->createMock(ForwardFactory::class);
        $controller = $this->getMockBuilder(LoadBlock::class)
            ->setConstructorArgs([
                $this->context,
                $this->productHelper,
                $this->escaper,
                $this->resultPageFactory,
                $forwardFactory,
                $this->resultRawFactory,
                $this->storeManager,
                $this->regexValidator
            ])
            ->onlyMethods(['_initSession', '_processData', '_reloadQuote'])
            ->getMock();

        // Setup request parameters
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['store_id', null, '1'],
                ['json', null, true],
                ['block', null, 'sidebar'],
                ['as_js_varname', null, false]
            ]);

        // _initSession returns $this
        $controller->expects($this->once())
            ->method('_initSession')
            ->willReturn($controller);

        // _processData throws LocalizedException
        $controller->expects($this->once())
            ->method('_processData')
            ->willThrowException(new LocalizedException(__('Test localized exception')));

        // _reloadQuote should be called in catch block
        $controller->expects($this->once())
            ->method('_reloadQuote');

        $this->regexValidator->expects($this->once())
            ->method('validateParamRegex')
            ->with('sidebar')
            ->willReturn(true);

        $this->resultPageFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->resultPage->expects($this->any())
            ->method('addHandle')
            ->willReturn($this->resultPage);

        $this->layout->expects($this->once())
            ->method('renderElement')
            ->with('content')
            ->willReturn('test content');

        $resultRaw = $this->createMock(Raw::class);
        $resultRaw->expects($this->once())
            ->method('setContents')
            ->with('test content')
            ->willReturnSelf();

        $this->resultRawFactory->expects($this->once())
            ->method('create')
            ->willReturn($resultRaw);

        $result = $controller->execute();
        $this->assertInstanceOf(Raw::class, $result);
    }

    /**
     * Test generic Exception during process data
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithGenericExceptionDuringProcessing(): void
    {
        // Create a partial mock that will throw Exception
        $forwardFactory = $this->createMock(ForwardFactory::class);
        $controller = $this->getMockBuilder(LoadBlock::class)
            ->setConstructorArgs([
                $this->context,
                $this->productHelper,
                $this->escaper,
                $this->resultPageFactory,
                $forwardFactory,
                $this->resultRawFactory,
                $this->storeManager,
                $this->regexValidator
            ])
            ->onlyMethods(['_initSession', '_processData', '_reloadQuote'])
            ->getMock();

        // Setup request parameters
        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['store_id', null, '1'],
                ['json', null, true],
                ['block', null, 'sidebar'],
                ['as_js_varname', null, false]
            ]);

        // _initSession returns $this
        $controller->expects($this->once())
            ->method('_initSession')
            ->willReturn($controller);

        // _processData throws generic Exception
        $controller->expects($this->once())
            ->method('_processData')
            ->willThrowException(new \Exception('Test generic exception'));

        // _reloadQuote should be called in catch block
        $controller->expects($this->once())
            ->method('_reloadQuote');

        $this->regexValidator->expects($this->once())
            ->method('validateParamRegex')
            ->with('sidebar')
            ->willReturn(true);

        $this->resultPageFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPage);

        $this->resultPage->expects($this->any())
            ->method('addHandle')
            ->willReturn($this->resultPage);

        $this->layout->expects($this->once())
            ->method('renderElement')
            ->with('content')
            ->willReturn('test content');

        $resultRaw = $this->createMock(Raw::class);
        $resultRaw->expects($this->once())
            ->method('setContents')
            ->with('test content')
            ->willReturnSelf();

        $this->resultRawFactory->expects($this->once())
            ->method('create')
            ->willReturn($resultRaw);

        $result = $controller->execute();
        $this->assertInstanceOf(Raw::class, $result);
    }
}
