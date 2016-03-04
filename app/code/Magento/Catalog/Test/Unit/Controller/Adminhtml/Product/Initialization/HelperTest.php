<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

/**
 * Class HelperTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
                'getProductLinks'
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

        $this->customOptionFactoryMock->expects($this->any())
            ->method('create')
            ->with(['data' => ['is_delete' => false]])
            ->willReturn($this->customOptionMock);
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
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::initialize
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testInitialize()
    {
        $this->websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->websiteId);
        $this->storeMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($this->websiteMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with(true)
            ->willReturn($this->storeMock);
        $this->customOptionMock->expects($this->once())
            ->method('setProductSku');
        $this->customOptionMock->expects($this->once())
            ->method('setOptionId');

        $productData = [
            'stock_data' => ['stock_data'],
            'options' => ['option1' => ['is_delete' => true], 'option2' => ['is_delete' => false]]
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
        $this->requestMock->expects($this->at(3))
            ->method('getPost')
            ->with('options_use_default')
            ->willReturn(true);
        $this->stockFilterMock->expects($this->once())
            ->method('filter')
            ->with(['stock_data'])
            ->willReturn(['stock_data']);
        $this->storeManagerMock->expects($this->once())
            ->method('hasSingleStore')
            ->willReturn(true);
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

        $productData['category_ids'] = [];
        $productData['website_ids'] = [];

        $this->productMock->expects($this->once())
            ->method('addData')
            ->with($productData);
        $this->productMock->expects($this->once())
            ->method('getSku')
            ->willReturn('sku');
        $this->productMock->expects($this->once())
            ->method('setWebsiteIds')
            ->with([$this->websiteId]);
        $this->productMock->expects($this->any())
            ->method('getOptionsReadOnly')
            ->willReturn(false);
        $this->productMock->expects($this->once())
            ->method('setOptions')
            ->with([$this->customOptionMock]);

        $this->assertEquals($this->productMock, $this->helper->initialize($this->productMock));
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
                ['key' => ['key' => 'val2', 'key2' => 'val2']],
                ['key' => ['key' => 'val2', 'key2' => 'val2']],
            ],
            [
                ['key' => ['key' => 'val', 'another_key' => 'another_value']],
                ['key' => ['key' => 'val2', 'key2' => 'val2']],
                ['key' => ['key' => 'val2', 'another_key' => 'another_value', 'key2' => 'val2',]],
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
