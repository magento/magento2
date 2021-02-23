<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Entity\Attribute\Frontend;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Checks Datetime attribute's frontend model
 *
 * @see \Magento\Eav\Model\Entity\Attribute\Frontend\Datetime
 */
class DatetimeTest extends TestCase
{
    /**
     * @var int
     */
    private const ONE_HOUR_IN_MILLISECONDS = 3600;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->attributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->dateTime = $this->objectManager->create(DateTime::class);
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     * @magentoDataFixture Magento/Catalog/_files/product_datetime_attribute.php
     *
     * @magentoConfigFixture default_store general/locale/timezone Europe/Moscow
     * @magentoConfigFixture fixture_second_store_store general/locale/timezone Europe/Kiev
     *
     * @return void
     */
    public function testFrontendValueOnDifferentWebsites(): void
    {
        $attribute = $this->attributeRepository->get('datetime_attribute');
        $product = $this->productRepository->get('simple-on-two-websites');
        $product->setDatetimeAttribute($this->dateTime->date('Y-m-d H:i:s'));
        $valueOnWebsiteOne = $attribute->getFrontend()->getValue($product);
        $secondStoreId = $this->storeManager->getStore('fixture_second_store')->getId();
        $this->storeManager->setCurrentStore($secondStoreId);
        $valueOnWebsiteTwo = $attribute->getFrontend()->getValue($product);
        $this->assertEquals(
            self::ONE_HOUR_IN_MILLISECONDS,
            $this->dateTime->gmtTimestamp($valueOnWebsiteOne) - $this->dateTime->gmtTimestamp($valueOnWebsiteTwo),
            'The difference between the two time zones are incorrect'
        );
    }
}
