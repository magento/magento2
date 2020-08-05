<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\ConfigInterface;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verify view test block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    private $block;

    /**
     * @var MockObject|Registry
     */
    private $coreRegistryMock;

    /**
     * @var MockObject|Sales
     */
    private $salesConfigMock;

    /**
     * @var MockObject|Reorder
     */
    private $reorderHelperMock;

    /**
     * @var MockObject|Context
     */
    private $contextMock;

    /**
     * @var MockObject|Order
     */
    private $orderMock;

    /**
     * @var MockObject|Url
     */
    private $urlBuilderMock;

    /**
     * @var MockObject|TimezoneInterface
     */
    private $localeDateMock;

    /**
     * @var MockObject|RequestInterface
     */
    private $requestMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->salesConfigMock = $this->createMock(ConfigInterface::class);
        $this->reorderHelperMock = $this->createMock(Reorder::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass();

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('sales_order')
            ->will($this->returnValue($this->orderMock));
        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        $buttonList = $this->createPartialMock(ButtonList::class, ['remove', 'add']);
        $this->contextMock->expects($this->once())
            ->method('getButtonList')
            ->will($this->returnValue($buttonList));
        $this->contextMock->expects($this->any())
             ->method('getLocaleDate')
             ->willReturn($this->localeDateMock);

        $objectManagerhelper = new ObjectManager($this);
        $this->block = $objectManagerhelper->getObject(
            View::class,
            [
                'context' => $this->contextMock,
                '_coreRegistry' => $this->coreRegistryMock,
                '_salesConfig' => $this->salesConfigMock,
                '_reorderHelper' => $this->reorderHelperMock
            ]
        );
    }

    /**
     * Verify header types
     *
     * @return void
     */
    public function testGetHeaderTest(): void
    {
        $this->orderMock->expects($this->once())
            ->method('getExtOrderId')
            ->willReturn('12345678');

        $this->assertEquals(
            new Phrase(
                'Order # %1 %2 | %3',
                [null, '[12345678] ', null]
            ),
            $this->block->getHeaderText()
        );
    }

    /**
     * Verify getBackUrl method redirect ot orders grid
     *
     * @return void
     */
    public function testGetBackUrlToOrdersGrid(): void
    {
        $expectedUrl = 'https://magento.local/admin/sales/order';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('sales/*/')
            ->willReturn($expectedUrl);

        $this->assertEquals($expectedUrl, $this->block->getBackUrl());
    }

    /**
     * Verify getBackUrl method redirect to customer edit page
     *
     * @return void
     */
    public function testGetBackUrlToCustomerEditPage(): void
    {
        $expectedUrl = 'https://magento.local/admin/customer/index/edit';

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->with('customer_id')
            ->willReturn(1);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('customer/index/edit')
            ->willReturn($expectedUrl);

        $this->assertEquals($expectedUrl, $this->block->getBackUrl());
    }
}
