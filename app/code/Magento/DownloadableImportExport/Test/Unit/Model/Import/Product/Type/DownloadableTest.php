<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\DownloadableImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as ProductAttributeCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Uploader;
use Magento\Downloadable\Model\Url\DomainValidator;
use Magento\DownloadableImportExport\Helper\Data;
use Magento\DownloadableImportExport\Helper\Uploader as UploaderHelper;
use Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as AttributeOptionCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Phrase;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DownloadableTest for downloadable products import
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadableTest extends AbstractImportTestCase
{
    use MockCreationTrait;

    /**
     * @var Mysql|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var AttributeSetCollectionFactory|MockObject
     */
    private $attrSetColFacMock;

    /**
     * @var ProductAttributeCollectionFactory|MockObject
     */
    private $prodAttrColFacMock;

    /**
     * @var DomainValidator|MockObject
     */
    private $domainValidator;

    /**
     * @var ProductAttributeCollection|MockObject
     */
    private $prodAttrColMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var Product|MockObject
     */
    private $entityModelMock;

    /**
     * @var array|mixed
     */
    private $paramsArray;

    /**
     * @var Uploader|MockObject
     */
    private $uploaderMock;

    /**
     * @var Write|MockObject
     */
    private $directoryWriteMock;

    /**
     * @var UploaderHelper|MockObject
     */
    private $uploaderHelper;

    /**
     * @var Data|MockObject
     */
    private $downloadableHelper;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var AttributeOptionCollectionFactory|MockObject
     */
    private $attributeOptionCollectionFactory;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        parent::setUp();

        //connection and sql query results
        $this->select = $this->createMock(Select::class);
        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();
        $this->select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $adapter = $this->createMock(Mysql::class);
        $adapter->method('quoteInto')->willReturn('query');
        $this->select->method('getAdapter')->willReturn($adapter);
        
        $this->connectionMock = $this->createPartialMockWithReflection(
            Mysql::class,
            ['select', 'fetchAll', 'quoteInto', 'delete', 'insertOnDuplicate']
        );
        $this->connectionMock->method('select')->willReturn($this->select);
        $this->connectionMock->method('quoteInto')->willReturn('');

        //constructor arguments:
        // 1. $attrSetColFac
        $this->attrSetColFacMock = $this->createMock(AttributeSetCollectionFactory::class);

        // 2. $prodAttrColFac
        $this->prodAttrColFacMock = $this->createMock(ProductAttributeCollectionFactory::class);
        $this->prodAttrColMock = $this->createMock(ProductAttributeCollection::class);
        $this->prodAttrColMock->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $this->prodAttrColMock->method('getItems')->willReturn([]);
        $this->prodAttrColFacMock->method('create')->willReturn($this->prodAttrColMock);

        // 3. $resource
        $this->resourceMock = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->resourceMock->method('getConnection')->willReturn(
            $this->connectionMock
        );
        $this->resourceMock->method('getTableName')->willReturn(
            'tableName'
        );

        // 4. $params
        $this->entityModelMock = $this->createPartialMock(Product::class, [
            'addMessageTemplate',
            'getEntityTypeId',
            'getBehavior',
            'getNewSku',
            'getNextBunch',
            'isRowAllowedToImport',
            'getParameters',
            'addRowError'
        ]);

        $this->entityModelMock->expects($this->any())->method('addMessageTemplate')->willReturnSelf();
        $this->entityModelMock->method('getEntityTypeId')->willReturn(5);
        $this->entityModelMock->method('getParameters')->willReturn([]);
        $this->paramsArray = [
            $this->entityModelMock,
            'downloadable'
        ];

        $this->uploaderMock = $this->createPartialMock(
            Uploader::class,
            ['move', 'setTmpDir', 'setDestDir']
        );

        // 6. $filesystem
        $this->directoryWriteMock = $this->createMock(Write::class);

        // 7. $fileHelper
        $this->uploaderHelper = $this->createPartialMock(
            UploaderHelper::class,
            ['getUploader', 'isFileExist']
        );
        $this->uploaderHelper->method('getUploader')->willReturn($this->uploaderMock);
        $this->downloadableHelper = $this->createPartialMock(
            Data::class,
            ['prepareDataForSave', 'fillExistOptions']
        );
        $this->downloadableHelper->method('prepareDataForSave')->willReturn([]);
        $this->domainValidator = $this->createMock(DomainValidator::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $productMetadata = $this->createMock(EntityMetadataInterface::class);
        $productMetadata->method('getLinkField')->willReturn('entity_id');
        $this->metadataPoolMock->method('getMetadata')->willReturnMap(
            [
                [ProductInterface::class, $productMetadata],
            ]
        );
        $this->attributeOptionCollectionFactory = $this->createMock(AttributeOptionCollectionFactory::class);
    }

    /**
     * @return void
     */
    #[DataProvider('dataForSave')]
    public function testSaveDataAppend($newSku, $bunch, $allowImport, $fetchResult): void
    {
        $this->entityModelMock->expects($this->once())->method('getNewSku')->willReturn($newSku);
        $this->entityModelMock
            ->method('getNextBunch')
            ->willReturnOnConsecutiveCalls(null, $bunch, null);
        $this->entityModelMock->method('isRowAllowedToImport')->willReturn($allowImport);

        $this->uploaderMock->method('setTmpDir')->willReturn(true);
        $this->uploaderMock->expects($this->any())->method('setDestDir')->with('pub/media/')->willReturn(true);

        // Configure connection mock for consecutive fetchAll calls
        $this->connectionMock->method('fetchAll')->willReturnOnConsecutiveCalls(
            [
                [
                    'attribute_set_name' => '1',
                    'attribute_id' => '1'
                ],
                [
                    'attribute_set_name' => '2',
                    'attribute_id' => '2'
                ]
            ],
            $fetchResult['sample'],
            $fetchResult['sample'],
            $fetchResult['link'],
            $fetchResult['link']
        );

        $downloadableModel = new Downloadable(
            $this->attrSetColFacMock,
            $this->prodAttrColFacMock,
            $this->resourceMock,
            $this->paramsArray,
            $this->uploaderHelper,
            $this->downloadableHelper,
            $this->domainValidator,
            $this->metadataPoolMock,
            $this->attributeOptionCollectionFactory
        );
        $downloadableModel->saveData();
    }

    /**
     * Data for method testSaveDataAppend.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function dataForSave(): array
    {
        return [
            [
                'newSku' => [
                    'downloadablesku1' => [
                        'entity_id' => '25',
                        'type_id' => 'downloadable',
                        'attr_set_id' => '4',
                        'attr_set_code' => 'Default'
                    ]
                ],
                'bunch' => [
                    [
                        'sku' => 'downloadablesku1',
                        'product_type' => 'downloadable',
                        'name' => 'Downloadable Product 1',
                        'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                            . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                        'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                            . ' downloads=unlimited, file=media/file_link.mp4,sortorder=1|group_title=Group Title,'
                            . 'title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                    ]
                ],
                'allowImport' => true,
                "fetchResult" => [
                    'sample' => [
                        [
                            'sample_id' => '65',
                            'product_id' => '25',
                            'sample_url' => null,
                            'sample_file' => '',
                            'sample_type' => 'file',
                            'sort_order' => '1'
                        ],
                        [
                            'sample_id' => '66',
                            'product_id' => '25',
                            'sample_url' => 'media/file2.mp4',
                            'sample_file' => null,
                            'sample_type' => 'url',
                            'sort_order' => '0'
                        ]
                    ],
                    'link' => [
                        [
                            'link_id' => '65',
                            'product_id' => '25',
                            'sort_order' => '1',
                            'number_of_downloads' => '0',
                            'is_shareable' => '2',
                            'link_url' => null,
                            'link_file' => '',
                            'link_type' => 'file',
                            'sample_url' => null,
                            'sample_file' => null,
                            'sample_type' => null
                        ],
                        [
                            'link_id' => '66',
                            'product_id' => '25',
                            'sort_order' => '0',
                            'number_of_downloads' => '0',
                            'is_shareable' => '2',
                            'link_url' => 'media/file2.mp4',
                            'link_file' => null,
                            'link_type' => 'url',
                            'sample_url' => null,
                            'sample_file' => null,
                            'sample_type' => null
                        ]
                    ]
                ]
            ],
            [
                'newSku' => [
                    'downloadablesku2' => [
                        'entity_id' => '25',
                        'type_id' => 'downloadable',
                        'attr_set_id' => '4',
                        'attr_set_code' => 'Default'
                    ]
                ],
                'bunch' => [
                    [
                        'sku' => 'downloadablesku2',
                        'product_type' => 'downloadable',
                        'name' => 'Downloadable Product 1',
                        'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                            . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                        'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                            . ' downloads=unlimited, file=media/file_link.mp4,sortorder=1|group_title=Group Title, '
                            . 'title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                    ]
                ],
                'allowImport' => false,
                "fetchResult" => ['sample' => [], 'link' => []]
            ],
            [
                'newSku' => [
                    'downloadablesku3' => [
                        'entity_id' => '25',
                        'type_id' => 'simple',
                        'attr_set_id' => '4',
                        'attr_set_code' => 'Default'
                    ]
                ],
                'bunch' => [
                    [
                        'sku' => 'downloadablesku3',
                        'product_type' => 'downloadable',
                        'name' => 'Downloadable Product 1',
                        'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4,'
                            . 'sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                        'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                            . ' downloads=unlimited, file=media/file_link.mp4,sortorder=1|group_title=Group Title,'
                            . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                    ]
                ],
                'allowImport' => true,
                "fetchResult" => ['sample' => [], 'link' => []]
            ],
            [
                'newSku' => [
                    'downloadablesku4' => [
                        'entity_id' => '25',
                        'type_id' => 'downloadable',
                        'attr_set_id' => '4',
                        'attr_set_code' => 'Default'
                    ]
                ],
                'bunch' => [
                    [
                        'sku' => 'downloadablesku4',
                        'product_type' => 'downloadable',
                        'name' => 'Downloadable Product 1',
                        'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4,'
                            . 'sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                        'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                            . ' downloads=unlimited, file=media/file_link.mp4,sortorder=1|group_title=Group Title,'
                            . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                    ]
                ],
                'allowImport' => true,
                "fetchResult" => [
                    'sample' => [
                        [
                            'sample_id' => '65',
                            'product_id' => '25',
                            'sample_url' => null,
                            'sample_file' => '',
                            'sample_type' => 'file',
                            'sort_order' => '1'
                        ],
                        [
                            'sample_id' => '66',
                            'product_id' => '25',
                            'sample_url' => 'media/some_another_file.mp4',
                            'sample_file' => null,
                            'sample_type' => 'url',
                            'sort_order' => '0'
                        ]
                    ],
                    'link' => [
                        [
                            'link_id' => '65',
                            'product_id' => '25',
                            'sort_order' => '1',
                            'number_of_downloads' => '0',
                            'is_shareable' => '2',
                            'link_url' => null,
                            'link_file' => '',
                            'link_type' => 'file',
                            'sample_url' => null,
                            'sample_file' => null,
                            'sample_type' => null
                        ],
                        [
                            'link_id' => '66',
                            'product_id' => '25',
                            'sort_order' => '0',
                            'number_of_downloads' => '0',
                            'is_shareable' => '2',
                            'link_url' => 'media/some_another_file.mp4',
                            'link_file' => null,
                            'link_type' => 'url',
                            'sample_url' => null,
                            'sample_file' => null,
                            'sample_type' => null
                        ]
                    ]
                ]
            ],
            [
                'newSku' => [
                    'downloadablesku5' => [
                        'entity_id' => '25',
                        'type_id' => 'downloadable',
                        'attr_set_id' => '4',
                        'attr_set_code' => 'Default'
                    ]
                ],
                'bunch' => [
                    [
                        'sku' => 'downloadablesku5',
                        'product_type' => 'downloadable',
                        'name' => 'Downloadable Product 1',
                        'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4,'
                            . 'sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                        'downloadable_links' => 'group_title=Group Title, title=Title 2, price=10, downloads=unlimited,'
                            . ' url=http://www.sample.com/pic.jpg,sortorder=0,sample=http://www.sample.com/pic.jpg,'
                            . 'purchased_separately=1,shareable=1|group_title=Group Title, title=Title 2, price=10, '
                            . 'downloads=unlimited, url=media/file2.mp4,sortorder=0,sample=media/file2mp4'
                    ]
                ],
                'allowImport' => true,
                "fetchResult" => [
                    'sample' => [
                        [
                            'sample_id' => '65',
                            'product_id' => '25',
                            'sample_url' => null,
                            'sample_file' => '',
                            'sample_type' => 'file',
                            'sort_order' => '1'
                        ],
                        [
                            'sample_id' => '66',
                            'product_id' => '25',
                            'sample_url' => 'media/file2.mp4',
                            'sample_file' => null,
                            'sample_type' => 'url',
                            'sort_order' => '0'
                        ]
                    ],
                    'link' => [
                        [
                            'link_id' => '65',
                            'product_id' => '25',
                            'sort_order' => '1',
                            'number_of_downloads' => '0',
                            'is_shareable' => '1',
                            'link_url' => 'http://www.sample.com/pic.jpg',
                            'link_file' => null,
                            'link_type' => 'url',
                            'sample_url' => 'http://www.sample.com/pic.jpg',
                            'sample_file' => null,
                            'sample_type' => 'url'
                        ],
                        [
                            'link_id' => '66',
                            'product_id' => '25',
                            'sort_order' => '0',
                            'number_of_downloads' => '0',
                            'is_shareable' => '2',
                            'link_url' => 'media/file2.mp4',
                            'link_file' => null,
                            'link_type' => 'url',
                            'sample_url' => null,
                            'sample_file' => 'f/i/file.png',
                            'sample_type' => 'file'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return void
     */
    #[DataProvider('isRowValidData')]
    public function testIsRowValid(array $rowData, $rowNum, $isNewProduct, $isDomainValid, $expectedResult): void
    {
        // Configure connection mock for fetchAll call
        $this->connectionMock->method('fetchAll')->willReturn([
            [
                'attribute_set_name' => '1',
                'attribute_id' => '1'
            ],
            [
                'attribute_set_name' => '2',
                'attribute_id' => '2'
            ]
        ]);
        $this->domainValidator->expects($this->any())
            ->method('isValid')
            ->withAnyParameters()
            ->willReturn($isDomainValid);

        $downloadableModel = new Downloadable(
            $this->attrSetColFacMock,
            $this->prodAttrColFacMock,
            $this->resourceMock,
            $this->paramsArray,
            $this->uploaderHelper,
            $this->downloadableHelper,
            $this->domainValidator,
            $this->metadataPoolMock,
            $this->attributeOptionCollectionFactory
        );
        $result = $downloadableModel->isRowValid($rowData, $rowNum, $isNewProduct);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data for method testIsRowValid.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function isRowValidData(): array
    {
        return [
            [
                'rowData' => [
                    'sku' => 'downloadablesku1',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 1',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4,'
                        . 'sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10, '
                        . 'downloads=unlimited, file=media/file_link.mp4,sortorder=1|group_title=Group Title, '
                        . 'title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'rowNum' => 0,
                'isNewProduct' => true,
                'isDomainValid' => true,
                'expectedResult' => true
            ],
            [
                'rowData' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                        . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                        . ' downloads=unlimited, file=media/file.mp4,sortorder=1|group_title=Group Title,'
                        . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'rowNum' => 1,
                'isNewProduct' => true,
                'isDomainValid' => true,
                'expectedResult' => true
            ],
            [
                'rowData' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                        . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                        . ' downloads=unlimited, file=media/file.mp4,sortorder=1|group_title=Group Title,'
                        . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'rowNum' => 3,
                'isNewProduct' => true,
                'isDomainValid' => true,
                'expectedResult' => true
            ],
            [
                'rowData' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'title=Title 1, file=media/file.mp4,sortorder=1|title=Title 2,' .
                        ' group_title=Group Title, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'title=Title 1, price=10, downloads=unlimited, file=media/file.mp4,'
                        . 'sortorder=1|group_title=Group Title, title=Title 2, price=10, downloads=unlimited,'
                        . ' url=media/file2.mp4,sortorder=0'
                ],
                'rowNum' => 4,
                'isNewProduct' => true,
                'isDomainValid' => true,
                'expectedResult' => true
            ],
            [ //empty group title samples
                'rowData' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                        . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                        . ' downloads=unlimited, file=media/file.mp4,sortorder=1|group_title=Group Title,'
                        . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'rowNum' => 5,
                'isNewProduct' => true,
                'isDomainValid' => true,
                'expectedResult' => true
            ],
            [ //empty group title links
                'rowData' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                        . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                        . ' downloads=unlimited, file=media/file.mp4,sortorder=1|group_title=Group Title,'
                        . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'rowNum' => 6,
                'isNewProduct' => true,
                'isDomainValid' => true,
                'expectedResult' => true
            ],
            [
                'rowData' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2'
                ],
                'rowNum' => 2,
                'isNewProduct' => false,
                'isDomainValid' => true,
                'expectedResult' => true
            ],
            [
                'rowData' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => '',
                    'downloadable_links' => ''
                ],
                'rowNum' => 7,
                'isNewProduct' => true,
                'isDomainValid' => true,
                'expectedResult' => false
            ]
        ];
    }

    /**
     * @return void
     */
    #[DataProvider('dataForUploaderDir')]
    public function testSetUploaderDirFalse($newSku, $bunch, $allowImport, $parsedOptions): void
    {
        // Configure connection mock for fetchAll call
        $this->connectionMock->method('fetchAll')->willReturn([]);
        $this->downloadableHelper->expects($this->atLeastOnce())
            ->method('fillExistOptions')->willReturn($parsedOptions['link']);
        $this->uploaderHelper->method('isFileExist')->willReturn(false);

        $downloadableModel = new Downloadable(
            $this->attrSetColFacMock,
            $this->prodAttrColFacMock,
            $this->resourceMock,
            $this->paramsArray,
            $this->uploaderHelper,
            $this->downloadableHelper,
            $this->domainValidator,
            $this->metadataPoolMock,
            $this->attributeOptionCollectionFactory
        );
        $this->entityModelMock->expects($this->once())->method('getNewSku')->willReturn($newSku);
        $this->entityModelMock
            ->method('getNextBunch')
            ->willReturnOnConsecutiveCalls($bunch, null);
        $this->entityModelMock->method('isRowAllowedToImport')->willReturn($allowImport);
        $exception = new LocalizedException(new Phrase('Error'));
        $this->uploaderMock->expects($this->any())->method('move')->willThrowException($exception);
        $this->entityModelMock->expects($this->exactly(2))->method('addRowError');
        $result = $downloadableModel->saveData();
        $this->assertNotNull($result);
    }

    /**
     * Data for methods testSetUploaderDirFalse, testSetDestDirFalse, testDirWithoutPermissions
     *
     * @return array
     */
    public static function dataForUploaderDir(): array
    {
        return [
            [
                'newSku' => [
                    'downloadablesku1' => [
                        'entity_id' => '25',
                        'type_id' => 'downloadable',
                        'attr_set_id' => '4',
                        'attr_set_code' => 'Default'
                    ]
                ],
                'bunch' => [
                    [
                        'sku' => 'downloadablesku1',
                        'product_type' => 'downloadable',
                        'name' => 'Downloadable Product 1',
                        'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                            . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                        'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10, downloads='
                            . 'unlimited, file=media/file_link.mp4,sortorder=1|group_title=Group Title, title=Title 2,'
                            . ' price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                    ]
                ],
                'allowImport' => true,
                'parsedOptions' => [
                    'sample' => [
                        'sample_id' => null,
                        'product_id' => '25',
                        'sample_url' => null,
                        'sample_file' => 'media/file.mp4',
                        'sample_type' => 'file',
                        'sort_order' => '1',
                        'group_title' => 'Group Title Samples',
                        'title' => 'Title 1'
                    ],
                    'link' => [
                        'link_id' => null,
                        'product_id' => '25',
                        'sort_order' => '1',
                        'number_of_downloads' => 0,
                        'is_shareable' => 2,
                        'link_url' => null,
                        'link_file' => '',
                        'link_type' => 'file',
                        'sample_url' => null,
                        'sample_file' => null,
                        'sample_type' => null,
                        'group_title' => 'Group Title Links',
                        'title' => 'Title 1',
                        'price' => '10'
                    ]
                ]
            ]
        ];
    }

    /**
     * Test for method prepareAttributesWithDefaultValueForSave.
     *
     * @return void
     */
    public function testPrepareAttributesWithDefaultValueForSave(): void
    {
        $rowData = [
            '_attribute_set' => 'Default',
            'sku' => 'downloadablesku1',
            'product_type' => 'downloadable',
            'name' => 'Downloadable Product 1',
            'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4,sortorder=1'
                . '|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
            'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10, downloads=unlimited,'
                . ' file=media/file_link.mp4,sortorder=1|group_title=Group Title, title=Title 2, price=10, downloads'
                . '=unlimited, url=media/file2.mp4,sortorder=0'
        ];
        // Configure connection mock for fetchAll call
        $this->connectionMock->method('fetchAll')->willReturn([
            [
                'attribute_set_name' => '1',
                'attribute_id' => '1'
            ],
            [
                'attribute_set_name' => '2',
                'attribute_id' => '2'
            ]
        ]);

        $downloadableModel = new Downloadable(
            $this->attrSetColFacMock,
            $this->prodAttrColFacMock,
            $this->resourceMock,
            $this->paramsArray,
            $this->uploaderHelper,
            $this->downloadableHelper,
            $this->domainValidator,
            $this->metadataPoolMock,
            $this->attributeOptionCollectionFactory
        );
        $this->setPropertyValue(
            $downloadableModel,
            '_attributes',
            [
                'Default' => [
                    'name' => [
                        'id' => '69',
                        'code' => 'name',
                        'is_global' => '0',
                        'is_required' => '1',
                        'is_unique' => '0',
                        'frontend_label' => 'Name',
                        'is_static' => false,
                        'apply_to' => [],
                        'type' => 'varchar',
                        'default_value' => null,
                        'options' => []
                    ],
                    'sku' => [
                        'id' => '70',
                        'code' => 'sku',
                        'is_global' => '1',
                        'is_required' => '1',
                        'is_unique' => '1',
                        'frontend_label' => 'SKU',
                        'is_static' => true,
                        'apply_to' => [],
                        'type' => 'varchar',
                        'default_value' => null,
                        'options' => []
                    ]
                ]
            ]
        );

        $result = $downloadableModel->prepareAttributesWithDefaultValueForSave($rowData);
        $this->assertNotNull($result);
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }
}
