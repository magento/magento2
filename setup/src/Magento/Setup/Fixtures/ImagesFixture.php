<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\ValidatorException;
use Magento\MediaStorage\Service\ImageResize;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate images per each product
 * Support next format:
 *  <product-images>
 *      <images-count>X</images-count>
 *      <images-per-product>Y</images-per-product>
 *  </product-images>
 *
 * Where
 *  X - number of images to be generated
 *  Y - number of images that will be assigned per each product
 *
 * note, that probably you would need to run command:
 *  php bin/magento catalog:images:resize
 * to resize images after generation but be patient with it
 * because it can take pretty much time
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImagesFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 51;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Setup\Fixtures\ImagesGenerator\ImagesGeneratorFactory
     */
    private $imagesGeneratorFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $mediaConfig;

    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $dbConnection;

    /**
     * @var \Magento\Framework\DB\Sql\ColumnValueExpressionFactory
     */
    private $expressionFactory;

    /**
     * @var \Magento\Setup\Model\BatchInsertFactory
     */
    private $batchInsertFactory;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var array
     */
    private $attributeCodesCache = [];

    /**
     * @var int
     */
    private $imagesInsertBatchSize = 1000;

    /**
     * @var int
     */
    private $productsSelectBatchSize = 1000;

    /**
     * @var int
     */
    private $productsCountCache;

    /**
     * @var array
     */
    private $tableCache = [];
    /**
     * @var ImageResize
     */
    private $imageResize;

    /**
     * @param FixtureModel $fixtureModel
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param ImagesGenerator\ImagesGeneratorFactory $imagesGeneratorFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Eav\Model\AttributeRepository $attributeRepository
     * @param \Magento\Framework\DB\Sql\ColumnValueExpressionFactory $expressionFactory
     * @param \Magento\Setup\Model\BatchInsertFactory $batchInsertFactory
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param ImageResize $imageResize
     */
    public function __construct(
        FixtureModel $fixtureModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Setup\Fixtures\ImagesGenerator\ImagesGeneratorFactory $imagesGeneratorFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Framework\DB\Sql\ColumnValueExpressionFactory $expressionFactory,
        \Magento\Setup\Model\BatchInsertFactory $batchInsertFactory,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        ImageResize $imageResize
    ) {
        parent::__construct($fixtureModel);

        $this->imagesGeneratorFactory = $imagesGeneratorFactory;
        $this->resourceConnection = $resourceConnection;
        $this->filesystem = $filesystem;
        $this->mediaConfig = $mediaConfig;
        $this->attributeRepository = $attributeRepository;
        $this->expressionFactory = $expressionFactory;
        $this->batchInsertFactory = $batchInsertFactory;
        $this->metadataPool = $metadataPool;
        $this->imageResize = $imageResize;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->checkIfImagesExists() && $this->getImagesToGenerate()) {
            $this->createImageEntities();
            $this->assignImagesToProducts();
            iterator_to_array($this->imageResize->resizeFromThemes(), false);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating images';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'product-images' => 'Product Images'
        ];
    }

    /**
     * {@inheritdoc}
     * @throws ValidatorException
     */
    public function printInfo(OutputInterface $output)
    {
        $config = $this->fixtureModel->getValue('product-images', []);
        if (!$config) {
            return;
        }

        if (!isset($config['images-count'])) {
            throw new ValidatorException(
                __("The amount of images to generate wasn't specified. Enter the amount and try again.")
            );
        }

        if (!isset($config['images-per-product'])) {
            throw new ValidatorException(
                __("The amount of images per product wasn't specified. Enter the amount and try again.")
            );
        }

        $output->writeln(
            sprintf(
                '<info> |- Product images: %s, %s per product</info>',
                $config['images-count'],
                $config['images-per-product']
            )
        );
    }

    /**
     * Check if DB already has any images
     *
     * @return bool
     */
    private function checkIfImagesExists()
    {
        return $this->getImagesCount() > 0;
    }

    /**
     * Create image file and add it to media gallery table in DB
     *
     * @return void
     */
    private function createImageEntities()
    {
        /** @var \Magento\Setup\Model\BatchInsert $batchInsert */
        $batchInsert = $this->batchInsertFactory->create([
            'insertIntoTable' => $this->getTable('catalog_product_entity_media_gallery'),
            'batchSize' => $this->imagesInsertBatchSize
        ]);

        foreach ($this->generateImageFilesGenerator() as $imageName) {
            $batchInsert->insert([
                'attribute_id' => $this->getAttributeId('media_gallery'),
                'value' => $imageName,
            ]);
        }

        $batchInsert->flush();
    }

    /**
     * Create generator that creates image files and puts them to appropriate media folder
     * in memory-safe way
     *
     * @return \Generator
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function generateImageFilesGenerator()
    {
        /** @var \Magento\Setup\Fixtures\ImagesGenerator\ImagesGenerator $imagesGenerator */
        $imagesGenerator = $this->imagesGeneratorFactory->create();
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $productImagesDirectoryPath = $mediaDirectory->getRelativePath($this->mediaConfig->getBaseMediaPath());

        for ($i = 1; $i <= $this->getImagesToGenerate(); $i++) {
            $imageName = md5($i) . '.jpg';
            $imageFullName = DIRECTORY_SEPARATOR . substr($imageName, 0, 1)
                . DIRECTORY_SEPARATOR . substr($imageName, 1, 1)
                . DIRECTORY_SEPARATOR . $imageName;

            $imagePath = $imagesGenerator->generate([
                'image-width' => 300,
                'image-height' => 300,
                'image-name' => $imageName
            ]);

            $mediaDirectory->renameFile(
                $mediaDirectory->getRelativePath($imagePath),
                $productImagesDirectoryPath . $imageFullName
            );

            yield $imageFullName;
        }
    }

    /**
     * Assign created images to products according to Y images per each product
     *
     * @return void
     * @throws \Exception
     */
    private function assignImagesToProducts()
    {
        /** @var \Magento\Setup\Model\BatchInsert $batchInsertCatalogProductEntityVarchar */
        $batchInsertCatalogProductEntityVarchar = $this->batchInsertFactory->create([
            'insertIntoTable' => $this->getTable('catalog_product_entity_varchar'),
            'batchSize' => $this->imagesInsertBatchSize
        ]);

        /** @var \Magento\Setup\Model\BatchInsert $batchInsertCatalogProductEntityMediaGalleryValue */
        $batchInsertCatalogProductEntityMediaGalleryValue = $this->batchInsertFactory->create([
            'insertIntoTable' => $this->getTable('catalog_product_entity_media_gallery_value'),
            'batchSize' => $this->imagesInsertBatchSize
        ]);

        /** @var \Magento\Setup\Model\BatchInsert $batchInsertCatalogProductEntityMediaGalleryValueToEntity */
        $batchInsertCatalogProductEntityMediaGalleryValueToEntity = $this->batchInsertFactory->create([
            'insertIntoTable' => $this->getTable('catalog_product_entity_media_gallery_value_to_entity'),
            'batchSize' => $this->imagesInsertBatchSize
        ]);

        $imageGenerator = $this->getImagesGenerator();

        foreach ($this->getProductGenerator() as $productEntity) {
            for ($imageNum = 1; $imageNum <= $this->getImagesPerProduct(); $imageNum++) {
                $image = $imageGenerator->current();
                $imageGenerator->next();

                if ($imageNum === 1) {
                    $attributes = ['image', 'small_image', 'thumbnail', 'swatch_image'];
                    foreach ($attributes as $attr) {
                        $batchInsertCatalogProductEntityVarchar->insert([
                            $this->getProductLinkField() => $productEntity[$this->getProductLinkField()],
                            'attribute_id' => $this->getAttributeId($attr),
                            'value' => $image['value'],
                            'store_id' => 0,
                        ]);
                    }
                }

                $batchInsertCatalogProductEntityMediaGalleryValueToEntity->insert([
                    'value_id' => $image['value_id'],
                    $this->getProductLinkField() => $productEntity[$this->getProductLinkField()]
                ]);

                $batchInsertCatalogProductEntityMediaGalleryValue->insert([
                    'value_id' => $image['value_id'],
                    'store_id' => 0,
                    $this->getProductLinkField() => $productEntity[$this->getProductLinkField()],
                    'position' => $image['value_id'],
                    'disabled' => 0
                ]);
            }
        }

        $batchInsertCatalogProductEntityVarchar->flush();
        $batchInsertCatalogProductEntityMediaGalleryValue->flush();
        $batchInsertCatalogProductEntityMediaGalleryValueToEntity->flush();
    }

    /**
     * Returns generator to iterate in memory-safe way over all product entities in DB
     *
     * @return \Generator
     * @throws \Exception
     */
    private function getProductGenerator()
    {
        $offset = 0;

        $products = $this->getProducts($this->productsSelectBatchSize, $offset);
        $offset += $this->productsSelectBatchSize;

        while (true) {
            yield current($products);

            if (next($products) === false) {
                $products = $this->getProducts($this->productsSelectBatchSize, $offset);
                $offset += $this->productsSelectBatchSize;

                if (empty($products)) {
                    break;
                }
            }
        }
    }

    /**
     * Get products entity ids
     *
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    private function getProducts($limit, $offset)
    {
        $select = $this->getDbConnection()
            ->select()
            ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
            ->columns([$this->getProductLinkField()])
            ->limit($limit, $offset);

        return $this->getDbConnection()->fetchAssoc($select);
    }

    /**
     * Creates generator to iterate infinitely over all image entities
     *
     * @return \Generator
     */
    private function getImagesGenerator()
    {
        $select = $this->getDbConnection()
            ->select()
            ->from(
                $this->getTable('catalog_product_entity_media_gallery'),
                ['value_id', 'value']
            )->order('value_id desc')
            ->limit($this->getProductsCount() * $this->getImagesPerProduct());

        $images = $this->getDbConnection()->fetchAssoc($select);

        while (true) {
            yield current($images);

            if (next($images) === false) {
                reset($images);
            }
        }
    }

    /**
     * Return number of images to create
     *
     * @return null|int
     */
    private function getImagesToGenerate()
    {
        $config = $this->fixtureModel->getValue('product-images', []);

        return $config['images-count'] ?? null;
    }

    /**
     * Return number of images to be assigned per each product
     *
     * @return null|int
     */
    private function getImagesPerProduct()
    {
        $config = $this->fixtureModel->getValue('product-images', []);

        return $config['images-per-product'] ?? null;
    }

    /**
     * Get amount of existing products
     *
     * @return int
     */
    private function getProductsCount()
    {
        if ($this->productsCountCache === null) {
            $select = $select = $this->getDbConnection()
                ->select()
                ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
                ->columns([
                    'count' => $this->expressionFactory->create([
                        'expression' => 'COUNT(*)'
                    ])
                ]);

            $this->productsCountCache = (int) $this->getDbConnection()->fetchOne($select);
        }

        return $this->productsCountCache;
    }

    /**
     * Get amount of existing images
     *
     * @return int
     */
    private function getImagesCount()
    {
        $select = $select = $this->getDbConnection()
            ->select()
            ->from(['product_entity' => $this->getTable('catalog_product_entity_media_gallery')], [])
            ->columns([
                'count' => $this->expressionFactory->create([
                    'expression' => 'COUNT(*)'
                ])
            ])->where('media_type="image"');

        return (int) $this->getDbConnection()->fetchOne($select);
    }

    /**
     * Get eav attribute id by its code
     *
     * @param string $attributeCode
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttributeId($attributeCode)
    {
        if (!isset($this->attributeCodesCache[$attributeCode])) {
            $attribute = $this->attributeRepository->get(
                'catalog_product',
                $attributeCode
            );

            $this->attributeCodesCache[$attributeCode] = $attribute->getAttributeId();
        }

        return $this->attributeCodesCache[$attributeCode];
    }

    /**
     * Retrieve current connection to DB
     *
     * Method is required to eliminate multiple calls to ResourceConnection class
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getDbConnection()
    {
        if ($this->dbConnection === null) {
            $this->dbConnection = $this->resourceConnection->getConnection();
        }

        return $this->dbConnection;
    }

    /**
     * Retrieve real table name
     *
     * Method act like a cache for already retrieved table names
     * is required to eliminate multiple calls to ResourceConnection class
     *
     * @param string $tableName
     * @return string
     */
    private function getTable($tableName)
    {
        if (!isset($this->tableCache[$tableName])) {
            $this->tableCache[$tableName] = $this->resourceConnection->getTableName($tableName);
        }

        return $this->tableCache[$tableName];
    }

    /**
     * Return product id field name - entity_id|row_id
     *
     * @return string
     * @throws \Exception
     */
    private function getProductLinkField()
    {
        return $this->metadataPool
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
    }
}
