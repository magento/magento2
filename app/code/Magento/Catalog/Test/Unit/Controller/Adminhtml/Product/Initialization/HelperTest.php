<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Api\Data\ProductLinkInterface;
use \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockFilterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinksMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsHelperMock;

    /**
     * @var \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkFactoryMock;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface\Proxy|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    protected function setUp()
    {
        $this->productLinkFactoryMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductLinkInterfaceFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder('Magento\Catalog\Api\ProductRepositoryInterface\Proxy')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->jsHelperMock = $this->getMock('Magento\Backend\Helper\Js', [], [], '', false);
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->websiteMock = $this->getMock('Magento\Store\Model\Website', [], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->dateFilterMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\Filter\Date', [], [], '', false);

        $this->stockFilterMock = $this->getMock(
            'Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter',
            [],
            [],
            '',
            false
        );
        $this->productLinksMock = $this->getMock(
            'Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks',
            [],
            [],
            '',
            false
        );

        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                'setData',
                'addData',
                'unsetData',
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
            ],
            [],
            '',
            false
        );
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::initialize
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testInitialize()
    {
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

        $this->jsHelperMock = $this->getMock('\Magento\Backend\Helper\Js', [], [], '', false);
        $customOptionFactory = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $customOption = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductCustomOptionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->helper = new Helper(
            $this->requestMock,
            $this->storeManagerMock,
            $this->stockFilterMock,
            $this->productLinksMock,
            $this->jsHelperMock,
            $this->dateFilterMock,
            $customOptionFactory,
            $this->productLinkFactoryMock,
            $this->productRepositoryMock
        );

        $productData = [
            'stock_data' => ['stock_data'],
            'options' => ['option1' => ['is_delete' => true], 'option2' => ['is_delete' => false]]
        ];
        $customOptionFactory->expects($this->once())->method('create')
            ->with(['data' => ['is_delete' => false]])
            ->willReturn($customOption);
        $customOption->expects($this->once())->method('setProductSku');
        $customOption->expects($this->once())->method('setOptionId');
        $attributeNonDate = $this->getMock('Magento\Catalog\Model\ResourceModel\Eav\Attribute', [], [], '', false);
        $attributeDate = $this->getMock('Magento\Catalog\Model\ResourceModel\Eav\Attribute', [], [], '', false);

        $attributeNonDateBackEnd =
            $this->getMock('Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend', [], [], '', false);
        $attributeDateBackEnd =
            $this->getMock('Magento\Eav\Model\Entity\Attribute\Backend\Datetime', [], [], '', false);

        $attributeNonDate->expects($this->any())
            ->method('getBackend')
            ->will($this->returnValue($attributeNonDateBackEnd));

        $attributeDate->expects($this->any())
            ->method('getBackend')
            ->will($this->returnValue($attributeDateBackEnd));

        $this->stepLinkDelete();

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

        $this->productMock->expects($this->any())
            ->method('getProductLinks')
            ->willReturn([]);

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
            ->method('getSku')->willReturn('sku');

        $this->productMock->expects($this->once())
            ->method('setWebsiteIds')
            ->with([$this->websiteId]);

        $this->productMock->expects($this->any())
            ->method('getOptionsReadOnly')
            ->will($this->returnValue(false));

        $this->productMock->expects($this->once())
            ->method('setOptions')
            ->with([$customOption]);

        $this->productMock->expects($this->once())
            ->method('setCanSaveCustomOptions')
            ->with(true);

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
        $this->jsHelperMock = $this->getMock('\Magento\Backend\Helper\Js', [], [], '', false);
        $customOptionFactory = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->helper = new Helper(
            $this->requestMock,
            $this->storeManagerMock,
            $this->stockFilterMock,
            $this->productLinksMock,
            $this->jsHelperMock,
            $this->dateFilterMock,
            $customOptionFactory,
            $this->productLinkFactoryMock,
            $this->productRepositoryMock
        );
        $result = $this->helper->mergeProductOptions($productOptions, $defaultOptions);
        $this->assertEquals($expectedResults, $result);
    }

    /**
     * @return void
     */
    protected function stepLinkDelete()
    {
        $linkMock = $this->getMockBuilder(ProductLinkInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productMock->expects($this->any())
            ->method('getProductLinks')
            ->willReturn([$linkMock]);

        $this->requestMock->expects($this->at(2))
            ->method('getPost')
            ->with('links')
            ->willReturn(['upsell' => []]);

        $this->productMock->expects($this->any())
            ->method('setProductLinks')
            ->with([]);
    }
}
