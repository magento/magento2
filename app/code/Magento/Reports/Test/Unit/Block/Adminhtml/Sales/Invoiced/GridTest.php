<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Sales\Invoiced;

use Magento\Backend\Block\Template\Context;
use Magento\Directory\Model\Currency;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Framework\Test\Unit\Helper\RequestInterfaceTestHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date;
use Magento\Reports\Helper\Data;
use Magento\Reports\Model\Grouped\CollectionFactory;
use Magento\Reports\Model\ResourceModel\Report\Collection\Factory;
use Magento\Reports\Block\Adminhtml\Sales\Invoiced\Grid;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends TestCase
{

    /**
     * @var Context|MockObject
     */
    private Context $context;

    /**
     * @var \Magento\Backend\Helper\Data|MockObject
     */
    private \Magento\Backend\Helper\Data $backendHelper;

    /**
     * @var Factory|MockObject
     */
    private Factory $resourceFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var Data|MockObject
     */
    private Data $reportsData;

    /**
     * @var LayoutInterface|MockObject
     */
    private LayoutInterface $layout;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var UrlInterface|MockObject
     */
    private UrlInterface $urlBuilder;

    /**
     * @var ManagerInterface|MockObject
     */
    private ManagerInterface $eventManager;

    /**
     * @var Random|MockObject
     */
    private Random $mathRandom;

    /**
     * @var RequestInterfaceTestHelper|MockObject
     */
    private RequestInterfaceTestHelper $request;

    /**
     * @var Grid
     */
    private Grid $invoicedGrid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this->createMock(RequestInterfaceTestHelper::class);
        $this->mathRandom = $this->createMock(Random::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->context = $this->createMock(Context::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->context->method('getEventManager')->willReturn($this->eventManager);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->context->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $filesystem = $this->createMock(Filesystem::class);
        $this->context->method('getFilesystem')->willReturn($filesystem);
        $this->layout = $this->createMock(LayoutInterface::class);
        $this->context->method('getLayout')->willReturn($this->layout);
        $this->context->method('getStoreManager')->willReturn($this->storeManager);
        $this->context->method('getMathRandom')->willReturn($this->mathRandom);
        $this->context->method('getRequest')->willReturn($this->request);

        $this->backendHelper = $this->createMock(\Magento\Backend\Helper\Data::class);
        $this->resourceFactory = $this->createMock(Factory::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->reportsData = $this->createMock(Data::class);

        $filterData = new DataObject();
        $this->invoicedGrid = new Grid(
            $this->context,
            $this->backendHelper,
            $this->resourceFactory,
            $this->collectionFactory,
            $this->reportsData,
            [
                'filter_data' => $filterData,
                'id' => 'test'
            ]
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testColumnsRenderer(): void
    {
        $currencyCode = 'USD';
        $rate = 0.5;
        $this->layout->method('getChildName')->willReturn('columns');
        $block = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->addMethods(['getColumns', 'isAvailable'])
            ->onlyMethods(['getChildBlock', 'getChildNames', 'setChild'])
            ->getMock();
        $block->method('getColumns')->willReturn([]);
        $this->layout->method('getBlock')->willReturn($block);
        $extendedBlock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['setId', 'setGrid', 'setDataAttribute'])
            ->onlyMethods(['setData'])
            ->getMock();

        $expectedData = $this->getColumnData($currencyCode, $rate);
        $callIndex = 0;
        $extendedBlock
            ->method('setData')
            ->willReturnCallback(function (array $data) use (&$callIndex, $expectedData, $extendedBlock) {
                if (!isset($expectedData[$callIndex])) {
                    return $extendedBlock;
                }
                $expected = $this->normalizeDataArray($expectedData[$callIndex]);
                $actual = $this->normalizeDataArray($data);

                self::assertSame(
                    $expected,
                    $actual,
                    sprintf('Unexpected data passed to setData() at call #%d', $callIndex + 1)
                );

                $callIndex++;

                return $extendedBlock;
            });

        $extendedBlock->method('setId')->willReturnSelf();
        $extendedBlock->method('setGrid')->willReturnSelf();
        $this->layout->method('createBlock')->willReturn($extendedBlock);
        $block->method('getChildBlock')->willReturn($extendedBlock);
        $block->method('getChildNames')->willReturn([]);

        $this->storeManager->method('getStores')->willReturn([]);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->method('getBaseCurrencyCode')->willReturn($currencyCode);
        $currency = $this->createMock(Currency::class);
        $currency->method('getRate')->willReturn($rate);
        $store->method('getBaseCurrency')->willReturn($currency);
        $this->storeManager->method('getStore')->willReturn($store);

        $collection = $this->createMock(AbstractDb::class);
        $select = $this->createMock(Select::class);
        $collection->method('getSelect')->willReturn($select);
        $collection
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([new DataObject([])]));

        $this->invoicedGrid->setTotals(new DataObject());
        $this->invoicedGrid->setCollection($collection);
        $this->invoicedGrid->getXml();
    }

    /**
     * Invoice grid column data
     *
     * @param string $currencyCode
     * @param float $rate
     * @return array[]
     */
    private function getColumnData(string $currencyCode, float $rate): array
    {
        return [
            [
                'header' => __('Interval'),
                'index' => 'period',
                'sortable' => false,
                'period_type' => null,
                'renderer' => Date::class,
                'totals_label' => __('Total'),
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ],
            [
                'header' => __('Orders'),
                'index' => 'orders_count',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ],
            [
                'header' => __('Invoiced Orders'),
                'index' => 'orders_invoiced',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-invoiced',
                'column_css_class' => 'col-invoiced'
            ],
            [
                'header' => __('Total Invoiced'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'invoiced',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-invoiced',
                'column_css_class' => 'col-total-invoiced',
                'renderer' => \Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency::class
            ],
            [
                'header' => __('Paid Invoices'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'invoiced_captured',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-invoiced-paid',
                'column_css_class' => 'col-total-invoiced-paid',
                'renderer' => \Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency::class
            ],
            [
                'header' => __('Unpaid Invoices'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'invoiced_not_captured',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-invoiced-not-paid',
                'column_css_class' => 'col-total-invoiced-not-paid',
                'renderer' => \Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency::class
            ]
        ];
    }

    /**
     * Prepare data for assertion
     *
     * @param array $data
     * @return array
     */
    private function normalizeDataArray(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_object($value)) {
                $value = (string)$value;
            }
        });

        return $data;
    }
}
