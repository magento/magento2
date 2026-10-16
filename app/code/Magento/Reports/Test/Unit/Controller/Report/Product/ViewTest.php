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
use Magento\Reports\Model\Product\Index\ViewedFactory;
use Magento\Reports\Model\ReportStatus;
use Magento\Reports\Observer\EventSaver;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

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
        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->with('product_id')->willReturn('42');
        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($request);

        $result = $this->createMock(Json::class);
        $result->method('setData')->willReturnSelf();
        $jsonFactory = $this->createMock(JsonFactory::class);
        $jsonFactory->method('create')->willReturn($result);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $this->reportStatus = $this->createMock(ReportStatus::class);
        $this->reportStatus->method('isReportEnabled')->willReturn(true);
        $this->visitor = $this->createMock(Visitor::class);
        $this->viewedFactory = $this->createMock(ViewedFactory::class);
        $this->eventSaver = $this->createMock(EventSaver::class);

        $this->controller = new View(
            $context,
            $jsonFactory,
            $this->reportStatus,
            $storeManager,
            $this->createMock(Session::class),
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
}
