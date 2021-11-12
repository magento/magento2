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
use Magento\TestFramework\Store\ExecuteInStoreContext;
use PHPUnit\Framework\TestCase;

/**
 * Checks Datetime attribute's frontend model
 *
 * @magentoAppArea frontend
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
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ExecuteInStoreContext
     */
    private $executeInStoreContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->attributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->dateTime = $this->objectManager->create(DateTime::class);
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * @magentoDbIsolation disabled
     *
     * @magentoDataFixture Magento/Catalog/_files/product_two_websites.php
     * @magentoDataFixture Magento/Catalog/_files/product_datetime_attribute.php
     *
     * @magentoConfigFixture default_store general/locale/timezone Asia/Tokyo
     * @magentoConfigFixture fixture_second_store_store general/locale/timezone Asia/Shanghai
     *
     * @return void
     */
    public function testFrontendValueOnDifferentWebsites(): void
    {
        $attribute = $this->attributeRepository->get('datetime_attribute');
        $product = $this->productRepository->get('simple-on-two-websites');
        $product->setDatetimeAttribute($this->dateTime->date('Y-m-d H:i:s'));
        $firstWebsiteValue = $this->executeInStoreContext->execute(
            'default',
            [$attribute->getFrontend(), 'getValue'],
            $product
        );
        $secondWebsiteValue = $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$attribute->getFrontend(), 'getValue'],
            $product
        );
        $this->assertEquals(
            self::ONE_HOUR_IN_MILLISECONDS,
            $this->dateTime->gmtTimestamp($firstWebsiteValue) - $this->dateTime->gmtTimestamp($secondWebsiteValue),
            'The difference between values per different timezones is incorrect'
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }
}
