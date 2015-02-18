<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;

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

    protected function setUp()
    {
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->jsHelperMock = $this->getMock('Magento\Backend\Helper\Js', [], [], '', false);
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->websiteMock = $this->getMock('Magento\Store\Model\Website', [], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');

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
                'getId',
                'setWebsiteIds',
                'isLockedAttribute',
                'lockAttribute',
                'unlockAttribute',
                'getOptionsReadOnly',
                'setProductOptions',
                'setCanSaveCustomOptions',
                '__sleep',
                '__wakeup'
            ],
            [],
            '',
            false
        );
    }

    /**
     * @covers Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::initialize
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
        $this->helper = new Helper(
            $this->requestMock,
            $this->storeManagerMock,
            $this->stockFilterMock,
            $this->productLinksMock,
            $this->jsHelperMock
        );

        $productData = [
            'stock_data' => ['stock_data'],
            'options' => ['option1', 'option2']
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

        $this->productMock->expects($this->once())
            ->method('lockAttribute')
            ->with('media');

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

        $this->productMock->expects($this->once())
            ->method('setProductOptions')
            ->with($productData['options']);

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
        $this->helper = new Helper(
            $this->requestMock,
            $this->storeManagerMock,
            $this->stockFilterMock,
            $this->productLinksMock,
            $this->jsHelperMock
        );
        $result = $this->helper->mergeProductOptions($productOptions, $defaultOptions);
        $this->assertEquals($expectedResults, $result);
    }
}
