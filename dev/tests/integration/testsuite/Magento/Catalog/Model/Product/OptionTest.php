<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Product repository.
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Custom option factory.
     *
     * @var ProductCustomOptionInterfaceFactory
     */
    private $customOptionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->customOptionFactory = Bootstrap::getObjectManager()->create(ProductCustomOptionInterfaceFactory::class);
    }

    /**
     * Tests removing ineligible characters from file_extension.
     *
     * @param string $rawExtensions
     * @param string $expectedExtensions
     * @dataProvider fileExtensionsDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     */
    public function testFileExtensions(string $rawExtensions, string $expectedExtensions)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');
        /** @var \Magento\Catalog\Model\Product\Option $fileOption */
        $fileOption = $this->createFileOption($rawExtensions);
        $product->addOption($fileOption);
        $product->save();
        $product = $this->productRepository->get('simple');
        $fileOption = $product->getOptions()[0];
        $actualExtensions = $fileOption->getFileExtension();
        $this->assertEquals($expectedExtensions, $actualExtensions);
    }

    /**
     * Data provider for testFileExtensions.
     *
     * @return array
     */
    public function fileExtensionsDataProvider()
    {
        return [
            ['JPG, PNG, GIF', 'jpg, png, gif'],
            ['jpg, jpg, jpg', 'jpg'],
            ['jpg, png, gif', 'jpg, png, gif'],
            ['jpg png gif', 'jpg, png, gif'],
            ['!jpg@png#gif%', 'jpg, png, gif'],
            ['jpg, png, 123', 'jpg, png, 123'],
            ['', ''],
        ];
    }

    /**
     * Create file type option for product.
     *
     * @param string $rawExtensions
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface|void
     */
    private function createFileOption(string $rawExtensions)
    {
        $data = [
            'title' => 'file option',
            'type' => 'file',
            'is_require' => true,
            'sort_order' => 3,
            'price' => 30.0,
            'price_type' => 'percent',
            'sku' => 'sku3',
            'file_extension' => $rawExtensions,
            'image_size_x' => 10,
            'image_size_y' => 20,
        ];

        return $this->customOptionFactory->create(['data' => $data]);
    }
}
