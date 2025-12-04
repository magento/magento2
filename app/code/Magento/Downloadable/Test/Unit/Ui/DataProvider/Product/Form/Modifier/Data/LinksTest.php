<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Data;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Downloadable\Model\Link as LinkModel;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Data\Links;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinksTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var DownloadableFile|MockObject
     */
    protected $downloadableFileMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var LinkModel|MockObject
     */
    protected $linkModelMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var Links
     */
    protected $links;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Test\Unit\Helper\ProductTestHelper::class,
            ['getId', 'getTypeId', 'getLinksTitle', 'getTypeInstance', 'getStoreId']
        );
        $this->locatorMock = $this->createMock(LocatorInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->downloadableFileMock = $this->createMock(DownloadableFile::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->linkModelMock = $this->createMock(LinkModel::class);
        $this->links = $this->objectManagerHelper->getObject(
            Links::class,
            [
                'escaper' => $this->escaperMock,
                'locator' => $this->locatorMock,
                'scopeConfig' => $this->scopeConfigMock,
                'downloadableFile' => $this->downloadableFileMock,
                'urlBuilder' => $this->urlBuilderMock,
                'linkModel' => $this->linkModelMock,
            ]
        );
    }

    /**
     * Test case for getLinksTitle
     *
     * @param int|null $id
     * @param string $typeId
     * @param InvokedCount $expectedGetTitle
     * @param InvokedCount $expectedGetValue
     * @return void
     */
    #[DataProvider('getLinksTitleDataProvider')]
    public function testGetLinksTitle($id, $typeId, $expectedGetTitle, $expectedGetValue)
    {
        $title = 'My Title';
        $this->locatorMock->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $this->productMock->method('getTypeId')->willReturn($typeId);
        $this->productMock->expects($this->$expectedGetTitle())
            ->method('getLinksTitle')
            ->willReturn($title);
        $this->scopeConfigMock->expects($this->$expectedGetValue())
            ->method('getValue')
            ->willReturn($title);

        $this->assertEquals($title, $this->links->getLinksTitle());
    }

    /**
     * @return array
     */
    public static function getLinksTitleDataProvider()
    {
        return [
            [
                'id' => 1,
                'typeId' => Type::TYPE_DOWNLOADABLE,
                'expectedGetTitle' => 'once',
                'expectedGetValue' => 'never',
            ],
            [
                'id' => null,
                'typeId' => Type::TYPE_DOWNLOADABLE,
                'expectedGetTitle' => 'never',
                'expectedGetValue' => 'once',
            ],
            [
                'id' => 1,
                'typeId' => 'someType',
                'expectedGetTitle' => 'never',
                'expectedGetValue' => 'once',
            ],
            [
                'id' => null,
                'typeId' => 'someType',
                'expectedGetTitle' => 'never',
                'expectedGetValue' => 'once',
            ],
        ];
    }

    /**
     * Test case for getLinksData
     *
     * @param $productTypeMock
     * @param string $typeId
     * @param int $storeId
     * @param array $links
     * @param array $expectedLinksData
     * @return void
     */
    #[DataProvider('getLinksDataProvider')]
    public function testGetLinksData(
        $productTypeMock,
        string $typeId,
        int $storeId,
        array $links,
        array $expectedLinksData
    ): void {
        $productTypeMock = $productTypeMock($this);
        $links[0] = $links[0]($this);
        $this->locatorMock->method('getProduct')->willReturn($this->productMock);
        if (!empty($expectedLinksData)) {
            $this->escaperMock->method('escapeHtml')->willReturn($expectedLinksData['title']);
        }
        $this->productMock->method('getTypeId')->willReturn($typeId);
        $this->productMock->method('getTypeInstance')->willReturn($productTypeMock);
        $this->productMock->method('getStoreId')->willReturn($storeId);
        $productTypeMock->method('getLinks')->willReturn($links);
        $getLinksData = $this->links->getLinksData();
        if (!empty($getLinksData)) {
            $actualResult = current($getLinksData);
        } else {
            $actualResult = $getLinksData;
        }
        $this->assertEquals($expectedLinksData, $actualResult);
    }

    /**
     * Get Links data provider
     *
     * @return array
     */
    public static function getLinksDataProvider()
    {
        $productData1 = [
            'link_id' => '1',
            'title' => 'test',
            'price' => '0.00',
            'number_of_downloads' => '0',
            'is_shareable' => '1',
            'link_url' => 'http://cdn.sourcebooks.com/test',
            'type' => 'url',
            'sample' =>
                [
                    'url' => null,
                    'type' => null,
                ],
            'sort_order' => '1',
            'is_unlimited' => '1',
            'use_default_price' => '0',
            'use_default_title' => '0',

        ];
        $productData2 = $productData1;
        unset($productData2['use_default_price']);
        unset($productData2['use_default_title']);
        $productData3 = [
            'link_id' => '1',
            'title' => 'simple',
            'price' => '10.00',
            'number_of_downloads' => '0',
            'is_shareable' => '0',
            'link_url' => '',
            'type' => 'simple',
            'sample' =>
                [
                    'url' => null,
                    'type' => null,
                ],
            'sort_order' => '1',
            'is_unlimited' => '1',
            'use_default_price' => '0',
            'use_default_title' => '0',

        ];
        $linkMock1 = static fn (self $testCase) => $testCase->getLinkMockObject($productData1, '1', '1');
        $linkMock2 = static fn (self $testCase) => $testCase->getLinkMockObject($productData1, '0', '0');
        $linkMock3 = static fn (self $testCase) => $testCase->getLinkMockObject($productData3, '0', '0');
        return [
            'test case for downloadable product for default store' => [
                'productTypeMock' => static fn (self $testCase) => $testCase->createMock(Type::class),
                'typeId' => Type::TYPE_DOWNLOADABLE,
                'storeId' => 1,
                'links' => [$linkMock1],
                'expectedLinksData' => $productData1
            ],
            'test case for downloadable product for all store' => [
                'productTypeMock' => static fn (self $testCase) => $testCase->createMock(Type::class),
                'typeId' => Type::TYPE_DOWNLOADABLE,
                'storeId' => 0,
                'links' => [$linkMock2],
                'expectedLinksData' => $productData2
            ],
            'test case for simple product for default store' => [
                'productTypeMock' => static fn (self $testCase) => $testCase->createMock(Type::class),
                'typeId' => ProductType::TYPE_SIMPLE,
                'storeId' => 1,
                'links' => [$linkMock3],
                'expectedLinksData' => []
            ],
        ];
    }

    /**
     * Data provider for getLinks
     *
     * @param array $productData
     * @param string $useDefaultPrice
     * @param string $useDefaultTitle
     * @return MockObject
     */
    protected function getLinkMockObject(
        array $productData,
        string $useDefaultPrice,
        string $useDefaultTitle
    ): MockObject {
        $linkMock = $this->createPartialMock(
            \Magento\Downloadable\Test\Unit\Helper\LinkTestHelper::class,
            [
                'getId', 'getTitle', 'getPrice', 'getNumberOfDownloads', 'getIsShareable', 'getSortOrder',
                'getLinkType', 'getLinkUrl', 'getSampleType', 'getSampleUrl', 'getWebsitePrice', 'getStoreTitle'
            ]
        );
        $linkMock->method('getId')->willReturn($productData['link_id']);
        $linkMock->method('getTitle')->willReturn($productData['title']);
        $linkMock->method('getPrice')->willReturn($productData['price']);
        $linkMock->method('getNumberOfDownloads')->willReturn($productData['number_of_downloads']);
        $linkMock->method('getIsShareable')->willReturn($productData['is_shareable']);
        $linkMock->method('getLinkUrl')->willReturn($productData['link_url']);
        $linkMock->method('getLinkType')->willReturn($productData['type']);
        $linkMock->method('getSampleUrl')->willReturn($productData['sample']['url']);
        $linkMock->method('getSampleType')->willReturn($productData['sample']['type']);
        $linkMock->method('getSortOrder')->willReturn($productData['sort_order']);
        $linkMock->method('getWebsitePrice')->willReturn($useDefaultPrice);
        $linkMock->method('getStoreTitle')->willReturn($useDefaultTitle);
        return $linkMock;
    }
}
