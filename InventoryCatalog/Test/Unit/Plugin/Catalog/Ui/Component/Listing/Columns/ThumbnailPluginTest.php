<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Unit\Plugin\Catalog\Ui\Component\Listing\Columns;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Ui\Component\Listing\Columns\Thumbnail;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\InventoryCatalog\Plugin\Catalog\Ui\Component\Listing\Columns\ThumbnailPlugin;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThumbnailPluginTest extends TestCase
{
    /**
     * @var ThumbnailPlugin
     */
    private $plugin;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->imageHelper = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->storeWebsiteRelation = $this->getMockBuilder(StoreWebsiteRelationInterface::class)
            ->getMockForAbstractClass();

        $this->plugin = new ThumbnailPlugin(
            $this->context,
            $this->imageHelper,
            $this->urlBuilder,
            $this->storeManager,
            $this->scopeConfig,
            $this->storeWebsiteRelation
        );
    }

    /**
     * Test a thumbnail placeholder image for the admin catalog products grid in a multi-website scenario.
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testAroundPrepareDataSource(): void
    {
        $storeId = 2;
        $fieldName = 'thumbnail';
        $dataSource = [
            'data' => [
                'totalRecords' => 1,
                'items' => [
                    [
                        'entity_id' => '1',
                        'attribute_set_id' => '4',
                        'type_id' => 'simple',
                        'sku' => 'P1',
                        'status' => '1',
                        'websites' => [
                            '2'
                        ],
                        'website_ids' => [
                            '2'
                        ]
                    ],
                ],
                'showTotalRecords' => true
            ]
        ];
        $subject = $this->createMock(Thumbnail::class);
        $proceed = function () use ($dataSource) {
            return $dataSource;
        };

        $websiteIds = [1, 2];
        $websites = [];
        foreach ($websiteIds as $websiteId) {
            $websites[] = $this->createConfiguredMock(
                Website::class,
                [
                    'getId' => $websiteId
                ]
            );
        }
        $this->storeWebsiteRelation->expects($this->atLeastOnce())
            ->method('getStoreByWebsiteId')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls([1], [2, 3]);

        $this->storeManager->expects($this->exactly(2))
            ->method('getWebsites')
            ->willReturn($websites);

        $product = new DataObject($dataSource['data']['items'][0]);

        $subject->expects($this->atLeastOnce())->method('getData')->willReturn($fieldName);

        $this->scopeConfig->expects($this->atLeastOnce())->method('getValue')
            ->with("catalog/placeholder/{$fieldName}_placeholder", ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn("stores/{$storeId}/test.jpg");

        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getId')->willReturn($storeId);
        $this->storeManager->expects($this->atLeastOnce())->method('setCurrentStore')->with($storeId);

        $this->imageHelper->expects($this->atLeastOnce())->method('init')
            ->withConsecutive([$product, 'product_listing_thumbnail'], [$product, 'product_listing_thumbnail_preview'])
            ->willReturnOnConsecutiveCalls($this->imageHelper, $this->imageHelper);
        $this->imageHelper->expects($this->atLeastOnce())->method('getUrl')
            ->willReturnMap([["http://magento.local/media/catalog/product/placeholder/stores/{$storeId}/test.jpg"]]);
        $this->imageHelper->expects($this->atLeastOnce())->method('getLabel')->willReturn('Image Label');

        $this->context->expects($this->atLeastOnce())->method('getRequestParam')->with('store')->willReturn(null);
        $this->urlBuilder->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('catalog/product/edit', ['id' => $product['entity_id'], 'store' => null])
            ->willReturn("http://magento.local/admin/catalog/product/edit/id/{$product['entity_id']}/");

        $preparedDataSource = $this->plugin->aroundPrepareDataSource($subject, $proceed, $dataSource);

        foreach ($preparedDataSource['data']['items'] as $item) {
            $this->assertEquals(
                "http://magento.local/media/catalog/product/placeholder/stores/{$storeId}/test.jpg",
                $item['thumbnail_src']
            );
        }
    }

    /**
     * Test for product datasource in single (default) website scenario
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testAroundPrepareDataSourceForDefaultWebsiteOnly(): void
    {
        $dataSource = [
            'data' => [
                'totalRecords' => 1,
                'items' => [
                    [
                        'entity_id' => '1',
                        'attribute_set_id' => '4',
                        'type_id' => 'simple',
                        'sku' => 'P1',
                        'status' => '1',
                        'websites' => [
                            '1'
                        ],
                        'website_ids' => [
                            '1'
                        ]
                    ]
                ],
                'showTotalRecords' => true
            ]
        ];
        $subject = $this->createMock(Thumbnail::class);
        $proceed = function () use ($dataSource) {
            return $dataSource;
        };

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $this->storeManager->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);
        $this->assertEquals($dataSource, $this->plugin->aroundPrepareDataSource($subject, $proceed, $dataSource));
    }
}
