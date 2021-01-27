<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\SpecialPriceInterface;
use Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test special price storage model
 */
class SpecialPriceStorageTest extends TestCase
{
    /**
     * @var SpecialPriceStorage
     */
    private $model;
    /**
     * @var SpecialPriceInterfaceFactory
     */
    private $specialPriceFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->get(SpecialPriceStorage::class);
        $this->specialPriceFactory = $objectManager->get(SpecialPriceInterfaceFactory::class);
    }

    /**
     * Test that price update validation works correctly
     *
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testUpdateValidationResult()
    {
        $date = new \Datetime('+2 days');
        $date->setTime(0, 0);
        /** @var SpecialPriceInterface $price */
        $price = $this->specialPriceFactory->create();
        $price->setSku('invalid')
            ->setStoreId(0)
            ->setPrice(5.0)
            ->setPriceFrom($date->format('Y-m-d H:i:s'))
            ->setPriceTo($date->modify('+1 day')->format('Y-m-d H:i:s'));
        $result = $this->model->update([$price]);
        $this->assertCount(1, $result);
        $this->assertStringContainsString('The product that was requested doesn\'t exist.', (string)$result[0]->getMessage());
        $price->setSku('simple333');
        $result = $this->model->update([$price]);
        $this->assertCount(0, $result);
    }
}
