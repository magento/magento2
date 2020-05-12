<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Orders;

use Magento\Customer\Block\Adminhtml\Edit\Tab\Orders;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\System\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Test cases related to check that orders tab with customer orders
 * grid correctly renders and contains all necessary data.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class RenderOrdersTabTest extends TestCase
{
    private const PATHS_TO_TABLE_BODY = [
        "//div[contains(@data-grid-id, 'customer_orders_grid')]",
        "//table[contains(@class, 'data-grid')]",
        "//tbody",
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var CurrencyInterface
     */
    private $currency;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Orders
     */
    private $ordersGridBlock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->store = $this->objectManager->get(Store::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->currency = $this->objectManager->get(CurrencyInterface::class);
        $this->timezone = $this->objectManager->get(TimezoneInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        parent::tearDown();
    }

    /**
     * Assert that customer orders tab renders with message "We couldn't find any records."
     * when customer doesn't have any orders.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testRenderBlockWithoutOrders(): void
    {
        $this->processCheckOrdersGridByCustomerId(1, 0);
    }

    /**
     * Assert that customer orders tab renders without message "We couldn't find any records."
     * and contains rendered order item when customer has one order.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     *
     * @return void
     */
    public function testRenderBlockWithOneOrder(): void
    {
        $this->processCheckOrdersGridByCustomerId(1, 1);
    }

    /**
     * Assert that customer orders tab renders without message "We couldn't find any records."
     * and contains rendered orders items when customer has few orders.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/orders_with_customer.php
     *
     * @return void
     */
    public function testRenderBlockWithFewOrders(): void
    {
        $this->processCheckOrdersGridByCustomerId(1, 5);
    }

    /**
     * Render orders grid and assert that all data rendered as expected.
     *
     * @param int $customerId
     * @param int $expectedOrderCount
     * @return void
     */
    private function processCheckOrdersGridByCustomerId(int $customerId, int $expectedOrderCount): void
    {
        $this->registerCustomerId($customerId);
        $ordersGridHtml = $this->getOrdersGridHtml();
        $orderItemsData = $this->getOrderGridItemsData();
        $this->assertOrdersCount($expectedOrderCount, $ordersGridHtml);
        $this->assertIsEmptyGridMessageArrears($ordersGridHtml, $expectedOrderCount === 0);
        $this->checkOrderItemsFields($orderItemsData, $ordersGridHtml);
    }

    /**
     * Add customer id to registry.
     *
     * @param int $customerId
     * @return void
     */
    private function registerCustomerId(int $customerId): void
    {
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->registry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
    }

    /**
     * Render customer orders tab.
     *
     * @return string
     */
    private function getOrdersGridHtml(): string
    {
        $this->ordersGridBlock = $this->layout->createBlock(Orders::class);

        return $this->ordersGridBlock->toHtml();
    }

    /**
     * Check that rendered html contains all provided order items.
     *
     * @param array $orderItemsData
     * @param string $html
     * @return void
     */
    private function checkOrderItemsFields(array $orderItemsData, string $html): void
    {
        foreach ($orderItemsData as $itemOrder => $orderItemData) {
            $this->assertViewOrderUrl($itemOrder, $orderItemData['order_id'], $html);
            $this->assertReorderUrl($itemOrder, $orderItemData['order_id'], $html);
            $this->assertStoreViewLabels($itemOrder, $orderItemData['store_view_labels'], $html);
            unset($orderItemData['order_id'], $orderItemData['store_view_labels']);
            $this->assertColumnsValues($itemOrder, $orderItemData, $html);
        }
    }

    /**
     * Assert that field store_id contains all provided store codes.
     *
     * @param int $itemOrder
     * @param array $storeViewLabels
     * @param string $html
     * @return void
     */
    private function assertStoreViewLabels(int $itemOrder, array $storeViewLabels, string $html): void
    {
        if (empty($storeViewLabels)) {
            return;
        }

        $elementPaths = array_merge(self::PATHS_TO_TABLE_BODY, [
            "//tr[{$itemOrder}]",
            "//td[contains(@class, 'store_id') and  %s]",
        ]);
        $storeLabelsPaths = [];
        foreach ($storeViewLabels as $labelIndex => $storeViewLabel) {
            $storeLabelsPaths[] = "contains(text()[{$labelIndex}], '{$storeViewLabel}')";
        }
        $checkStoreViewsXPath = sprintf(implode('', $elementPaths), implode(' and ', $storeLabelsPaths));
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($checkStoreViewsXPath, $html),
            sprintf("Some store view label not found. Labels: %s. Html: %s", implode(', ', $storeViewLabels), $html)
        );
    }

    /**
     * Assert that columns values as expected.
     *
     * @param int $itemOrder
     * @param array $columnsData
     * @param string $html
     * @return void
     */
    private function assertColumnsValues(int $itemOrder, array $columnsData, string $html): void
    {
        $elementPaths = array_merge(self::PATHS_TO_TABLE_BODY, [
            "//tr[{$itemOrder}]",
            "//td[contains(@class, '%s') and contains(text(), '%s')]",
        ]);
        $elementXPathTemplate = implode('', $elementPaths);
        foreach ($columnsData as $columnName => $columnValue) {
            $preparedXPath = sprintf($elementXPathTemplate, $columnName, $columnValue);
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath($preparedXPath, $html),
                sprintf("Column %s doesn't have value %s. Html: %s", $columnName, $columnValue, $html)
            );
        }
    }

    /**
     * Assert that rendered html contains URL to reorder by order id.
     *
     * @param int $itemOrder
     * @param int $orderId
     * @param string $html
     * @return void
     */
    private function assertReorderUrl(int $itemOrder, int $orderId, string $html): void
    {
        $urlLabel = (string)__('Reorder');
        $elementPaths = array_merge(self::PATHS_TO_TABLE_BODY, [
            "//tr[{$itemOrder}]",
            "//td[contains(@class, 'action')]",
            "//a[contains(@href, 'sales/order_create/reorder/order_id/$orderId') and contains(text(), '{$urlLabel}')]",
        ]);
        $reorderUrlXPath = implode('', $elementPaths);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($reorderUrlXPath, $html),
            sprintf('Reorder URL is not as expected. Html: %s', $html)
        );
    }

    /**
     * Assert that rendered html contains URL to order view by order id.
     *
     * @param int $itemOrder
     * @param int $orderId
     * @param string $html
     * @return void
     */
    private function assertViewOrderUrl(int $itemOrder, int $orderId, string $html): void
    {
        $elementPaths = array_merge(self::PATHS_TO_TABLE_BODY, [
            "//tr[{$itemOrder}][contains(@title, 'sales/order/view/order_id/{$orderId}')]",
        ]);
        $viewOrderUrlXPath = implode('', $elementPaths);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($viewOrderUrlXPath, $html),
            sprintf('URL to view order is not as expected. Html: %s', $html)
        );
    }

    /**
     * Assert that provided orders count and count in html are equals.
     *
     * @param int $expectedOrdersCount
     * @param string $html
     * @return void
     */
    private function assertOrdersCount(int $expectedOrdersCount, string $html): void
    {
        $elementPaths = [
            "//div[contains(@data-grid-id, 'customer_orders_grid')]",
            "//div[contains(@class, 'grid-header-row')]",
            "//div[contains(@class, 'control-support-text')]",
            sprintf("//span[contains(@id, 'grid-total-count') and contains(text(), '%s')]", $expectedOrdersCount),
        ];
        $ordersCountXPath = implode('', $elementPaths);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($ordersCountXPath, $html),
            sprintf('Provided count and count in html are not equals. Html: %s', $html)
        );
    }

    /**
     * Assert that grid contains or not contains message "We couldn't find any records.".
     *
     * @param string $html
     * @param bool $isMessageAppears
     * @return void
     */
    private function assertIsEmptyGridMessageArrears(string $html, bool $isMessageAppears = false): void
    {
        $gridText = (string)__("We couldn't find any records.");
        $elementPaths = array_merge(self::PATHS_TO_TABLE_BODY, [
            "//tr[contains(@class, 'tr-no-data')]",
            "//td[contains(@class, 'empty-text') and contains(text(), \"{$gridText}\")]",
        ]);
        $emptyTextXPath = implode('', $elementPaths);
        $this->assertEquals(
            $isMessageAppears ? 1 : 0,
            Xpath::getElementsCountForXpath($emptyTextXPath, $html),
            sprintf('Message "We couldn\'t find any records." not found in html. Html: %s', $html)
        );
    }

    /**
     * Build array with rendered orders for check that all contained data appears.
     *
     * @return array
     */
    private function getOrderGridItemsData(): array
    {
        $orders = [];
        $orderNumber = 1;
        /** @var Document $order */
        foreach ($this->ordersGridBlock->getCollection() as $order) {
            $orderGrandTotal = $this->prepareGrandTotal(
                $order->getData('grand_total'),
                $order->getData('order_currency_code')
            );
            $orders[$orderNumber] = [
                'order_id' => (int)$order->getData(OrderInterface::ENTITY_ID),
                'increment_id' => $order->getData(OrderInterface::INCREMENT_ID),
                'created_at' => $this->prepareCreatedAtDate($order->getData(OrderInterface::CREATED_AT)),
                'billing_name' => $order->getData('billing_name'),
                'shipping_name' => $order->getData('shipping_name'),
                'grand_total' => $orderGrandTotal,
                'store_view_labels' => $this->prepareStoreViewLabels([$order->getData(OrderInterface::STORE_ID)]),
            ];
            $orderNumber++;
        }

        return $orders;
    }

    /**
     * Normalize created at date.
     *
     * @param string $createdAt
     * @return string
     */
    private function prepareCreatedAtDate(string $createdAt): string
    {
        $date = new \DateTime($createdAt);

        return $this->timezone->formatDateTime($date, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM);
    }

    /**
     * Normalize grand total.
     *
     * @param string $grandTotal
     * @param string|null $orderCurrencyCode
     * @return string
     */
    private function prepareGrandTotal(string $grandTotal, ?string $orderCurrencyCode = null): string
    {
        $resultGrandTotal = sprintf("%f", (float)$grandTotal * 1.0);
        $orderCurrencyCode = $orderCurrencyCode ?:
            $this->scopeConfig->getValue(Currency::XML_PATH_CURRENCY_BASE, 'default');

        return $this->currency->getCurrency($orderCurrencyCode)->toCurrency($resultGrandTotal);
    }

    /**
     * Normalize store ids.
     *
     * @param array $orderStoreIds
     * @return array
     */
    private function prepareStoreViewLabels(array $orderStoreIds): array
    {
        $result = [];
        $storeStructure = $this->store->getStoresStructure(false, $orderStoreIds);
        $textIndex = 0;
        foreach ($storeStructure as $website) {
            $textIndex++;
            foreach ($website['children'] as $group) {
                $textIndex++;
                foreach ($group['children'] as $store) {
                    $textIndex++;
                    $result[$textIndex] = $store['label'];
                }
            }
        }

        return $result;
    }
}
