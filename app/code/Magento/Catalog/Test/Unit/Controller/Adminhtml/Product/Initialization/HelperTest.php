<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface\Proxy as ProductRepository;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Api\Data\ProductLinkTypeInterface;
use Magento\Catalog\Model\ProductLink\Link as ProductLink;

/**
 * Class HelperTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var int
     */
    protected $websiteId = 1;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var StockDataFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockFilterMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var WebsiteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var DateFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateFilterMock;

    /**
     * @var ProductLinkInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkFactoryMock;

    /**
     * @var ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var ProductCustomOptionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customOptionFactoryMock;

    /**
     * @var ProductCustomOptionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customOptionMock;

    /**
     * @var \Magento\Catalog\Model\Product\Link\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkResolverMock;

    /**
     * @var \Magento\Catalog\Model\Product\LinkTypeProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeProviderMock;

    /**
     * @var ProductLinks|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinksMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->productLinkFactoryMock = $this->getMockBuilder(ProductLinkInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getWebsite'])
            ->getMockForAbstractClass();
        $this->websiteMock = $this->getMockBuilder(WebsiteInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->dateFilterMock = $this->getMockBuilder(DateFilter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockFilterMock = $this->getMockBuilder(StockDataFilter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->setMethods([
                'setData',
                'addData',
                'getId',
                'setWebsiteIds',
                'isLockedAttribute',
                'lockAttribute',
                'getAttributes',
                'unlockAttribute',
                'getOptionsReadOnly',
                'setOptions',
                'setCanSaveCustomOptions',
                '__sleep',
                '__wakeup',
                'getSku',
                'getWebsiteIds'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customOptionFactoryMock = $this->getMockBuilder(ProductCustomOptionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customOptionMock = $this->getMockBuilder(ProductCustomOptionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productLinksMock = $this->getMockBuilder(ProductLinks::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productLinksMock->expects($this->any())
            ->method('initializeLinks')
            ->willReturn($this->productMock);
        $this->linkTypeProviderMock = $this->getMockBuilder(LinkTypeProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->objectManager->getObject(Helper::class, [
            'request' => $this->requestMock,
            'storeManager' => $this->storeManagerMock,
            'stockFilter' => $this->stockFilterMock,
            'productLinks' => $this->productLinksMock,
            'dateFilter' => $this->dateFilterMock,
            'customOptionFactory' => $this->customOptionFactoryMock,
            'productLinkFactory' => $this->productLinkFactoryMock,
            'productRepository' => $this->productRepositoryMock,
            'linkTypeProvider' => $this->linkTypeProviderMock,
        ]);

        $this->linkResolverMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Link\Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperReflection = new \ReflectionClass(get_class($this->helper));
        $resolverProperty = $helperReflection->getProperty('linkResolver');
        $resolverProperty->setAccessible(true);
        $resolverProperty->setValue($this->helper, $this->linkResolverMock);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param array $links
     */
    private function assembleProductMock($links = [])
    {
        $this->customOptionMock->expects($this->once())
            ->method('setProductSku');
        $this->customOptionMock->expects($this->once())
            ->method('setOptionId');

        $optionsData = [
            'option1' => ['is_delete' => true, 'name' => 'name1', 'price' => 'price1'],
            'option2' => ['is_delete' => false, 'name' => 'name1', 'price' => 'price1'],
        ];
        $productData = [
            'stock_data' => ['stock_data'],
            'options' => $optionsData,
        ];
        $attributeNonDate = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeDate = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attributeNonDateBackEnd =
            $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend::class)
                ->disableOriginalConstructor()
                ->getMock();
        $attributeDateBackEnd = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attributeNonDate->expects($this->any())
            ->method('getBackend')
            ->willReturn($attributeNonDateBackEnd);
        $attributeDate->expects($this->any())
            ->method('getBackend')
            ->willReturn($attributeDateBackEnd);
        $attributeNonDateBackEnd->expects($this->any())
            ->method('getType')
            ->willReturn('non-datetime');
        $attributeDateBackEnd->expects($this->any())
            ->method('getType')
            ->willReturn('datetime');

        $attributesArray = [
            $attributeNonDate,
            $attributeDate
        ];

        $useDefaults = ['attributeCode1', 'attributeCode2'];

        $this->requestMock->expects($this->at(0))
            ->method('getPost')
            ->with('product')
            ->willReturn($productData);
        $this->requestMock->expects($this->at(1))
            ->method('getPost')
            ->with('use_default')
            ->willReturn($useDefaults);
        $this->linkResolverMock->expects($this->once())->method('getLinks')->willReturn($links);
        $this->stockFilterMock->expects($this->once())
            ->method('filter')
            ->with(['stock_data'])
            ->willReturn(['stock_data']);
        $this->productMock->expects($this->once())
            ->method('isLockedAttribute')
            ->with('media')
            ->willReturn(true);
        $this->productMock->expects($this->once())
            ->method('unlockAttribute')
            ->with('media');
        $this->productMock->expects($this->once())
            ->method('lockAttribute')
            ->with('media');
        $this->productMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributesArray);

        $productData['category_ids'] = [];
        $productData['website_ids'] = [];
        unset($productData['options']);

        $this->productMock->expects($this->once())
            ->method('addData')
            ->with($productData);
        $this->productMock->expects($this->any())
            ->method('getSku')
            ->willReturn('sku');
        $this->productMock->expects($this->any())
            ->method('getOptionsReadOnly')
            ->willReturn(false);

        $this->customOptionFactoryMock->expects($this->any())
            ->method('create')
            ->with(['data' => $optionsData['option2']])
            ->willReturn($this->customOptionMock);
        $this->productMock->expects($this->once())
            ->method('setOptions')
            ->with([$this->customOptionMock]);
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::initialize
     */
    public function testInitialize()
    {
        $this->assembleProductMock();
        $this->linkTypeProviderMock->expects($this->once())
            ->method('getItems')
            ->willReturn($this->assembleLinkTypes(['related', 'upsell', 'crosssell']));

        $this->assertEquals($this->productMock, $this->helper->initialize($this->productMock));
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::initialize
     * @dataProvider initializeWithLinksDataProvider
     */
    public function testInitializeWithLinks($links, $linkTypes, $expectedLinks)
    {
        $this->productLinkFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(function () {
                return $this->getMockBuilder(ProductLink::class)
                    ->setMethods(null)
                    ->disableOriginalConstructor()
                    ->getMock();
            });

        $this->linkTypeProviderMock->expects($this->once())
            ->method('getItems')
            ->willReturn($this->assembleLinkTypes($linkTypes));

        $this->assembleProductRepositoryMock($links);
        $this->assembleProductMock($links);

        $this->assertEquals($this->productMock, $this->helper->initialize($this->productMock));

        $productLinks = $this->productMock->getProductLinks();
        $this->assertCount(count($expectedLinks), $productLinks);
        $resultLinks = [];

        foreach ($productLinks as $link) {
            $this->assertInstanceOf(ProductLink::class, $link);
            $this->assertEquals('sku', $link->getSku());
            $resultLinks[] = ['type' => $link->getLinkType(), 'linked_product_sku' => $link->getLinkedProductSku()];
        }

        $this->assertEquals($expectedLinks, $resultLinks);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function initializeWithLinksDataProvider()
    {
        return [
            // No links
            [
                'links' => [],
                'linkTypes' => ['related', 'upsell', 'crosssell'],
                'expected_links' => [],
            ],

            // Related links
            [
                'links' => [
                    'related' => [
                        0 => [
                            'id' => 1,
                            'thumbnail' => 'http://magento.dev/media/no-image.jpg',
                            'name' => 'Test',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'Test',
                            'price' => 1.00,
                            'position' => 1,
                            'record_id' => 1,
                        ]
                    ]
                ],
                'linkTypes' => ['related', 'upsell', 'crosssell'],
                'expected_links' => [
                    ['type' => 'related', 'linked_product_sku' => 'Test'],
                ],
            ],

            // Custom link
            [
                'links' => [
                    'customlink' => [
                        0 => [
                            'id' => 4,
                            'thumbnail' => 'http://magento.dev/media/no-image.jpg',
                            'name' => 'Test Custom',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'Testcustom',
                            'price' => 1.00,
                            'position' => 1,
                            'record_id' => 1,
                        ],
                    ],
                ],
                'linkTypes' => ['related', 'upsell', 'crosssell', 'customlink'],
                'expected_links' => [
                    ['type' => 'customlink', 'linked_product_sku' => 'Testcustom'],
                ],
            ],

            // Both links
            [
                'links' => [
                    'related' => [
                        0 => [
                            'id' => 1,
                            'thumbnail' => 'http://magento.dev/media/no-image.jpg',
                            'name' => 'Test',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'Test',
                            'price' => 1.00,
                            'position' => 1,
                            'record_id' => 1,
                        ],
                    ],
                    'customlink' => [
                        0 => [
                            'id' => 4,
                            'thumbnail' => 'http://magento.dev/media/no-image.jpg',
                            'name' => 'Test Custom',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'Testcustom',
                            'price' => 2.00,
                            'position' => 2,
                            'record_id' => 1,
                        ],
                    ],
                ],
                'linkTypes' => ['related', 'upsell', 'crosssell', 'customlink'],
                'expected_links' => [
                    ['type' => 'related', 'linked_product_sku' => 'Test'],
                    ['type' => 'customlink', 'linked_product_sku' => 'Testcustom'],
                ],
            ],

            // Undefined link type
            [
                'links' => [
                    'related' => [
                        0 => [
                            'id' => 1,
                            'thumbnail' => 'http://magento.dev/media/no-image.jpg',
                            'name' => 'Test',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'Test',
                            'price' => 1.00,
                            'position' => 1,
                            'record_id' => 1,
                        ],
                    ],
                    'customlink' => [
                        0 => [
                            'id' => 4,
                            'thumbnail' => 'http://magento.dev/media/no-image.jpg',
                            'name' => 'Test Custom',
                            'status' => 'Enabled',
                            'attribute_set' => 'Default',
                            'sku' => 'Testcustom',
                            'price' => 2.00,
                            'position' => 2,
                            'record_id' => 1,
                        ],
                    ],
                ],
                'linkTypes' => ['related', 'upsell', 'crosssell'],
                'expected_links' => [
                    ['type' => 'related', 'linked_product_sku' => 'Test'],
                ],
            ],
        ];
    }

    /**
     * Data provider for testMergeProductOptions
     *
     * @return array
     */
    public function mergeProductOptionsDataProvider()
    {
        return [
            'options are not array, empty array is returned' => [
                null,
                [],
                [],
            ],
            'replacement is not array, original options are returned' => [
                ['val'],
                null,
                ['val'],
            ],
            'ids do not match, no replacement occurs' => [
                [
                    [
                        'option_id' => '3',
                        'key1' => 'val1',
                        'default_key1' => 'val2'
                    ]
                ],
                [4 => ['key1' => '1']],
                [
                    [
                        'option_id' => '3',
                        'key1' => 'val1',
                        'default_key1' => 'val2'
                    ]
                ]
            ],
            'key2 is replaced, key1 is not (checkbox is not checked)' => [
                [
                    [
                        'option_id' => '5',
                        'key1' => 'val1',
                        'key2' => 'val2',
                        'default_key1' => 'val3',
                        'default_key2' => 'val4'
                    ]
                ],
                [5 => ['key1' => '0', 'key2' => '1']],
                [
                    [
                        'option_id' => '5',
                        'key1' => 'val1',
                        'key2' => 'val4',
                        'default_key1' => 'val3',
                        'default_key2' => 'val4'
                    ]
                ]
            ],
            'key1 is replaced, key2 has no default value' => [
                [
                    [
                        'option_id' => '7',
                        'key1' => 'val1',
                        'key2' => 'val2',
                        'default_key1' => 'val3'
                    ]
                ],
                [7 => ['key1' => '1', 'key2' => '1']],
                [
                    [
                        'option_id' => '7',
                        'key1' => 'val3',
                        'key2' => 'val2',
                        'default_key1' => 'val3'
                    ]
                ],
            ],
        ];
    }

    /**
     * @param array $productOptions
     * @param array $defaultOptions
     * @param array $expectedResults
     * @dataProvider mergeProductOptionsDataProvider
     */
    public function testMergeProductOptions($productOptions, $defaultOptions, $expectedResults)
    {
        $result = $this->helper->mergeProductOptions($productOptions, $defaultOptions);
        $this->assertEquals($expectedResults, $result);
    }

    /**
     * @param array $types
     * @return array
     */
    private function assembleLinkTypes($types)
    {
        $linkTypes = [];
        $linkTypeCode = 1;

        foreach ($types as $typeName) {
            $linkType = $this->getMock(ProductLinkTypeInterface::class);
            $linkType->method('getCode')->willReturn($linkTypeCode++);
            $linkType->method('getName')->willReturn($typeName);

            $linkTypes[] = $linkType;
        }

        return $linkTypes;
    }

    /**
     * @param array $links
     */
    private function assembleProductRepositoryMock($links)
    {
        $repositoryReturnMap = [];

        foreach ($links as $linkType) {
            foreach ($linkType as $link) {
                $mockLinkedProduct = $this->getMockBuilder(Product::class)
                    ->disableOriginalConstructor()
                    ->getMock();

                $mockLinkedProduct->expects($this->any())
                    ->method('getId')
                    ->willReturn($link['id']);

                $mockLinkedProduct->expects($this->any())
                    ->method('getSku')
                    ->willReturn($link['sku']);

                // Even optional arguments need to be provided for returnMapValue
                $repositoryReturnMap[] = [$link['id'], false, null, false, $mockLinkedProduct];
            }
        }

        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->will($this->returnValueMap($repositoryReturnMap));
    }
}
