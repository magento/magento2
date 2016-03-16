<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSampleData\Model\Product;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media as GalleryAttribute;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Gallery
 */
class Gallery
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var array
     */
    protected $images;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var false|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected $eavConfig;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param ProductFactory $productFactory
     * @param GalleryAttribute $galleryAttribute
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        ProductFactory $productFactory,
        GalleryAttribute $galleryAttribute,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->galleryAttribute = $galleryAttribute;
        $this->productFactory = $productFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param $product
     */
    public function install($product)
    {
        if (!empty($this->images[$product->getSku()])) {
            $this->storeImage($product, $this->images[$product->getSku()]);
        } else {
            $this->errors[] = $product->getSku();
        }
    }

    /**
     * Set fixtures
     *
     * @param array $fixtures
     * @return void
     */
    public function setFixtures(array $fixtures)
    {
        $this->images = [];
        foreach ($fixtures as $fileName) {
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
                $this->images[$data['sku']][] = $data['image'];
            }
        }
    }

    /**
     * Save image information to DB.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $images
     * @return void
     */
    protected function storeImage($product, $images)
    {
        $baseImage = '';
        $i = 1;
        $mediaAttribute = $this->eavConfig->getAttribute('catalog_product', 'media_gallery');
        foreach ($images as $image) {
            if (empty($image)) {
                $this->errors[] = $product->getSku();
                continue;
            }
            if (strpos($image, '_main') !== false) {
                $baseImage = $image;
            }
            $id = $this->galleryAttribute->insertGallery([
                'attribute_id' => $mediaAttribute->getAttributeId(),
                'entity_id' => $product->getId(),
                'value' => $image,
            ]);
            $this->galleryAttribute->insertGalleryValueInStore([
                'value_id' => $id,
                'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                'entity_id' => $product->getId(),
                'label' => 'Image',
                'position' => $i,
                'disables' => 0,
            ]);
            $this->galleryAttribute->bindValueToEntity($id, $product->getId());
            $i++;
        }

        if (empty($baseImage)) {
            $baseImage = $images[0];
        }

        if ($baseImage) {
            $imageAttribute = $product->getResource()->getAttribute('image');
            $smallImageAttribute = $product->getResource()->getAttribute('small_image');
            $thumbnailAttribute = $product->getResource()->getAttribute('thumbnail');
            $adapter = $product->getResource()->getConnection();
            foreach ([$imageAttribute, $smallImageAttribute, $thumbnailAttribute] as $attribute) {
                $table = $imageAttribute->getBackend()->getTable();
                /** @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter*/
                $data = [
                    $attribute->getBackend()->getEntityIdField() => $product->getId(),
                    'attribute_id' => $attribute->getId(),
                    'value' => $baseImage,
                ];
                $adapter->insertOnDuplicate($table, $data, ['value']);
            }
        }
    }
}
