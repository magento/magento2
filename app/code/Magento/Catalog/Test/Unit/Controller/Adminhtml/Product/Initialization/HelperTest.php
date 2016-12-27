<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var Helper
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
     * @var ProductLinks|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinksMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateFilterMock;

    /**
     * @var int
     */
    protected $websiteId = 1;

    /**
     * @var \Magento\Backend\Helper\Js|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsHelperMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();

        $this->jsHelperMock = $this->getMock(\Magento\Backend\Helper\Js::class, [], [], '', false);
        $this->storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->websiteMock = $this->getMock(\Magento\Store\Model\Website::class, [], [], '', false);
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->dateFilterMock = $this->getMock(
            \Magento\Framework\Stdlib\DateTime\Filter\Date::class,
            [],
            [],
            '',
            false
        );

        $this->stockFilterMock = $this->getMockBuilder(StockDataFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productLinksMock = $this->getMock(
            \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks::class,
            [],
            [],
            '',
            false
        );

        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'addData',
                'getId',
                'setWebsiteIds',
                'isLockedAttribute',
                'lockAttribute',
                'getAttributes',
                'unlockAttribute',
                'getOptionsReadOnly',
                'setCanSaveCustomOptions',
                '__sleep',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $this->helper = $this->objectManager->getObject(Helper::class, [
            'request' => $this->requestMock,
            'storeManager' => $this->storeManagerMock,
            'stockFilter' => $this->stockFilterMock,
            'productLinks' => $this->productLinksMock,
            'jsHelper' => $this->jsHelperMock,
            'dateFilter' => $this->dateFilterMock,
        ]);
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::initialize
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testInitialize()
    {
        $optionsData = [
            'option1' => ['is_delete' => false, 'name' => 'name1', 'price' => 'price1', 'option_id' => '13'],
            'option2' => ['is_delete' => false, 'name' => 'name1', 'price' => 'price1', 'option_id' => '14',
                'values' => [
                    'value1' => ['is_delete' =>''],
                    'value2' => ['is_delete' =>'1']
                ]
            ],
        ];
        $productData = [
            'stock_data' => ['stock_data'],
            'options' => $optionsData,
        ];
        
        $this->websiteMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($this->websiteId));

        $this->storeMock->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($this->websiteMock));

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with(true)
            ->will($this->returnValue($this->storeMock));

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
            ->will($this->returnValue($attributeNonDateBackEnd));

        $attributeDate->expects($this->any())
            ->method('getBackend')
            ->will($this->returnValue($attributeDateBackEnd));


        $attributeNonDateBackEnd->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('non-datetime'));

        $attributeDateBackEnd->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('datetime'));

        $attributesArray = [
            $attributeNonDate,
            $attributeDate
        ];

        $useDefaults = ['attributeCode1', 'attributeCode2'];

        $this->requestMock->expects($this->any())->method('getPost')->willReturnMap(
            [
                ['product', [], $productData],
                ['use_default', null, $useDefaults]
            ]
        );

        $this->requestMock->expects($this->at(0))
            ->method('getPost')
            ->with('product')
            ->will($this->returnValue($productData));

        $this->requestMock->expects($this->at(1))
            ->method('getPost')
            ->with('use_default')
            ->will($this->returnValue($useDefaults));

        $this->requestMock->expects($this->at(3))
            ->method('getPost')
            ->with('options_use_default')
            ->will($this->returnValue(true));

        $this->requestMock->expects($this->at(4))
            ->method('getPost')
            ->with('affect_product_custom_options')
            ->will($this->returnValue(true));

        $this->stockFilterMock->expects($this->once())
            ->method('filter')
            ->with(['stock_data'])
            ->will($this->returnValue(['stock_data']));

        $this->storeManagerMock->expects($this->once())
            ->method('hasSingleStore')
            ->will($this->returnValue(true));

        $this->productLinksMock->expects($this->once())
            ->method('initializeLinks')
            ->with($this->productMock)
            ->will($this->returnValue($this->productMock));

        $this->productMock->expects($this->once())
            ->method('isLockedAttribute')
            ->with('media')
            ->will($this->returnValue(true));

        $this->productMock->expects($this->once())
            ->method('unlockAttribute')
            ->with('media');

        $this->productMock->expects($this->once())
            ->method('lockAttribute')
            ->with('media');

        $this->productMock->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributesArray));

        $productData['category_ids'] = [];
        $productData['website_ids'] = [];

        $this->productMock->expects($this->once())
            ->method('addData')
            ->with($productData);

        $this->productMock->expects($this->once())
            ->method('setWebsiteIds')
            ->with([$this->websiteId]);

        $this->productMock->expects($this->any())
            ->method('getOptionsReadOnly')
            ->will($this->returnValue(false));

        $this->assertEquals($this->productMock, $this->helper->initialize($this->productMock));

        $productOptions = $this->productMock->getProductOptions();
        $this->assertTrue(2 == count($productOptions));
        $this->assertTrue(1 == count($productOptions['option2']['values']));
    }

    /**
     * Data provider for testMergeProductOptions
     *
     * @return array
     */
    public function mergeProductOptionsDataProvider()
    {
        return [
            [
                null,
                [],
                [],
            ],
            [
                ['key' => 'val'],
                null,
                ['key' => 'val'],
            ],
            [
                ['key' => ['key' => 'val']],
                ['key' => ['key' => 'val2' , 'key2' => 'val2']],
                ['key' => ['key' => 'val2' , 'key2' => 'val2']],
            ],
            [
                ['key' => ['key' => 'val', 'another_key' => 'another_value']],
                ['key' => ['key' => 'val2' , 'key2' => 'val2']],
                ['key' => ['key' => 'val2' , 'another_key' => 'another_value', 'key2' => 'val2', ]],
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
