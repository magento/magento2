<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var string
     */
    protected $productType = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var int
     */
    protected $attributeSetId;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var Product\Converter
     */
    protected $converter;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var Product\Gallery
     */
    protected $gallery;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * Product constructor.
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ConfigFactory $catalogConfig
     * @param Product\Converter $converter
     * @param Product\Gallery $gallery
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ConfigFactory $catalogConfig,
        Product\Converter $converter,
        Product\Gallery $gallery,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->productFactory = $productFactory;
        $this->catalogConfig = $catalogConfig->create();
        $this->converter = $converter;
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->gallery = $gallery;
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param array $productFixtures
     * @param array $galleryFixtures
     * @throws \Exception
     */
    public function install(array $productFixtures, array $galleryFixtures)
    {
        $this->eavConfig->clear();
        $this->setGalleryFixtures($galleryFixtures);

        $product = $this->productFactory->create();
        foreach ($productFixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;

                $attributeSetId = $this->catalogConfig->getAttributeSetId(4, $row['attribute_set']);
                $this->converter->setAttributeSetId($attributeSetId);
                $data = $this->converter->convertRow($row);
                $product->unsetData();
                $product->setData($data);
                $product
                    ->setTypeId($this->productType)
                    ->setAttributeSetId($attributeSetId)
                    ->setWebsiteIds([$this->storeManager->getDefaultStoreView()->getWebsiteId()])
                    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                    ->setStockData(['is_in_stock' => 1, 'manage_stock' => 0])
                    ->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

                if (empty($data['visibility'])) {
                    $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
                }

                $this->prepareProduct($product, $data);

                $product->save();
                $this->installGallery($product);
            }
        }
    }

    /**
     * Set fixtures for product images
     *
     * @param array $fixtures
     */
    protected function setGalleryFixtures(array $fixtures)
    {
        $this->gallery->setFixtures($fixtures);
    }

    /**
     * Store images for product to db
     *
     * @param $product
     */
    protected function installGallery($product)
    {
        $this->gallery->install($product);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $product
     * @param array $data
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function prepareProduct($product, $data)
    {
        return $this;
    }

    /**
     * Set fixtures
     *
     * @param array $fixtures
     * @return $this
     */
    public function setFixtures(array $fixtures)
    {
        $this->fixtures = $fixtures;
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $product
     * @return void
     */
    public function setVirtualStockData($product)
    {
        $product->setStockData(
            [
                'use_config_manage_stock' => 0,
                'is_in_stock' => 1,
                'manage_stock' => 0,
            ]
        );
    }
}
