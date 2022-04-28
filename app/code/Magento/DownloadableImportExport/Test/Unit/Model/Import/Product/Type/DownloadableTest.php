<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Uploader;
use Magento\Downloadable\Model\Url\DomainValidator;
use Magento\DownloadableImportExport\Helper\Data;
use Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManager;
use Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DownloadableTest for downloadable products import
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadableTest extends AbstractImportTestCase
{
    /**
     * @var ObjectManager|Downloadable
     */
    protected $downloadableModelMock;

    /**
     * @var Mysql|MockObject
     */
    protected $connectionMock;

    /**
     * @var Select|MockObject
     */
    protected $select;

    /**
     * @var MockObject
     */
    protected $attrSetColFacMock;

    /**
     * @var Collection|MockObject
     */
    protected $attrSetColMock;

    /**
     * @var MockObject
     */
    protected $prodAttrColFacMock;

    /**
     * @var DomainValidator
     */
    private $domainValidator;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection|MockObject
     */
    protected $prodAttrColMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|MockObject
     */
    protected $entityModelMock;

    /**
     * @var array|mixed
     */
    protected $paramsArray;

    /**
     * @var Uploader|MockObject
     */
    protected $uploaderMock;

    /**
     * @var Write|MockObject
     */
    protected $directoryWriteMock;

    /**
     * @var MockObject
     */
    protected $uploaderHelper;

    /**
     * @var MockObject
     */
    protected $downloadableHelper;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        parent::setUp();

        //connection and sql query results
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->addMethods(['joinLeft'])
            ->onlyMethods(
                ['select', 'fetchAll', 'fetchPairs', 'insertOnDuplicate', 'delete', 'quoteInto', 'fetchAssoc']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->select = $this->createMock(Select::class);
        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();
        $this->select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $adapter = $this->createMock(Mysql::class);
        $adapter->expects($this->any())->method('quoteInto')->willReturn('query');
        $this->select->expects($this->any())->method('getAdapter')->willReturn($adapter);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->select);

        $this->connectionMock->expects($this->any())->method('insertOnDuplicate')->willReturnSelf();
        $this->connectionMock->expects($this->any())->method('delete')->willReturnSelf();
        $this->connectionMock->expects($this->any())->method('quoteInto')->willReturn('');

        //constructor arguments:
        // 1. $attrSetColFac
        $this->attrSetColFacMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->attrSetColMock = $this->createPartialMock(
            Collection::class,
            ['setEntityTypeFilter']
        );
        $this->attrSetColMock
            ->expects($this->any())
            ->method('setEntityTypeFilter')
            ->willReturn([]);

        // 2. $prodAttrColFac
        $this->prodAttrColFacMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
            ['create']
        );

        $attrCollection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class);

        $attrCollection->expects($this->any())->method('addFieldToFilter')->willReturn([]);
        $this->prodAttrColFacMock->expects($this->any())->method('create')->willReturn($attrCollection);

        // 3. $resource
        $this->resourceMock = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn(
            $this->connectionMock
        );
        $this->resourceMock->expects($this->any())->method('getTableName')->willReturn(
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
        $this->entityModelMock->expects($this->any())->method('getEntityTypeId')->willReturn(5);
        $this->entityModelMock->expects($this->any())->method('getParameters')->willReturn([]);
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
            \Magento\DownloadableImportExport\Helper\Uploader::class,
            ['getUploader', 'isFileExist']
        );
        $this->uploaderHelper->expects($this->any())->method('getUploader')->willReturn($this->uploaderMock);
        $this->downloadableHelper = $this->createPartialMock(
            Data::class,
            ['prepareDataForSave', 'fillExistOptions']
        );
        $this->downloadableHelper->expects($this->any())->method('prepareDataForSave')->willReturn([]);
    }

    /**
     * @return void
     * @dataProvider dataForSave
     */
    public function testSaveDataAppend($newSku, $bunch, $allowImport, $fetchResult): void
    {
        $this->entityModelMock->expects($this->once())->method('getNewSku')->willReturn($newSku);
        $this->entityModelMock
            ->method('getNextBunch')
            ->willReturnOnConsecutiveCalls(null, $bunch, null);
        $this->entityModelMock->expects($this->any())->method('isRowAllowedToImport')->willReturn($allowImport);

        $this->uploaderMock->expects($this->any())->method('setTmpDir')->willReturn(true);
        $this->uploaderMock->expects($this->any())->method('setDestDir')->with('pub/media/')->willReturn(true);

        $this->connectionMock->expects($this->any())->method('fetchAll')->with(
            $this->select
        )->will($this->onConsecutiveCalls(
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
        ));

        $downloadableModelMock = $this->objectManagerHelper->getObject(
            Downloadable::class,
            [
                'attrSetColFac' => $this->attrSetColFacMock,
                'prodAttrColFac' => $this->prodAttrColFacMock,
                'resource' => $this->resourceMock,
                'params' => $this->paramsArray,
                'uploaderHelper' => $this->uploaderHelper,
                'downloadableHelper' => $this->downloadableHelper
            ]
        );

        $downloadableModelMock->saveData();
    }

    /**
     * Data for method testSaveDataAppend.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataForSave(): array
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
                [
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
                ['sample' => [], 'link' => []]
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
                ['sample' => [], 'link' => []]
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
                [
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
                [
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
     * @dataProvider isRowValidData
     */
    public function testIsRowValid(array $rowData, $rowNum, $isNewProduct, $isDomainValid, $expectedResult): void
    {
        $this->connectionMock->expects($this->any())->method('fetchAll')->with(
            $this->select
        )->willReturnOnConsecutiveCalls(
            [
                [
                    'attribute_set_name' => '1',
                    'attribute_id' => '1'
                ],
                [
                    'attribute_set_name' => '2',
                    'attribute_id' => '2'
                ]
            ]
        );

        $this->domainValidator = $this->createMock(DomainValidator::class);
        $this->domainValidator
            ->expects($this->any())->method('isValid')
            ->withAnyParameters()
            ->willReturn($isDomainValid);

        $this->downloadableModelMock = $this->objectManagerHelper->getObject(
            Downloadable::class,
            [
                'attrSetColFac' => $this->attrSetColFacMock,
                'prodAttrColFac' => $this->prodAttrColFacMock,
                'resource' => $this->resourceMock,
                'params' => $this->paramsArray,
                'uploaderHelper' => $this->uploaderHelper,
                'downloadableHelper' => $this->downloadableHelper,
                'domainValidator' => $this->domainValidator
            ]
        );
        $result = $this->downloadableModelMock->isRowValid($rowData, $rowNum, $isNewProduct);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data for method testIsRowValid.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function isRowValidData(): array
    {
        return [
            [
                'row_data' => [
                    'sku' => 'downloadablesku1',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 1',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4,'
                        . 'sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10, '
                        . 'downloads=unlimited, file=media/file_link.mp4,sortorder=1|group_title=Group Title, '
                        . 'title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'row_num' => 0,
                'is_new_product' => true,
                'is_domain_valid' => true,
                'expected_result' => true
            ],
            [
                'row_data' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                        . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                        . ' downloads=unlimited, file=media/file.mp4,sortorder=1|group_title=Group Title,'
                        . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'row_num' => 1,
                'is_new_product' => true,
                'is_domain_valid' => true,
                'expected_result' => true
            ],
            [
                'row_data' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                        . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                        . ' downloads=unlimited, file=media/file.mp4,sortorder=1|group_title=Group Title,'
                        . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'row_num' => 3,
                'is_new_product' => true,
                'is_domain_valid' => true,
                'expected_result' => true
            ],
            [
                'row_data' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'title=Title 1, file=media/file.mp4,sortorder=1|title=Title 2,' .
                        ' group_title=Group Title, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'title=Title 1, price=10, downloads=unlimited, file=media/file.mp4,'
                        . 'sortorder=1|group_title=Group Title, title=Title 2, price=10, downloads=unlimited,'
                        . ' url=media/file2.mp4,sortorder=0'
                ],
                'row_num' => 4,
                'is_new_product' => true,
                'is_domain_valid' => true,
                'expected_result' => true
            ],
            [ //empty group title samples
                'row_data' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                        . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                        . ' downloads=unlimited, file=media/file.mp4,sortorder=1|group_title=Group Title,'
                        . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'row_num' => 5,
                'is_new_product' => true,
                'is_domain_valid' => true,
                'expected_result' => true
            ],
            [ //empty group title links
                'row_data' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => 'group_title=Group Title Samples, title=Title 1, file=media/file.mp4'
                        . ',sortorder=1|group_title=Group Title, title=Title 2, url=media/file2.mp4,sortorder=0',
                    'downloadable_links' => 'group_title=Group Title Links, title=Title 1, price=10,'
                        . ' downloads=unlimited, file=media/file.mp4,sortorder=1|group_title=Group Title,'
                        . ' title=Title 2, price=10, downloads=unlimited, url=media/file2.mp4,sortorder=0'
                ],
                'row_num' => 6,
                'is_new_product' => true,
                'is_domain_valid' => true,
                'expected_result' => true
            ],
            [
                'row_data' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2'
                ],
                'row_num' => 2,
                'is_new_product' => false,
                'is_domain_valid' => true,
                'expected_result' => true
            ],
            [
                'row_data' => [
                    'sku' => 'downloadablesku12',
                    'product_type' => 'downloadable',
                    'name' => 'Downloadable Product 2',
                    'downloadable_samples' => '',
                    'downloadable_links' => ''
                ],
                'row_num' => 7,
                'is_new_product' => true,
                'is_domain_valid' => true,
                'expected_result' => false
            ]
        ];
    }

    /**
     * @return void
     * @dataProvider dataForUploaderDir
     */
    public function testSetUploaderDirFalse($newSku, $bunch, $allowImport, $parsedOptions): void
    {
        $this->connectionMock->expects($this->any())->method('fetchAll')->with(
            $this->select
        )->willReturn([]);

        $metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->addMethods(['getLinkField'])
            ->onlyMethods(['getMetadata'])
            ->disableOriginalConstructor()
            ->getMock();
        $metadataPoolMock->expects($this->any())->method('getMetadata')->willReturnSelf();
        $metadataPoolMock->expects($this->any())->method('getLinkField')->willReturn('entity_id');
        $this->downloadableHelper->expects($this->atLeastOnce())
            ->method('fillExistOptions')->willReturn($parsedOptions['link']);
        $this->uploaderHelper->method('isFileExist')->willReturn(false);

        $this->downloadableModelMock = $this->objectManagerHelper->getObject(
            Downloadable::class,
            [
                'attrSetColFac' => $this->attrSetColFacMock,
                'prodAttrColFac' => $this->prodAttrColFacMock,
                'resource' => $this->resourceMock,
                'params' => $this->paramsArray,
                'uploaderHelper' => $this->uploaderHelper,
                'downloadableHelper' => $this->downloadableHelper,
                'metadataPool' => $metadataPoolMock
            ]
        );
        $this->entityModelMock->expects($this->once())->method('getNewSku')->willReturn($newSku);
        $this->entityModelMock
            ->method('getNextBunch')
            ->willReturnOnConsecutiveCalls($bunch, null);
        $this->entityModelMock->expects($this->any())->method('isRowAllowedToImport')->willReturn($allowImport);
        $exception = new LocalizedException(new Phrase('Error'));
        $this->uploaderMock->expects($this->any())->method('move')->willThrowException($exception);
        $this->entityModelMock->expects($this->exactly(2))->method('addRowError');
        $result = $this->downloadableModelMock->saveData();
        $this->assertNotNull($result);
    }

    /**
     * Data for methods testSetUploaderDirFalse, testSetDestDirFalse, testDirWithoutPermissions
     *
     * @return array
     */
    public function dataForUploaderDir(): array
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
        $this->connectionMock->expects($this->any())->method('fetchAll')->with(
            $this->select
        )->willReturnOnConsecutiveCalls(
            [
                [
                    'attribute_set_name' => '1',
                    'attribute_id' => '1'
                ],
                [
                    'attribute_set_name' => '2',
                    'attribute_id' => '2'
                ]
            ]
        );
        $this->downloadableModelMock = $this->objectManagerHelper->getObject(
            Downloadable::class,
            [
                'attrSetColFac' => $this->attrSetColFacMock,
                'prodAttrColFac' => $this->prodAttrColFacMock,
                'resource' => $this->resourceMock,
                'params' => $this->paramsArray,
                'uploaderHelper' => $this->uploaderHelper,
                'downloadableHelper' => $this->downloadableHelper
            ]
        );
        $this->setPropertyValue(
            $this->downloadableModelMock,
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

        $result = $this->downloadableModelMock->prepareAttributesWithDefaultValueForSave($rowData);
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
