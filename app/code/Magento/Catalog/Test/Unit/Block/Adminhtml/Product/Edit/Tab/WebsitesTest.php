<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\ElementCreator;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Websites;
use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Directory\ReadInterface as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Group;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group\Collection as GroupCollection;
use Magento\Store\Model\ResourceModel\Store\Collection as StoreCollection;
use Magento\Store\Model\ResourceModel\Website\Collection as WebsiteCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Websites tab block.
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Websites
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WebsitesTest extends TestCase
{
    /**
     * @var Websites
     */
    private Websites $block;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registry;

    /**
     * @var Product|MockObject
     */
    private Product|MockObject $product;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface|MockObject $storeManager;

    /**
     * @var WebsiteFactory|MockObject
     */
    private WebsiteFactory|MockObject $websiteFactory;

    /**
     * @var GroupFactory|MockObject
     */
    private GroupFactory|MockObject $groupFactory;

    /**
     * @var StoreFactory|MockObject
     */
    private StoreFactory|MockObject $storeFactory;

    /**
     * @var Escaper|MockObject
     */
    private Escaper|MockObject $escaper;

    /**
     * Set up test dependencies and mocks.
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->prepareObjectManager();

        $this->registry = $this->createMock(Registry::class);

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStoreId', 'getId', 'getWebsiteIds'])
            ->addMethods(['getWebsitesReadonly'])
            ->getMock();
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->websiteFactory = $this->createMock(WebsiteFactory::class);
        $this->groupFactory = $this->createMock(GroupFactory::class);
        $this->storeFactory = $this->createMock(StoreFactory::class);
        $this->escaper = $this->createMock(Escaper::class);

        $context = $this->createMock(Context::class);
        $context->method('getStoreManager')->willReturn($this->storeManager);
        $context->method('getEscaper')->willReturn($this->escaper);

        $this->block = new Websites(
            $context,
            $this->websiteFactory,
            $this->groupFactory,
            $this->storeFactory,
            $this->registry
        );

        $this->registry->method('registry')->with('product')->willReturn($this->product);
    }

    /**
     * Test getProduct returns correct product.
     *
     * @return void
     */
    public function testGetProduct(): void
    {
        $this->assertSame($this->product, $this->block->getProduct());
    }

    /**
     * Test getProduct returns null when registry has no product.
     *
     * @return void
     */
    public function testGetProductReturnsNullWhenRegistryEmpty(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('registry')->with('product')->willReturn(null);

        $context = $this->createMock(Context::class);
        $context->method('getStoreManager')->willReturn($this->storeManager);
        $context->method('getEscaper')->willReturn($this->escaper);

        $block = new Websites(
            $context,
            $this->websiteFactory,
            $this->groupFactory,
            $this->storeFactory,
            $registry
        );

        $this->assertNull($block->getProduct());
    }

    /**
     * Test getStoreId throws Error when product is null.
     *
     * @return void
     */
    public function testGetStoreIdThrowsErrorWhenProductNull(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('registry')->with('product')->willReturn(null);

        $context = $this->createMock(Context::class);
        $context->method('getStoreManager')->willReturn($this->storeManager);
        $context->method('getEscaper')->willReturn($this->escaper);

        $block = new Websites(
            $context,
            $this->websiteFactory,
            $this->groupFactory,
            $this->storeFactory,
            $registry
        );

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function getStoreId() on null');
        $block->getStoreId();
    }

    /**
     * Test getProductId throws Error when product is null.
     *
     * @return void
     */
    public function testGetProductIdThrowsErrorWhenProductNull(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('registry')->with('product')->willReturn(null);

        $context = $this->createMock(Context::class);
        $context->method('getStoreManager')->willReturn($this->storeManager);
        $context->method('getEscaper')->willReturn($this->escaper);

        $block = new Websites(
            $context,
            $this->websiteFactory,
            $this->groupFactory,
            $this->storeFactory,
            $registry
        );

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function getId() on null');
        $block->getProductId();
    }

    /**
     * Test getWebsites throws Error when product is null.
     *
     * @return void
     */
    public function testGetWebsitesThrowsErrorWhenProductNull(): void
    {
        $registry = $this->createMock(Registry::class);
        $registry->method('registry')->with('product')->willReturn(null);

        $context = $this->createMock(Context::class);
        $context->method('getStoreManager')->willReturn($this->storeManager);
        $context->method('getEscaper')->willReturn($this->escaper);

        $block = new Websites(
            $context,
            $this->websiteFactory,
            $this->groupFactory,
            $this->storeFactory,
            $registry
        );

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function getWebsiteIds() on null');
        $block->getWebsites();
    }

    /**
     * Test getStoreId returns correct store ID.
     *
     * @return void
     */
    public function testGetStoreId(): void
    {
        $this->product->method('getStoreId')->willReturn(5);
        $this->assertSame(5, $this->block->getStoreId());
    }

    /**
     * Test getProductId returns correct product ID.
     *
     * @return void
     */
    public function testGetProductId(): void
    {
        $this->product->method('getId')->willReturn(123);
        $this->assertSame(123, $this->block->getProductId());
    }

    /**
     * Test getProductId returns null for new product (no ID yet).
     *
     * @return void
     */
    public function testGetProductIdReturnsNullForNewProduct(): void
    {
        $this->product->method('getId')->willReturn(null);
        $this->assertNull($this->block->getProductId());
    }

    /**
     * Test getProductId returns zero when product ID is zero.
     *
     * @return void
     */
    public function testGetProductIdReturnsZero(): void
    {
        $this->product->method('getId')->willReturn(0);
        $this->assertSame(0, $this->block->getProductId());
    }

    /**
     * Test getWebsites returns correct website IDs.
     *
     * @return void
     */
    public function testGetWebsites(): void
    {
        $websiteIds = [1, 2, 3];
        $this->product->method('getWebsiteIds')->willReturn($websiteIds);
        $this->assertSame($websiteIds, $this->block->getWebsites());
    }

    /**
     * Test getWebsites returns empty array when product has no websites.
     *
     * @return void
     */
    public function testGetWebsitesReturnsEmptyArray(): void
    {
        $this->product->method('getWebsiteIds')->willReturn([]);
        $this->assertSame([], $this->block->getWebsites());
        $this->assertEmpty($this->block->getWebsites());
    }

    /**
     * Test hasWebsite method returns correct value.
     *
     * @param int $websiteId
     * @param bool $expected
     * @return void
     * @dataProvider hasWebsiteDataProvider
     */
    public function testHasWebsite(int $websiteId, bool $expected): void
    {
        $this->product->method('getWebsiteIds')->willReturn([1, 2, 3]);
        $this->assertSame($expected, $this->block->hasWebsite($websiteId));
    }

    /**
     * Data provider for testHasWebsite.
     *
     * @return array
     */
    public static function hasWebsiteDataProvider(): array
    {
        return [
            'website_exists' => [2, true],
            'website_not_exists' => [999, false],
        ];
    }

    /**
     * Test isReadonly method returns correct value.
     *
     * @param bool $websitesReadonly
     * @param bool $expected
     * @return void
     * @dataProvider isReadonlyDataProvider
     */
    public function testIsReadonly(bool $websitesReadonly, bool $expected): void
    {
        $this->product->method('getWebsitesReadonly')->willReturn($websitesReadonly);
        $this->assertSame($expected, $this->block->isReadonly());
    }

    /**
     * Data provider for testIsReadonly.
     *
     * @return array
     */
    public static function isReadonlyDataProvider(): array
    {
        return [
            'readonly_true' => [true, true],
            'readonly_false' => [false, false],
        ];
    }

    /**
     * Test getStoreName returns correct store name.
     *
     * @return void
     * @throws Exception
     */
    public function testGetStoreName(): void
    {
        $store = $this->createMock(Store::class);
        $store->method('getName')->willReturn('Main Store');
        $this->storeManager->method('getStore')->with(1)->willReturn($store);

        $this->assertSame('Main Store', $this->block->getStoreName(1));
    }

    /**
     * Test getStoreName throws exception for invalid store ID.
     *
     * @return void
     */
    public function testGetStoreNameThrowsExceptionForInvalidStore(): void
    {
        $this->storeManager->method('getStore')
            ->with(99999)
            ->willThrowException(new NoSuchEntityException(
                __('The store that was requested wasn\'t found.')
            ));

        $this->expectException(NoSuchEntityException::class);
        $this->block->getStoreName(99999);
    }

    /**
     * Test getChooseFromStoreHtml generates correct HTML structure.
     *
     * @param array $productWebsites
     * @param array $websiteData
     * @param array $groupData
     * @param array $storeData
     * @param int $targetStoreId
     * @param array $expectedContains
     * @param array $expectedNotContains
     * @return void
     * @dataProvider getChooseFromStoreHtmlDataProvider
     * @throws Exception
     */
    public function testGetChooseFromStoreHtml(
        array $productWebsites,
        array $websiteData,
        array $groupData,
        array $storeData,
        int $targetStoreId,
        array $expectedContains,
        array $expectedNotContains = []
    ): void {
        $this->product->method('getWebsiteIds')->willReturn($productWebsites);

        $stores = [];
        foreach ($storeData as $sData) {
            $store = $this->createMock(Store::class);
            $store->method('getId')->willReturn($sData['id']);
            $store->method('getName')->willReturn($sData['name']);
            $store->method('getGroupId')->willReturn($sData['group_id']);
            $stores[] = $store;
        }

        $storeCollection = $this->createMock(StoreCollection::class);
        $storeCollection->method('getIterator')->willReturn(new \ArrayIterator($stores));
        $storeCollection->method('addIdFilter')->willReturnSelf();

        $groups = [];
        foreach ($groupData as $gData) {
            $group = $this->getMockBuilder(Group::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getName', 'getWebsiteId', 'getStoreCollection'])
                ->addMethods(['getGroupId'])
                ->getMock();
            $group->method('getName')->willReturn($gData['name']);
            $group->method('getWebsiteId')->willReturn($gData['website_id']);
            $group->method('getGroupId')->willReturn($gData['id']);
            $group->method('getStoreCollection')->willReturn($storeCollection);
            $groups[] = $group;
        }

        $groupCollection = $this->createMock(GroupCollection::class);
        $groupCollection->method('getIterator')->willReturn(new \ArrayIterator($groups));

        $websites = [];
        foreach ($websiteData as $wData) {
            $website = $this->createMock(Website::class);
            $website->method('getId')->willReturn($wData['id']);
            $website->method('getName')->willReturn($wData['name']);
            $website->method('getGroupCollection')->willReturn($groupCollection);
            $websites[] = $website;
        }

        $websiteCollection = $this->createMock(WebsiteCollection::class);
        $websiteCollection->method('getIterator')->willReturn(new \ArrayIterator($websites));

        $websiteInstance = $this->createMock(Website::class);
        $websiteInstance->method('getResourceCollection')->willReturn($websiteCollection);
        $this->websiteFactory->method('create')->willReturn($websiteInstance);

        $websiteCollection->method('addIdFilter')->willReturnSelf();
        $websiteCollection->method('load')->willReturnSelf();

        $groupInstance = $this->createMock(Group::class);
        $groupInstance->method('getCollection')->willReturn($groupCollection);
        $this->groupFactory->method('create')->willReturn($groupInstance);

        $groupCollection->method('addFieldToFilter')->willReturnSelf();
        $groupCollection->method('setOrder')->willReturnSelf();
        $groupCollection->method('load')->willReturnSelf();

        $storeInstance = $this->createMock(Store::class);
        $storeInstance->method('getCollection')->willReturn($storeCollection);
        $this->storeFactory->method('create')->willReturn($storeInstance);

        $storeCollection->method('addFieldToFilter')->willReturnSelf();
        $storeCollection->method('setOrder')->willReturnSelf();
        $storeCollection->method('load')->willReturnSelf();

        if (!empty($websiteData) && strpos($websiteData[0]['name'], '<') !== false) {
            $this->escaper->method('escapeHtml')->willReturnCallback(function ($value) {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
            });
        } else {
            $this->escaper->method('escapeHtml')->willReturnArgument(0);
        }

        $storeTo = $this->createMock(Store::class);
        $storeTo->method('getId')->willReturn($targetStoreId);

        $html = $this->block->getChooseFromStoreHtml($storeTo);

        foreach ($expectedContains as $expectedString) {
            $this->assertStringContainsString($expectedString, $html);
        }

        foreach ($expectedNotContains as $unexpectedString) {
            $this->assertStringNotContainsString($unexpectedString, $html);
        }
    }

    /**
     * Data provider for testGetChooseFromStoreHtml.
     *
     * @return array
     */
    public static function getChooseFromStoreHtmlDataProvider(): array
    {
        return [
            'basic_structure' => self::getBasicStructureData(),
            'multiple_groups' => self::getMultipleGroupsData(),
            'skips_unassigned_websites' => self::getSkipsUnassignedWebsitesData(),
            'no_websites_assigned' => self::getNoWebsitesAssignedData(),
            'html_escaping' => self::getHtmlEscapingData()
        ];
    }

    /**
     * Basic structure test data.
     *
     * @return array
     */
    private static function getBasicStructureData(): array
    {
        return [
            'productWebsites' => [1],
            'websiteData' => [['id' => 1, 'name' => 'Main Website']],
            'groupData' => [['id' => 1, 'name' => 'Main Store', 'website_id' => 1]],
            'storeData' => [['id' => 1, 'name' => 'Default Store View', 'group_id' => 1]],
            'targetStoreId' => 2,
            'expectedContains' => [
                '<select', 'name="copy_to_stores[2]"', 'disabled="disabled"',
                '<option value="0">Default Values</option>', 'Main Website',
                'Main Store', 'Default Store View', '<option value="1">',
                '</optgroup>', '</select>'
            ],
            'expectedNotContains' => []
        ];
    }

    /**
     * Multiple groups test data.
     *
     * @return array
     */
    private static function getMultipleGroupsData(): array
    {
        return [
            'productWebsites' => [1],
            'websiteData' => [['id' => 1, 'name' => 'Main Website']],
            'groupData' => [
                ['id' => 1, 'name' => 'Store Group 1', 'website_id' => 1],
                ['id' => 2, 'name' => 'Store Group 2', 'website_id' => 1]
            ],
            'storeData' => [
                ['id' => 1, 'name' => 'Store 1', 'group_id' => 1],
                ['id' => 2, 'name' => 'Store 2', 'group_id' => 2]
            ],
            'targetStoreId' => 5,
            'expectedContains' => [
                'Main Website', 'Store Group 1', 'Store Group 2', 'Store 1',
                'Store 2', '<option value="1">', '<option value="2">',
                'name="copy_to_stores[5]"', '</optgroup>', '</select>'
            ],
            'expectedNotContains' => []
        ];
    }

    /**
     * Skips unassigned websites test data.
     *
     * @return array
     */
    private static function getSkipsUnassignedWebsitesData(): array
    {
        return [
            'productWebsites' => [1],
            'websiteData' => [
                ['id' => 1, 'name' => 'Assigned Website'],
                ['id' => 2, 'name' => 'Skipped Website']
            ],
            'groupData' => [['id' => 1, 'name' => 'Main Store', 'website_id' => 1]],
            'storeData' => [['id' => 1, 'name' => 'Default Store View', 'group_id' => 1]],
            'targetStoreId' => 2,
            'expectedContains' => ['Assigned Website', 'Main Store', 'Default Store View'],
            'expectedNotContains' => ['Skipped Website']
        ];
    }

    /**
     * No websites assigned test data.
     *
     * @return array
     */
    private static function getNoWebsitesAssignedData(): array
    {
        return [
            'productWebsites' => [],
            'websiteData' => [['id' => 1, 'name' => 'Unassigned Website']],
            'groupData' => [],
            'storeData' => [],
            'targetStoreId' => 1,
            'expectedContains' => ['<select', 'Default Values', '</select>'],
            'expectedNotContains' => ['Unassigned Website']
        ];
    }

    /**
     * HTML escaping test data.
     *
     * @return array
     */
    private static function getHtmlEscapingData(): array
    {
        return [
            'productWebsites' => [1],
            'websiteData' => [
                ['id' => 1, 'name' => '<script type="text/x-magento-init">alert("xss")</script>']
            ],
            'groupData' => [['id' => 1, 'name' => '<b>Bold Store</b>', 'website_id' => 1]],
            'storeData' => [['id' => 1, 'name' => '"Quoted Store"', 'group_id' => 1]],
            'targetStoreId' => 2,
            'expectedContains' => [
                '&lt;script type=&quot;text/x-magento-init&quot;&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
                '&lt;b&gt;Bold Store&lt;/b&gt;', '&quot;Quoted Store&quot;',
                '</optgroup>', '</select>', 'name="copy_to_stores[2]"'
            ],
            'expectedNotContains' => [
                '<script type="text/x-magento-init">alert', '<b>Bold Store</b>'
            ]
        ];
    }

    /**
     * Test getChooseFromStoreHtml caches HTML and reuses it for different stores.
     *
     * @return void
     * @throws Exception
     */
    public function testGetChooseFromStoreHtmlCachesResult(): void
    {
        $this->product->method('getWebsiteIds')->willReturn([1]);

        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(1);
        $store->method('getName')->willReturn('Default Store View');
        $store->method('getGroupId')->willReturn(1);

        $storeCollection = $this->createMock(StoreCollection::class);
        $storeCollection->method('getIterator')->willReturn(new \ArrayIterator([$store]));
        $storeCollection->method('addFieldToFilter')->willReturnSelf();
        $storeCollection->method('setOrder')->willReturnSelf();
        $storeCollection->method('load')->willReturnSelf();

        $group = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getName', 'getWebsiteId', 'getStoreCollection'])
            ->addMethods(['getGroupId'])
            ->getMock();
        $group->method('getName')->willReturn('Main Store');
        $group->method('getWebsiteId')->willReturn(1);
        $group->method('getGroupId')->willReturn(1);
        $group->method('getStoreCollection')->willReturn($storeCollection);

        $groupCollection = $this->createMock(GroupCollection::class);
        $groupCollection->method('getIterator')->willReturn(new \ArrayIterator([$group]));
        $groupCollection->method('addFieldToFilter')->willReturnSelf();
        $groupCollection->method('setOrder')->willReturnSelf();
        $groupCollection->method('load')->willReturnSelf();

        $website = $this->createMock(Website::class);
        $website->method('getId')->willReturn(1);
        $website->method('getName')->willReturn('Main Website');
        $website->method('getGroupCollection')->willReturn($groupCollection);

        $websiteCollection = $this->createMock(WebsiteCollection::class);
        $websiteCollection->method('getIterator')->willReturn(new \ArrayIterator([$website]));

        $websiteInstance = $this->createMock(Website::class);
        $websiteInstance->expects($this->once())
            ->method('getResourceCollection')
            ->willReturn($websiteCollection);
        $this->websiteFactory->expects($this->once())
            ->method('create')
            ->willReturn($websiteInstance);

        $websiteCollection->method('addIdFilter')->willReturnSelf();
        $websiteCollection->method('load')->willReturnSelf();

        $this->escaper->method('escapeHtml')->willReturnArgument(0);

        $storeTo1 = $this->createMock(Store::class);
        $storeTo1->method('getId')->willReturn(2);
        $html1 = $this->block->getChooseFromStoreHtml($storeTo1);
        $storeTo2 = $this->createMock(Store::class);
        $storeTo2->method('getId')->willReturn(3);
        $html2 = $this->block->getChooseFromStoreHtml($storeTo2);

        $this->assertStringContainsString('name="copy_to_stores[2]"', $html1);
        $this->assertStringContainsString('name="copy_to_stores[3]"', $html2);
        $this->assertStringContainsString('Main Website', $html1);
        $this->assertStringContainsString('Main Website', $html2);
    }
}
