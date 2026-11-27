<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Report\Product;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Reports\Controller\Report\Product\View;
use Magento\Reports\Model\Event;
use Magento\Reports\Model\Product\Index\Viewed;
use Magento\Reports\Model\Product\Index\ViewedFactory;
use Magento\Reports\Model\ReportStatus;
use Magento\Reports\Observer\EventSaver;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    /**
     * @var ReportStatus
     */
    private ReportStatus $reportStatus;

    /**
     * @var Visitor
     */
    private Visitor $visitor;

    /**
     * @var ViewedFactory
     */
    private ViewedFactory $viewedFactory;

    /**
     * @var EventSaver
     */
    private EventSaver $eventSaver;

    /**
     * @var View
     */
    private View $controller;

    protected function setUp(): void
    {
        $request = $this->createStub(RequestInterface::class);
        $request->method('getParam')->willReturnMap([['product_id', null, '42']]);
        $context = $this->createStub(Context::class);
        $context->method('getRequest')->willReturn($request);

        $result = $this->createStub(Json::class);
        $result->method('setData')->willReturnSelf();
        $jsonFactory = $this->createStub(JsonFactory::class);
        $jsonFactory->method('create')->willReturn($result);

        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $this->reportStatus = $this->createStub(ReportStatus::class);
        $this->reportStatus->method('isReportEnabled')->willReturn(true);
        $this->visitor = $this->createStub(Visitor::class);
        $this->viewedFactory = $this->createMock(ViewedFactory::class);
        $this->eventSaver = $this->createMock(EventSaver::class);

        $this->controller = new View(
            $context,
            $jsonFactory,
            $this->reportStatus,
            $storeManager,
            $this->createStub(Session::class),
            $this->visitor,
            $this->viewedFactory,
            $this->eventSaver
        );
    }

    public function testExecuteSkipsIgnoredUserAgent(): void
    {
        $this->visitor->method('isIgnoredUserAgent')->willReturn(true);
        $this->viewedFactory->expects($this->never())->method('create');
        $this->eventSaver->expects($this->never())->method('save');

        $this->controller->execute();
    }

    public function testExecuteRecordsViewForRegularUserAgent(): void
    {
        $this->visitor->method('isIgnoredUserAgent')->willReturn(false);
        $this->visitor->method('getId')->willReturn(7);

        $viewed = $this->createMock(Viewed::class);
        $viewed->expects($this->once())
            ->method('setData')
            ->with(['product_id' => '42', 'store_id' => 1, 'visitor_id' => 7])
            ->willReturnSelf();
        $viewed->expects($this->once())->method('save')->willReturnSelf();
        $viewed->expects($this->once())->method('calculate')->willReturnSelf();
        $this->viewedFactory->expects($this->once())->method('create')->willReturn($viewed);
        $this->eventSaver->expects($this->once())
            ->method('save')
            ->with(Event::EVENT_PRODUCT_VIEW, '42');

        $this->controller->execute();
    }
}
