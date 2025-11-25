<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend\WebsiteSpecific;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\ClearQueueProcessor;
use PHPUnit\Framework\TestCase;

#[
    AppArea('adminhtml'),
]
class ValueSynchronizerTest extends TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $clearQueueProcessor = $objectManager->get(ClearQueueProcessor::class);
        $clearQueueProcessor->execute('catalog_website_attribute_value_sync');
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    protected function tearDown(): void
    {
        $store = Bootstrap::getObjectManager()->create(Store::class);
        $store->load('store2', 'code');
        if ($store->getId()) {
            $store->delete();
        }
    }

    #[
        DbIsolation(false),
        DataFixture(ProductFixture::class, ['sku' => 'prod1']),
    ]
    public function testProcess(): void
    {
        $defaultStore = $this->storeManager->getStore('default');
        $product = $this->productRepository->get('prod1', true, $defaultStore->getId());
        $product->setStatus(Status::STATUS_DISABLED);
        $this->productRepository->save($product);

        $secondStore = Bootstrap::getObjectManager()->create(Store::class);
        $secondStore->setName('Second store')
            ->setCode('store2')
            ->setStoreGroupId($defaultStore->getStoreGroupId())
            ->setWebsiteId($defaultStore->getWebsiteId())
            ->setIsActive(1);
        $secondStore->save();
        $this->storeManager->reinitStores();

        $consumerFactory = Bootstrap::getObjectManager()->get(ConsumerFactory::class);
        $consumer = $consumerFactory->get('catalog_website_attribute_value_sync');
        $consumer->process(1);

        $product = $this->productRepository->get('prod1', false, $secondStore->getId(), true);
        self::assertEquals(Status::STATUS_DISABLED, $product->getStatus());
    }
}
