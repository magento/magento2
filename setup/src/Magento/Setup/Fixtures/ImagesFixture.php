<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Framework\App\Filesystem\DirectoryList;

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
     * @var array
     */
    private $attributeCodesCache = [];

    /**
     * @var int
     */
    private $imagesInsertBatchSize = 3000;

    /**
     * @var array
     */
    private $tableCache = [];

    public function __construct(
        FixtureModel $fixtureModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Setup\Fixtures\ImagesGenerator\ImagesGeneratorFactory $imagesGeneratorFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Framework\DB\Sql\ColumnValueExpressionFactory $expressionFactory,
        \Magento\Setup\Model\BatchInsertFactory $batchInsertFactory
    ) {
        parent::__construct($fixtureModel);

        $this->imagesGeneratorFactory = $imagesGeneratorFactory;
        $this->resourceConnection = $resourceConnection;
        $this->filesystem = $filesystem;
        $this->mediaConfig = $mediaConfig;
        $this->attributeRepository = $attributeRepository;
        $this->expressionFactory = $expressionFactory;
        $this->batchInsertFactory = $batchInsertFactory;
    }

    public function execute() {
        $this->createImageEntities();
        $this->assignImagesToProducts();
    }

    public function getActionTitle() {
       return 'Generating images';
    }

    public function introduceParamLabels() {
        return [
            'images' => 'Images'
        ];
    }

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

    private function assignImagesToProducts()
    {
        $imageGenerator = $this->getImagesGenerator();
        $productsLimit = $this->getProductsIncrement();

        for ($offset = 0; $offset <= $this->getProductsCount(); $offset += $productsLimit) {
            for ($imageNum = 1; $imageNum <= $this->getImagesPerProduct(); $imageNum++) {
                $image = $imageGenerator->current();
                $imageGenerator->next();

                if ($imageNum == 1) {
                    $attrs = ['image', 'small_image', 'thumbnail', 'swatch_image'];
                    foreach ($attrs as $attr) {
                        $columns = [
                            'entity_id' => 'product_entity.entity_id',
                            'attribute_id' => $this->expressionFactory->create([
                                'expression' => $this->getAttributeId($attr)
                            ]),
                            'value' => $this->expressionFactory->create([
                                'expression' => $this->getDbConnection()->quoteInto('?', $image['value'])
                            ]),
                            'store_id' => $this->expressionFactory->create([
                                'expression' => 0
                            ]),
                        ];

                        $select = $this->getDbConnection()
                            ->select()
                            ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
                            ->columns($columns)
                            ->limit($productsLimit, $offset);

                        $this->getDbConnection()->query(
                            $select->insertFromSelect(
                                $this->getTable('catalog_product_entity_varchar'),
                                array_keys($columns)
                            )
                        );
                    }
                }

                $columns = [
                    'value_id' => $this->expressionFactory->create([
                        'expression' => $image['value_id']
                    ]),
                    'entity_id' => 'product_entity.entity_id'
                ];

                $select = $this->getDbConnection()
                    ->select()
                    ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
                    ->columns($columns)
                    ->limit($productsLimit, $offset);

                $this->getDbConnection()->query(
                    $select->insertFromSelect(
                        $this->getTable('catalog_product_entity_media_gallery_value_to_entity'),
                        array_keys($columns)
                    )
                );

                $columns = [
                    'value_id' => $this->expressionFactory->create([
                        'expression' => $image['value_id']
                    ]),
                    'store_id' => $this->expressionFactory->create([
                        'expression' => 0
                    ]),
                    'entity_id' => 'product_entity.entity_id',
                    'position' => $this->expressionFactory->create([
                        'expression' => 1
                    ]),
                    'disabled' => $this->expressionFactory->create([
                        'expression' => 0
                    ])
                ];

                $select = $this->getDbConnection()
                    ->select()
                    ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
                    ->columns($columns)
                    ->limit($productsLimit, $offset);

                $this->getDbConnection()->query(
                    $select->insertFromSelect(
                        $this->getTable('catalog_product_entity_media_gallery_value'),
                        array_keys($columns)
                    )
                );
            }
        }
    }

    private function _assignImagesToProducts(array $images)
    {
        $imagesPerProduct = 3;
        foreach ($images as $image) {
            $columns = [
                'value_id' => $this->expressionFactory->create([
                    'expression' => $image['value_id']
                ]),
                'entity_id' => 'product_entity.entity_id'
            ];

            $select = $this->getDbConnection()
                ->select()
                ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
                ->columns($columns);

            $this->getDbConnection()->query(
                $select->insertFromSelect(
                    $this->getTable('catalog_product_entity_media_gallery_value_to_entity'),
                    array_keys($columns)
                )
            );

            $columns = [
                'value_id' => $this->expressionFactory->create([
                    'expression' => $image['value_id']
                ]),
                'store_id' => $this->expressionFactory->create([
                    'expression' => 0
                ]),
                'entity_id' => 'product_entity.entity_id',
                'position' => $this->expressionFactory->create([
                    'expression' => 1
                ]),
                'disabled' => $this->expressionFactory->create([
                    'expression' => 0
                ])
            ];

            $select = $this->getDbConnection()
                ->select()
                ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
                ->columns($columns);

            $this->getDbConnection()->query(
                $select->insertFromSelect(
                    $this->getTable('catalog_product_entity_media_gallery_value'),
                    array_keys($columns)
                )
            );

            $attrs = ['image', 'small_image', 'thumbnail', 'swatch_image'];
            foreach ($attrs as $attr) {
                $columns = [
                    'entity_id' => 'product_entity.entity_id',
                    'attribute_id' => $this->expressionFactory->create([
                        'expression' => $this->getAttributeId($attr)
                    ]),
                    'value' => $this->expressionFactory->create([
                        'expression' => $this->getDbConnection()->quoteInto('?', $image['value'])
                    ]),
                    'store_id' => $this->expressionFactory->create([
                        'expression' => 0
                    ]),
                ];

                $select = $this->getDbConnection()
                    ->select()
                    ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
                    ->columns($columns);

                $this->getDbConnection()->query(
                    $select->insertFromSelect(
                        $this->getTable('catalog_product_entity_varchar'),
                        array_keys($columns)
                    )
                );
            }
        }
    }

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

    private function getImagesToGenerate()
    {
        return 10;
    }

    private function getImagesPerProduct()
    {
        return 2;
    }

    private function getProductsCount()
    {
        $select = $select = $this->getDbConnection()
            ->select()
            ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
            ->columns([
                'count' => $this->expressionFactory->create([
                    'expression' => 'COUNT(1)'
                ])
            ]);

        return (int) $this->getDbConnection()->fetchOne($select);
    }

    private function getProductsIncrement()
    {
        return floor($this->getProductsCount() / ($this->getImagesToGenerate() / $this->getImagesPerProduct()));
    }

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

}
