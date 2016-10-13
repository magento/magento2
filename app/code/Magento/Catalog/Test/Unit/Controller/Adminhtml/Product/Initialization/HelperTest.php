<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;

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
     * @var Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customOptionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkResolverMock;

    /**
     * @var ProductLinks
     */
    protected $productLinksMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->productLinkFactoryMock = $this->getMockBuilder(ProductLinkInterfaceFactory::class)
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
                'getId',
                'setWebsiteIds',
                'isLockedAttribute',
                'lockAttribute',
                'getAttributes',
                'unlockAttribute',
                'getOptionsReadOnly',
                'setCanSaveCustomOptions',
                '__sleep',
                '__wakeup',
                'getSku',
                'getProductLinks',
                'getWebsiteIds'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customOptionFactoryMock = $this->getMockBuilder(ProductCustomOptionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->customOptionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->productLinksMock = $this->getMockBuilder(ProductLinks::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productLinksMock->expects($this->any())
            ->method('initializeLinks')
            ->willReturn($this->productMock);

        $this->helper = $this->objectManager->getObject(Helper::class, [
            'request' => $this->requestMock,
            'storeManager' => $this->storeManagerMock,
            'stockFilter' => $this->stockFilterMock,
            'productLinks' => $this->productLinksMock,
            'dateFilter' => $this->dateFilterMock,
            'customOptionFactory' => $this->customOptionFactoryMock,
            'productLinkFactory' => $this->productLinkFactoryMock,
            'productRepository' => $this->productRepositoryMock,
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
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::initialize
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testInitialize()
    {
        $optionsData = [
            'option1' => ['is_delete' => true, 'name' => 'name1', 'price' => 'price1', 'option_id' => ''],
            'option2' => ['is_delete' => false, 'name' => 'name1', 'price' => 'price1', 'option_id' => '13'],
            'option3' => ['is_delete' => false, 'name' => 'name1', 'price' => 'price1', 'option_id' => '14']
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
        $this->productMock->expects($this->any())
            ->method('getProductLinks')
            ->willReturn([]);
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
        $this->linkResolverMock->expects($this->once())->method('getLinks')->willReturn([]);
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
        $this->productMock->expects($this->any())
            ->method('getProductLinks')
            ->willReturn([]);
        $this->productMock->expects($this->once())
            ->method('lockAttribute')
            ->with('media');
        $this->productMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributesArray);

        $this->productMock->expects($this->any())
            ->method('getSku')
            ->willReturn('sku');
        $this->productMock->expects($this->any())
            ->method('getOptionsReadOnly')
            ->willReturn(false);

        $firstExpectedCustomOption = clone $this->customOptionMock;
        $firstExpectedCustomOption->setData($optionsData['option2']);
        $secondExpectedCustomOption = clone $this->customOptionMock;
        $secondExpectedCustomOption->setData($optionsData['option3']);
        $this->customOptionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnMap([
                [
                    ['data' => $optionsData['option2']],
                    $firstExpectedCustomOption
                ], [
                    ['data' => $optionsData['option3']],
                    $secondExpectedCustomOption
                ]
            ]);

        $this->assertEquals($this->productMock, $this->helper->initialize($this->productMock));

        $productOptions = $this->productMock->getOptions();
        $this->assertTrue(2 == count($productOptions));
        list($option2, $option3) = $productOptions;
        $this->assertTrue($option2->getOptionId() == $optionsData['option2']['option_id']);
        $this->assertTrue('sku' == $option2->getData('product_sku'));
        $this->assertTrue($option3->getOptionId() == $optionsData['option3']['option_id']);
        $this->assertTrue('sku' == $option2->getData('product_sku'));
    }

    /**
     * Data provider for testMergeProductOptions
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
                        'default_key1' => 'val2',
                        'values' => [
                            [
                                'option_type_id' => '2',
                                'key1' => 'val1',
                                'default_key1' => 'val2'
                            ]
                        ]
                    ]
                ],
                [
                    4 => [
                        'key1' => '1',
                        'values' => [3 => ['key1' => 1]]
                    ]
                ],
                [
                    [
                        'option_id' => '3',
                        'key1' => 'val1',
                        'default_key1' => 'val2',
                        'values' => [
                            [
                                'option_type_id' => '2',
                                'key1' => 'val1',
                                'default_key1' => 'val2'
                            ]
                        ]
                    ]
                ]
            ],
            'key2 is replaced, key1 is not (checkbox is not checked)' => [
                [
                    [
                        'option_id' => '5',
                        'key1' => 'val1',
                        'title' => 'val2',
                        'default_key1' => 'val3',
                        'default_title' => 'val4',
                        'values' => [
                            [
                                'option_type_id' => '2',
                                'key1' => 'val1',
                                'key2' => 'val2',
                                'default_key1' => 'val11',
                                'default_key2' => 'val22'
                            ]
                        ]
                    ]
                ],
                [
                    5 => [
                        'key1' => '0',
                        'title' => '1',
                        'values' => [2 => ['key1' => 1]]
                    ]
                ],
                [
                    [
                        'option_id' => '5',
                        'key1' => 'val1',
                        'title' => 'val4',
                        'default_key1' => 'val3',
                        'default_title' => 'val4',
                        'is_delete_store_title' => 1,
                        'values' => [
                            [
                                'option_type_id' => '2',
                                'key1' => 'val11',
                                'key2' => 'val2',
                                'default_key1' => 'val11',
                                'default_key2' => 'val22'
                            ]
                        ]
                    ]
                ]
            ],
            'key1 is replaced, key2 has no default value' => [
                [
                    [
                        'option_id' => '7',
                        'key1' => 'val1',
                        'key2' => 'val2',
                        'default_key1' => 'val3',
                        'values' => [
                            [
                                'option_type_id' => '2',
                                'key1' => 'val1',
                                'title' => 'val2',
                                'default_key1' => 'val11',
                                'default_title' => 'val22'
                            ]
                        ]
                    ]
                ],
                [
                    7 => [
                        'key1' => '1',
                        'key2' => '1',
                        'values' => [2 => ['key1' => 0, 'title' => 1]]
                    ]
                ],
                [
                    [
                        'option_id' => '7',
                        'key1' => 'val3',
                        'key2' => 'val2',
                        'default_key1' => 'val3',
                        'values' => [
                            [
                                'option_type_id' => '2',
                                'key1' => 'val1',
                                'title' => 'val22',
                                'default_key1' => 'val11',
                                'default_title' => 'val22',
                                'is_delete_store_title' => 1
                            ]
                        ]
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
}
