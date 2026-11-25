<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Webapi;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Request as WebapiRequest;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[AppArea('webapi_rest')]
#[AppIsolation(true)]
class ProductOutputProcessorTest extends TestCase
{
    #[
        DataFixture(ProductFixture::class, as: 'linked'),
        DataFixture(
            ProductFixture::class,
            [
                'tier_prices' => [['customer_group_id' => 0, 'qty' => 2, 'value' => 5]],
                'product_links' => ['$linked.sku$'],
            ],
            as: 'omitted'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'tier_prices' => [['customer_group_id' => 0, 'qty' => 2, 'value' => 5]],
                'product_links' => ['$linked.sku$'],
            ],
            as: 'empty'
        )
    ]
    public function testEncodedProductsPreserveOmittedFieldsAndClearExplicitEmptyFields(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $repository = $objectManager->get(ProductRepositoryInterface::class);
        $omittedSku = $fixtures->get('omitted')->getSku();
        $emptySku = $fixtures->get('empty')->getSku();
        $requestData = [
            ['product' => ['sku' => $omittedSku, 'name' => 'Omitted fields']],
            ['product' => ['sku' => $emptySku, 'tier_prices' => [], 'product_links' => []]],
        ];
        $json = $objectManager->get(Json::class);
        $body = $json->serialize($requestData);
        $objectManager->get(WebapiRequest::class)->setContent($body);
        $request = $objectManager->get(Request::class);
        $request->setContent($body);
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $objectManager->get(ProductOutputProcessor::class)->_resetState();
        $encoder = $objectManager->get(MessageEncoder::class);
        $inputProcessor = $objectManager->get(ServiceInputProcessor::class);
        $topic = 'async.magento.catalog.api.productrepositoryinterface.save.post';
        foreach ($requestData as $item) {
            $arguments = $inputProcessor->process(ProductRepositoryInterface::class, 'save', $item);
            $encoded = $encoder->encode($topic, $arguments);
            $message = $json->unserialize($encoded);
            if ($item['product']['sku'] === $omittedSku) {
                $this->assertArrayNotHasKey('tier_prices', $message['product']);
                $this->assertArrayNotHasKey('product_links', $message['product']);
            } else {
                $this->assertSame([], $message['product']['tier_prices']);
                $this->assertSame([], $message['product']['product_links']);
            }
            $decoded = $encoder->decode($topic, $encoded);
            $repository->save($decoded['product']);
        }

        $omitted = $repository->get($omittedSku, false, null, true);
        $this->assertCount(1, $omitted->getTierPrices());
        $this->assertEquals(5, $omitted->getTierPrices()[0]->getValue());
        $this->assertCount(1, $omitted->getProductLinks());
        $this->assertSame($fixtures->get('linked')->getSku(), $omitted->getProductLinks()[0]->getLinkedProductSku());
        $empty = $repository->get($emptySku, false, null, true);
        $this->assertSame([], $empty->getTierPrices());
        $this->assertSame([], $empty->getProductLinks());
    }
}
