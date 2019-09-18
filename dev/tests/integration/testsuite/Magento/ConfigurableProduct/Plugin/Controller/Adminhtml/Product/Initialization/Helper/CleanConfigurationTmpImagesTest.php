<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Controller\Adminhtml\Product\Initialization\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\ObjectManagerInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Eav\Api\Data\AttributeInterface;

/**
 * @magentoAppArea adminhtml
 */
class CleanConfigurationTmpImagesTest extends AbstractBackendController
{
    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var  Config
     */
    private $config;

    /**
     * @var string[]
     */
    private $newGeneratedSimpleProducts = [];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->mediaDirectory = $this->objectManager->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->eavConfig = $this->objectManager->get(EavConfig::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->config = $this->objectManager->get(Config::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        foreach ($this->newGeneratedSimpleProducts as $sku) {
            try {
                $this->productRepository->deleteById($sku);
            } catch (NoSuchEntityException $e) {
                //Product already removed
            }
        }
    }

    /**
     * Test for save configurable product with new generated simple product and images
     *
     * @return void
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/associated_products.php
     */
    public function testSaveConfigurableProductWithNewGeneratedSimpleAndImages()
    {
        // Model accepts only files in tmp media path, we need to copy fixture file there
        $mediaFile = $this->copyFileToBaseTmpMediaPath(dirname(__DIR__, 7) . '/Catalog/_files/magento_image.jpg');
        $associatedProductIds = ['3', '14', '15', '92'];
        $associatedProductIdsJSON = json_encode($associatedProductIds);
        $this->getRequest()
            ->setMethod(HttpRequest::METHOD_POST);
        $dataMatrix = array_merge(
            $this->getNonChangesData($associatedProductIds),
            [$this->getNewProductVariation('qwerty')],
            [$this->getNewProductVariation('qwerty')]
        );
        $this->getRequest()
            ->setPostValue(
                [
                    'id' => 1,
                    'attributes' => [$this->getConfigurableAttribute()->getId()],
                    'associated_product_ids_serialized' => $associatedProductIdsJSON,
                    'configurable-matrix-serialized' =>  json_encode($dataMatrix),
                ]
            );

        $this->dispatch('backend/catalog/product/save');

        /** @var ProductRepositoryInterface $product */
        $product = $this->productRepository->getById(1, false, null, true);
        $configurableProductLinks = array_values($product->getExtensionAttributes()->getConfigurableProductLinks());

        self::assertCount(
            6,
            $configurableProductLinks,
            'Product links are not available in the database'
        );
        self::assertFalse($this->mediaDirectory->isExist($this->mediaDirectory->getRelativePath($mediaFile)));
    }

    /**
     * Copy file to media tmp directory and return it's name
     *
     * @param string $sourceFile
     *
     * @return string
     */
    private function copyFileToBaseTmpMediaPath(string $sourceFile): string
    {
        $this->mediaDirectory->create($this->config->getBaseTmpMediaPath());
        $targetFile = $this->config->getTmpMediaPath(basename($sourceFile));
        copy($sourceFile, $this->mediaDirectory->getAbsolutePath($targetFile));

        return $targetFile;
    }

    /**
     * Get non changes for configurations matrix
     *
     * @param array $productIds
     *
     * @return array
     */
    private function getNonChangesData(array $productIds): array
    {
        $data = [];
        $defaultData = $this->getDefaultData();
        foreach ($productIds as $key => $productId) {
            $data[$key] = $defaultData;
            $data[$key]['id'] = $productId;
        }

        return $data;
    }

    /**
     * Get default data for configurations matrix
     *
     * @return array
     */
    private function getDefaultData(): array
    {
        return [
            'newProduct' => false,
            'was_changed' => true,
            'media_gallery' => [
                'images' => [
                    [
                        'file' => 'magento_image.jpg',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get new data for configurations matrix
     *
     * @param string $imagesKey
     *
     * @return array
     */
    private function getNewProductVariation(string $imagesKey): array
    {
        $sku = uniqid();
        $this->newGeneratedSimpleProducts[] = $sku;
        return [
            'variationKey' => uniqid(),
            'newProduct' => true,
            'name' => uniqid(),
            'configurable_attribute' => '{"configurable_attribute":"23"}',
            'price' => '3',
            'sku' => $sku,
            'quantity_and_stock_status' => ['qty' => ''],
            'media_gallery' => [
                'images' => [
                    $imagesKey => [
                        'file' => 'magento_image.jpg',
                    ],
                ],
            ]
        ];
    }

    /**
     * Retrieve configurable attribute instance
     *
     * @return AttributeInterface
     */
    private function getConfigurableAttribute(): AttributeInterface
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, 'test_configurable');
    }
}
