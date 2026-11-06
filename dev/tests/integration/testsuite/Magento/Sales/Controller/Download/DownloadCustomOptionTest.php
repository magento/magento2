<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Download;

use Magento\Catalog\Model\Product\Option\UrlBuilder;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option as OptionResource;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Sales\Model\Download;
use Magento\Sales\Model\ResourceModel\Order\Item as ItemResource;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Test\Fixture\PlaceOrderWithCustomerOrGuest;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadCustomOptionTest extends TestCase
{
    #[AppArea('frontend')]
    #[DbIsolation(true)]
    #[DataFixture(
        ProductFixture::class,
        ['options' => [['type' => 'file', 'title' => 'Attachment', 'is_require' => 0]]],
        'product'
    )]
    #[DataFixture(GuestCart::class, as: 'cart')]
    #[DataFixture(
        AddProductToCart::class,
        ['cart_id' => '$cart.id$', 'product_id' => '$product.id$'],
        'quote_item'
    )]
    #[DataFixture(
        PlaceOrderWithCustomerOrGuest::class,
        ['customer_is_guest' => true, 'items' => [['sku' => '$product.sku$']]],
        'order'
    )]
    public function testGeneratedOrderLinkSurvivesQuoteDeletion(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $product = $fixtures->get('product');
        $quoteItem = $fixtures->get('quote_item');
        $productOption = array_values($product->getOptions())[0];
        $optionId = $productOption->getId();
        $code = 'option_' . $optionId;
        $serializer = $objectManager->get(Json::class);
        $value = [
            'secret_key' => 'download-secret',
            'title' => 'sample.txt',
            'type' => 'text/plain',
            'quote_path' => 'custom_options/quote/sample.txt',
            'order_path' => 'custom_options/order/sample.txt',
        ];
        $option = $objectManager->create(Option::class);
        $option->setItem($quoteItem)->setProduct($product)->setCode($code)
            ->setValue($serializer->serialize($value));
        $objectManager->get(OptionResource::class)->save($option);
        $optionIdInQuote = $option->getId();
        $product->addCustomOption('option_ids', (string)$optionId);
        $customOptions = $product->getCustomOptions();
        $customOptions[$code] = $option;
        $product->setCustomOptions($customOptions);
        $orderOptions = $product->getTypeInstance()->getOrderOptions($product);
        $info = $serializer->unserialize($orderOptions['options'][0]['option_value']);
        $params = $info['url']['params'];
        $this->assertEquals([
            'id' => $optionIdInQuote,
            'quote' => $quoteItem->getQuoteId(),
            'item' => $quoteItem->getId(),
            'option' => $optionId,
            'key' => $value['secret_key'],
        ], $params);
        $url = $objectManager->get(UrlBuilder::class)->getUrl($info['url']['route'], $params);
        $this->assertStringContainsString($url, $orderOptions['options'][0]['value']);
        $order = $fixtures->get('order');
        $order->setQuoteId($quoteItem->getQuoteId());
        $objectManager->get(OrderResource::class)->save($order);
        $orderItem = array_values($order->getAllItems())[0];
        $orderItem->setQuoteItemId($quoteItem->getId())->setProductOptions($orderOptions);
        $objectManager->get(ItemResource::class)->save($orderItem);
        $response = $this->createStub(ResponseInterface::class);
        $download = $this->createMock(Download::class);
        $expectedValues = [$value, $info];
        $download->expects($this->exactly(2))->method('createResponse')->willReturnCallback(
            function (array $actual) use (&$expectedValues, $response) {
                $this->assertSame(array_shift($expectedValues), $actual);
                return $response;
            }
        );
        $objectManager->get(RequestInterface::class)->setParams($params);
        $controller = $objectManager->create(DownloadCustomOption::class, ['download' => $download]);
        $this->assertSame($response, $controller->execute());
        $objectManager->get(QuoteResource::class)->delete($fixtures->get('cart'));
        $deletedOption = $objectManager->create(Option::class);
        $objectManager->get(OptionResource::class)->load($deletedOption, $optionIdInQuote);
        $this->assertNull($deletedOption->getId());
        $this->assertSame($response, $controller->execute());
    }
}
