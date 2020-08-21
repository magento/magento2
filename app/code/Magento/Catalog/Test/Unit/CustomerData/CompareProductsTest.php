<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\CustomerData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\CustomerData\CompareProducts;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompareProductsTest extends TestCase
{
    /**
     * @var CompareProducts
     */
    private $model;

    /**
     * @var Compare|MockObject
     */
    private $helperMock;

    /**
     * @var Url|MockObject
     */
    private $productUrlMock;

    /**
     * @var Output|MockObject
     */
    private $outputHelperMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var array
     */
    private $productValueMap = [
        'id' => 'getId',
        ProductInterface::NAME => 'getName'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->helperMock = $this->getMockBuilder(Compare::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlMock = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->outputHelperMock = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            CompareProducts::class,
            [
                'helper' => $this->helperMock,
                'productUrl' => $this->productUrlMock,
                'outputHelper' => $this->outputHelperMock,
                'scopeConfig'  => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Prepare compare items collection.
     *
     * @param array $items
     * @return MockObject
     */
    private function getItemCollectionMock(array $items) : MockObject
    {
        $itemCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        return $itemCollectionMock;
    }

    /**
     * Prepare product mocks objects and add corresponding method mocks for helpers.
     *
     * @param array $dataSet
     * @return array
     */
    private function prepareProductsWithCorrespondingMocks(array $dataSet) : array
    {
        $items = [];
        $urlMap = [];
        $outputMap = [];
        $helperMap = [];
        $productScopeMap = [];

        $count = count($dataSet);

        foreach ($dataSet as $data) {
            $item = $this->getProductMock($data);
            $items[] = $item;

            $outputMap[] = [$item, $data['name'], 'name', 'productName#' . $data['id']];
            $helperMap[] = [$item, 'http://remove.url/' . $data['id']];
            $urlMap[] = [$item, [], 'http://product.url/' . $data['id']];
            $productScopeMap[] = [$item, 'store-' . $data['id']];
        }

        $this->productUrlMock->expects($this->exactly($count))
            ->method('getUrl')
            ->willReturnMap($urlMap);

        $this->outputHelperMock->expects($this->exactly($count))
            ->method('productAttribute')
            ->willReturnMap($outputMap);

        $this->helperMock->expects($this->exactly($count))
            ->method('getPostDataRemove')
            ->willReturnMap($helperMap);

        return $items;
    }

    /**
     * Prepare mock of product object.
     *
     * @param array $data
     * @return MockObject
     */
    private function getProductMock(array $data) : MockObject
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($data as $index => $value) {
            $product->expects($this->once())
                ->method($this->productValueMap[$index])
                ->willReturn($value);
        }

        return $product;
    }

    public function testGetSectionData()
    {
        $dataSet = [
            ['id' => 1, 'name' => 'product#1'],
            ['id' => 2, 'name' => 'product#2'],
            ['id' => 3, 'name' => 'product#3']
        ];

        $count = count($dataSet);

        $this->helperMock->expects($this->once())
            ->method('getItemCount')
            ->willReturn($count);

        $items = $this->prepareProductsWithCorrespondingMocks($dataSet);

        $itemCollectionMock = $this->getItemCollectionMock($items);

        $this->helperMock->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($itemCollectionMock);

        $this->helperMock->expects($this->once())
            ->method('getListUrl')
            ->willReturn('http://list.url');

        $this->assertEquals(
            [
                'count' => $count,
                'countCaption' =>  __('%1 items', $count),
                'listUrl' => 'http://list.url',
                'items' => [
                    [
                        'id' => 1,
                        'product_url' => 'http://product.url/1',
                        'name' => 'productName#1',
                        'remove_url' => 'http://remove.url/1',
                        'productScope' => null
                    ],
                    [
                        'id' => 2,
                        'product_url' => 'http://product.url/2',
                        'name' => 'productName#2',
                        'remove_url' => 'http://remove.url/2',
                        'productScope' => null
                    ],
                    [
                        'id' => 3,
                        'product_url' => 'http://product.url/3',
                        'name' => 'productName#3',
                        'remove_url' => 'http://remove.url/3',
                        'productScope' => null
                    ]
                ]
            ],
            $this->model->getSectionData()
        );
    }

    public function testGetSectionDataNoItems()
    {
        $count = 0;

        $this->helperMock->expects($this->once())
            ->method('getItemCount')
            ->willReturn($count);

        $this->helperMock->expects($this->never())
            ->method('getItemCollection');

        $this->helperMock->expects($this->once())
            ->method('getListUrl')
            ->willReturn('http://list.url');

        $this->assertEquals(
            [
                'count' => $count,
                'countCaption' =>  __('%1 items', $count),
                'listUrl' => 'http://list.url',
                'items' => []
            ],
            $this->model->getSectionData()
        );
    }

    public function testGetSectionDataSingleItem()
    {
        $count = 1;

        $this->helperMock->expects($this->once())
            ->method('getItemCount')
            ->willReturn($count);

        $items = $this->prepareProductsWithCorrespondingMocks(
            [
                [
                    'id' => 12345,
                    'name' => 'SingleProduct'
                ]
            ]
        );

        $itemCollectionMock = $this->getItemCollectionMock($items);

        $this->helperMock->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($itemCollectionMock);

        $this->helperMock->expects($this->once())
            ->method('getListUrl')
            ->willReturn('http://list.url');

        $this->assertEquals(
            [
                'count' => 1,
                'countCaption' =>  __('1 item'),
                'listUrl' => 'http://list.url',
                'items' => [
                    [
                        'id' => 12345,
                        'product_url' => 'http://product.url/12345',
                        'name' => 'productName#12345',
                        'remove_url' => 'http://remove.url/12345',
                        'productScope' => null
                    ]
                ]
            ],
            $this->model->getSectionData()
        );
    }
}
