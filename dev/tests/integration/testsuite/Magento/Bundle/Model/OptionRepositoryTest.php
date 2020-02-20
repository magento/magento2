<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test option repository class
 */
class OptionRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->optionRepository = $this->objectManager->get(OptionRepository::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Bundle/_files/empty_bundle_product.php
     */
    public function testBundleProductIsSaleableAfterNewOptionSave()
    {
        $bundleProduct = $this->productRepository->get('bundle-product');

        /** @var OptionInterface $newOption */
        $newOption = $this->objectManager->create(OptionInterfaceFactory::class)->create();
        /** @var LinkInterface $productLink */
        $productLink = $this->objectManager->create(LinkInterfaceFactory::class)->create();

        $newOption->setTitle('new-option');
        $newOption->setRequired(true);
        $newOption->setType('select');
        $newOption->setSku($bundleProduct->getSku());

        $productLink->setSku('simple');
        $productLink->setQty(1);
        $productLink->setIsDefault(true);
        $productLink->setCanChangeQuantity(0);

        $newOption->setProductLinks([$productLink]);

        $optionId = $this->optionRepository->save($bundleProduct, $newOption);
        $bundleProduct = $this->productRepository->get($bundleProduct->getSku(), false, null, true);

        $this->assertNotNull($optionId, 'Bundle option was not saved correctly');
        $this->assertTrue($bundleProduct->isSaleable(), 'Bundle product should show as in stock once option is added');
    }
}
