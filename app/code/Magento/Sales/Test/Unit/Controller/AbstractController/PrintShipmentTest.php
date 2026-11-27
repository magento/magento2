<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\AbstractController;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface;
use Magento\Sales\Controller\AbstractController\PrintShipment;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintShipmentTest extends TestCase
{
    /**
     * @var PrintShipment
     */
    private $controller;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var OrderViewAuthorizationInterface|MockObject
     */
    private $orderAuthorization;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var PageFactory|MockObject
     */
    private $pageFactory;

    /**
     * @var Redirect|MockObject
     */
    private $redirect;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var ShipmentRepositoryInterface|MockObject
     */
    private $repository;

    protected function setUp(): void
    {
        $this->request = $this->createMock(Http::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->orderAuthorization = $this->createMock(OrderViewAuthorizationInterface::class);
        $this->registry = $this->createMock(Registry::class);
        $this->pageFactory = $this->createMock(PageFactory::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->session = $this->createMock(Session::class);
        $this->repository = $this->createMock(ShipmentRepositoryInterface::class);

        $redirectFactory = $this->createMock(RedirectFactory::class);
        $redirectFactory->method('create')->willReturn($this->redirect);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($this->request);
        $context->method('getObjectManager')->willReturn($this->objectManager);
        $context->method('getMessageManager')->willReturn($this->messageManager);
        $context->method('getResultRedirectFactory')->willReturn($redirectFactory);

        $this->objectManager->method('create')->willReturnCallback(
            fn (string $type) => match ($type) {
                ShipmentRepositoryInterface::class => $this->repository,
                Order::class => $this->createMock(Order::class),
                default => null
            }
        );
        $this->objectManager->method('get')->willReturnMap([[Session::class, $this->session]]);

        $this->controller = new class (
            $context,
            $this->orderAuthorization,
            $this->registry,
            $this->pageFactory
        ) extends PrintShipment {
        };
    }

    #[DataProvider('redirectDataProvider')]
    public function testMissingShipmentRedirectsWithErrorMessage(bool $loggedIn, string $path): void
    {
        $this->request->method('getParam')->with('shipment_id')->willReturn('5');
        $this->repository->expects($this->once())
            ->method('get')
            ->with(5)
            ->willThrowException(new NoSuchEntityException(__('Missing.')));
        $this->session->method('isLoggedIn')->willReturn($loggedIn);
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with('Missing.');
        $this->orderAuthorization->expects($this->never())->method('canView');
        $this->registry->expects($this->never())->method('register');
        $this->redirect->expects($this->once())->method('setPath')->with($path)->willReturnSelf();

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    #[DataProvider('redirectDataProvider')]
    public function testMissingOrderOfShipmentRedirectsWithErrorMessage(bool $loggedIn, string $path): void
    {
        $shipment = $this->createMock(Shipment::class);
        $shipment->method('getOrder')->willThrowException(new NoSuchEntityException(__('Missing order.')));
        $this->request->method('getParam')->with('shipment_id')->willReturn('5');
        $this->repository->method('get')->with(5)->willReturn($shipment);
        $this->session->method('isLoggedIn')->willReturn($loggedIn);
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with('Missing order.');
        $this->orderAuthorization->expects($this->never())->method('canView');
        $this->redirect->expects($this->once())->method('setPath')->with($path)->willReturnSelf();

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public function testExistingShipmentIsPrintedWhenAuthorized(): void
    {
        $order = $this->createMock(Order::class);
        $shipment = $this->createMock(Shipment::class);
        $shipment->method('getOrder')->willReturn($order);
        $page = $this->createMock(Page::class);
        $this->request->method('getParam')->with('shipment_id')->willReturn('5');
        $this->repository->method('get')->with(5)->willReturn($shipment);
        $this->orderAuthorization->method('canView')->with($order)->willReturn(true);
        $this->pageFactory->method('create')->willReturn($page);
        $page->expects($this->once())->method('addHandle')->with('print');
        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $registered = [];
        $this->registry->method('register')->willReturnCallback(
            function (string $key, $value) use (&$registered) {
                $registered[$key] = $value;
            }
        );

        $this->assertSame($page, $this->controller->execute());
        $this->assertSame(['current_order' => $order, 'current_shipment' => $shipment], $registered);
    }

    #[DataProvider('redirectDataProvider')]
    public function testUnauthorizedShipmentRedirects(bool $loggedIn, string $path): void
    {
        $shipment = $this->createMock(Shipment::class);
        $shipment->method('getOrder')->willReturn($this->createMock(Order::class));
        $this->request->method('getParam')->with('shipment_id')->willReturn('5');
        $this->repository->method('get')->with(5)->willReturn($shipment);
        $this->orderAuthorization->method('canView')->willReturn(false);
        $this->session->method('isLoggedIn')->willReturn($loggedIn);
        $this->registry->expects($this->never())->method('register');
        $this->redirect->expects($this->once())->method('setPath')->with($path)->willReturnSelf();

        $this->assertSame($this->redirect, $this->controller->execute());
    }

    public static function redirectDataProvider(): array
    {
        return [
            'logged in customer' => [true, '*/*/history'],
            'guest' => [false, 'sales/guest/form'],
        ];
    }
}
