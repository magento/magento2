<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Helper\Product;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Controller\Adminhtml\Order\Create\ShowUpdateResult;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Unit test for ShowUpdateResult controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class ShowUpdateResultTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ShowUpdateResult
     */
    private $controller;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var RawFactory|MockObject
     */
    private $resultRawFactory;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var Raw|MockObject
     */
    private $resultRaw;

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->resultRawFactory = $this->createMock(RawFactory::class);
        $this->session = $this->createPartialMockWithReflection(
            Session::class,
            ['hasUpdateResult', 'getUpdateResult', 'unsUpdateResult']
        );
        $this->resultRaw = $this->createMock(Raw::class);

        // Mock required context dependencies
        $request = $this->createPartialMock(Http::class, ['getPost', 'getPostValue', 'has', 'getParam']);

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);
        $this->context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);

        // Mock event manager
        $eventManager = $this->createMock(ManagerInterface::class);
        $eventManager->expects($this->any())
            ->method('dispatch')
            ->willReturn(true);
        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);

        // Mock message manager
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

        // Setup dependencies for parent class constructor
        $quoteSession = $this->createMock(QuoteSession::class);
        $registry = $this->createMock(Registry::class);
        $store = $this->createMock(Store::class);
        $quote = $this->createMock(Quote::class);
        $customer = $this->createPartialMockWithReflection(Customer::class, ['getCustomerGroupId']);

        $registry->expects($this->any())
            ->method('unregister')
            ->willReturnSelf();
        $registry->expects($this->any())
            ->method('register')
            ->willReturnSelf();

        $store->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $customer->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(1);

        $shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSameAsBilling'])
            ->getMock();
        $shippingAddress->expects($this->any())
            ->method('getSameAsBilling')
            ->willReturn(false);

        $quote->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $quote->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);

        $quoteSession->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $quoteSession->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);

        $adminOrderCreate = $this->createPartialMockWithReflection(Create::class, ['setStoreId']);

        $reflection = new \ReflectionClass($adminOrderCreate);
        $registryProperty = $reflection->getProperty('_coreRegistry');
        $registryProperty->setValue($adminOrderCreate, $registry);

        $sessionProperty = $reflection->getProperty('_session');
        $sessionProperty->setValue($adminOrderCreate, $quoteSession);

        $adminOrderCreate->expects($this->any())
            ->method('setStoreId')
            ->willReturnSelf();

        // Setup ObjectManager to return session and other dependencies
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($className) use ($quoteSession, $adminOrderCreate, $registry) {
                switch ($className) {
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

        // Bootstrap ObjectManager with our mock BEFORE creating controller
        ObjectManager::setInstance($this->objectManager);

        $productHelper = $this->createMock(Product::class);
        $productHelper->expects($this->any())
            ->method('setSkipSaleableCheck')
            ->with(true)
            ->willReturnSelf();

        $escaper = $this->createMock(Escaper::class);
        $resultPageFactory = $this->createMock(PageFactory::class);
        $resultForwardFactory = $this->createMock(ForwardFactory::class);

        $this->controller = new ShowUpdateResult(
            $this->context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory,
            $this->resultRawFactory
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        // Clean up ObjectManager instance
        try {
            $cleanObjectManager = $this->createMock(ObjectManagerInterface::class);
            ObjectManager::setInstance($cleanObjectManager);
        } catch (\Exception $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            // Ignore any errors during cleanup
        }
        parent::tearDown();
    }

    /**
     * Test execute with compressed data (from JSON responses)
     *
     * @return void
     */
    public function testExecuteWithCompressedData(): void
    {
        $originalContent = '{"sidebar":"test content with lots of data"}';
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $compressedData = gzencode($originalContent, 6);
        $sessionData = [
            'compressed' => true,
            'data' => $compressedData
        ];

        // Session has compressed data
        $this->session->expects($this->once())
            ->method('hasUpdateResult')
            ->willReturn(true);

        $this->session->expects($this->once())
            ->method('getUpdateResult')
            ->willReturn($sessionData);

        // Result should contain decompressed content
        $this->resultRawFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRaw);

        $this->resultRaw->expects($this->once())
            ->method('setContents')
            ->with($originalContent)
            ->willReturnSelf();

        // Session should be cleared
        $this->session->expects($this->once())
            ->method('unsUpdateResult');

        $result = $this->controller->execute();
        $this->assertInstanceOf(Raw::class, $result);
    }

    /**
     * Test execute with scalar/string data (backward compatibility for non-JSON responses)
     *
     * @return void
     */
    public function testExecuteWithScalarData(): void
    {
        $content = '<div>test content</div>';

        // Session has scalar string data
        $this->session->expects($this->once())
            ->method('hasUpdateResult')
            ->willReturn(true);

        $this->session->expects($this->once())
            ->method('getUpdateResult')
            ->willReturn($content);

        // Result should contain the content as-is
        $this->resultRawFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRaw);

        $this->resultRaw->expects($this->once())
            ->method('setContents')
            ->with($content)
            ->willReturnSelf();

        // Session should be cleared
        $this->session->expects($this->once())
            ->method('unsUpdateResult');

        $result = $this->controller->execute();
        $this->assertInstanceOf(Raw::class, $result);
    }

    /**
     * Test execute with empty session (no update result)
     *
     * @return void
     */
    public function testExecuteWithEmptySession(): void
    {
        // Session has no update result
        $this->session->expects($this->once())
            ->method('hasUpdateResult')
            ->willReturn(false);

        // getUpdateResult should not be called
        $this->session->expects($this->never())
            ->method('getUpdateResult');

        // Result should be created but no content set
        $this->resultRawFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRaw);

        $this->resultRaw->expects($this->never())
            ->method('setContents');

        // Session should still be cleared
        $this->session->expects($this->once())
            ->method('unsUpdateResult');

        $result = $this->controller->execute();
        $this->assertInstanceOf(Raw::class, $result);
    }

    /**
     * Test execute with invalid compressed data (missing 'data' key)
     *
     * @return void
     */
    public function testExecuteWithInvalidCompressedData(): void
    {
        $sessionData = [
            'compressed' => true
            // Missing 'data' key
        ];

        // Session has invalid compressed data
        $this->session->expects($this->once())
            ->method('hasUpdateResult')
            ->willReturn(true);

        $this->session->expects($this->once())
            ->method('getUpdateResult')
            ->willReturn($sessionData);

        // Result should be created with empty content
        $this->resultRawFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRaw);

        $this->resultRaw->expects($this->once())
            ->method('setContents')
            ->with('')
            ->willReturnSelf();

        // Session should be cleared
        $this->session->expects($this->once())
            ->method('unsUpdateResult');

        $result = $this->controller->execute();
        $this->assertInstanceOf(Raw::class, $result);
    }

    /**
     * Test execute with array data but not compressed format
     *
     * @return void
     */
    public function testExecuteWithNonCompressedArrayData(): void
    {
        $sessionData = [
            'some' => 'data',
            'not' => 'compressed'
        ];

        // Session has array data but not in compressed format
        $this->session->expects($this->once())
            ->method('hasUpdateResult')
            ->willReturn(true);

        $this->session->expects($this->once())
            ->method('getUpdateResult')
            ->willReturn($sessionData);

        // Result should be created but no content set (array is not scalar)
        $this->resultRawFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRaw);

        $this->resultRaw->expects($this->never())
            ->method('setContents');

        // Session should be cleared
        $this->session->expects($this->once())
            ->method('unsUpdateResult');

        $result = $this->controller->execute();
        $this->assertInstanceOf(Raw::class, $result);
    }
}
