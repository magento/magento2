<?php

/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs;
use Magento\Catalog\Helper\Catalog;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit test for Tabs class
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TabsTest extends TestCase
{
    /**
     * @var Tabs
     */
    private Tabs $tabs;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var EncoderInterface|MockObject
     */
    private $jsonEncoderMock;

    /**
     * @var Session|MockObject
     */
    private $authSessionMock;

    /**
     * @var Manager|MockObject
     */
    private $moduleManagerMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var Catalog|MockObject
     */
    private $helperCatalogMock;

    /**
     * @var Data|MockObject
     */
    private $catalogDataMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var InlineInterface|MockObject
     */
    private $translateInlineMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $jsonHelperMock = $this->createMock(JsonHelper::class);
        $directoryHelperMock = $this->createMock(DirectoryHelper::class);

        $objects = [
            [
                JsonHelper::class,
                $jsonHelperMock
            ],
            [
                DirectoryHelper::class,
                $directoryHelperMock
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->contextMock = $this->createMock(Context::class);
        $this->jsonEncoderMock = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->authSessionMock = $this->createMock(Session::class);
        $this->moduleManagerMock = $this->createMock(Manager::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->helperCatalogMock = $this->createMock(Catalog::class);
        $this->catalogDataMock = $this->createMock(Data::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->translateInlineMock = $this->getMockForAbstractClass(InlineInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->productMock = $this->createMock(Product::class);

        $this->contextMock->expects($this->any())
            ->method('getStoreManager')
            ->willReturn($this->storeManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->tabs = $objectManager->getObject(
            Tabs::class,
            [
                'context' => $this->contextMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'authSession' => $this->authSessionMock,
                'moduleManager' => $this->moduleManagerMock,
                'collectionFactory' => $this->collectionFactory,
                'helperCatalog' => $this->helperCatalogMock,
                'catalogData' => $this->catalogDataMock,
                'registry' => $this->registryMock,
                'translateInline' => $this->translateInlineMock,
                'data' => [
                    'jsonHelper' => $jsonHelperMock,
                    'directoryHelper' => $directoryHelperMock
                ]
            ]
        );
    }

    /**
     * Test getGroupCollection method
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getGroupCollection
     * @return void
     */
    public function testGetGroupCollection(): void
    {
        $attributeSetId = 5;
        $collectionMock = $this->createMock(Collection::class);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with($attributeSetId)
            ->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('setSortOrder')
            ->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $result = $this->tabs->getGroupCollection($attributeSetId);
        $this->assertSame($collectionMock, $result);
    }

    /**
     * Test getProduct returns product from data
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getProduct
     * @return void
     */
    public function testGetProductFromData(): void
    {
        $this->tabs->setData('product', $this->productMock);
        $result = $this->tabs->getProduct();
        $this->assertSame($this->productMock, $result);
    }

    /**
     * Test getProduct returns product from registry
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getProduct
     * @return void
     */
    public function testGetProductFromRegistry(): void
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->tabs->getProduct();
        $this->assertSame($this->productMock, $result);
    }

    /**
     * Test getAttributeTabBlock method with various scenarios
     *
     * @param string|null $helperReturn
     * @param string $expectedBlock
     * @dataProvider getAttributeTabBlockDataProvider
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getAttributeTabBlock
     * @return void
     */
    public function testGetAttributeTabBlock(?string $helperReturn, string $expectedBlock): void
    {
        $this->helperCatalogMock->expects($this->any())
            ->method('getAttributeTabBlock')
            ->willReturn($helperReturn);

        $result = $this->tabs->getAttributeTabBlock();
        $this->assertEquals($expectedBlock, $result);
    }

    /**
     * Data provider for testGetAttributeTabBlock
     *
     * @return array
     */
    public static function getAttributeTabBlockDataProvider(): array
    {
        return [
            'custom_block_from_helper' => [
                'helperReturn' => 'Custom\Block\Class',
                'expectedBlock' => 'Custom\Block\Class'
            ],
            'default_block_when_helper_returns_null' => [
                'helperReturn' => null,
                'expectedBlock' => Attributes::class
            ]
        ];
    }

    /**
     * Test setAttributeTabBlock method
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::setAttributeTabBlock
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getAttributeTabBlock
     * @return void
     */
    public function testSetAttributeTabBlock(): void
    {
        $customBlock = 'Custom\Block\Class';
        $this->helperCatalogMock->expects($this->any())
            ->method('getAttributeTabBlock')
            ->willReturn(null);

        $result = $this->tabs->setAttributeTabBlock($customBlock);

        $this->assertSame($this->tabs, $result);
        $this->assertEquals($customBlock, $this->tabs->getAttributeTabBlock());
    }

    /**
     * Test isAdvancedTabGroupActive returns true for advanced tab
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::isAdvancedTabGroupActive
     * @return void
     */
    public function testIsAdvancedTabGroupActive(): void
    {
        $reflection = new ReflectionClass($this->tabs);

        $tabDataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGroupCode'])
            ->getMock();
        $tabDataObjectMock->expects($this->once())
            ->method('getGroupCode')
            ->willReturn(Tabs::ADVANCED_TAB_GROUP_CODE);

        $tabsProperty = $reflection->getProperty('_tabs');
        $tabsProperty->setAccessible(true);
        $tabsProperty->setValue($this->tabs, ['advanced-pricing' => $tabDataObjectMock]);

        $activeTabProperty = $reflection->getProperty('_activeTab');
        $activeTabProperty->setAccessible(true);
        $activeTabProperty->setValue($this->tabs, 'advanced-pricing');

        $result = $this->tabs->isAdvancedTabGroupActive();
        $this->assertTrue($result);
    }

    /**
     * Test isAdvancedTabGroupActive returns false for non-advanced tab
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::isAdvancedTabGroupActive
     * @return void
     */
    public function testIsAdvancedTabGroupActiveFalse(): void
    {
        $reflection = new ReflectionClass($this->tabs);

        $tabDataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGroupCode'])
            ->getMock();
        $tabDataObjectMock->expects($this->once())
            ->method('getGroupCode')
            ->willReturn(Tabs::BASIC_TAB_GROUP_CODE);

        $tabsProperty = $reflection->getProperty('_tabs');
        $tabsProperty->setAccessible(true);
        $tabsProperty->setValue($this->tabs, ['basic-tab' => $tabDataObjectMock]);

        $activeTabProperty = $reflection->getProperty('_activeTab');
        $activeTabProperty->setAccessible(true);
        $activeTabProperty->setValue($this->tabs, 'basic-tab');

        $result = $this->tabs->isAdvancedTabGroupActive();
        $this->assertFalse($result);
    }

    /**
     * Test getAccordion method with matching parent tab
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getAccordion
     * @return void
     */
    public function testGetAccordionWithMatchingParentTab(): void
    {
        $parentTabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMock();
        $parentTabMock->expects($this->any())
            ->method('getId')
            ->willReturn('parent-tab');

        $childTabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getParentTab'])
            ->getMock();
        $childTabMock->expects($this->any())
            ->method('getParentTab')
            ->willReturn('parent-tab');

        $childBlockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->addMethods(['setTab'])
            ->onlyMethods(['toHtml'])
            ->getMock();
        $childBlockMock->expects($this->once())
            ->method('setTab')
            ->with($childTabMock)
            ->willReturnSelf();
        $childBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn('<div>child tab html</div>');

        $reflection = new ReflectionClass($this->tabs);
        $tabsProperty = $reflection->getProperty('_tabs');
        $tabsProperty->setAccessible(true);
        $tabsProperty->setValue($this->tabs, [$childTabMock]);

        $tabsMock = $this->getMockBuilder(Tabs::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->jsonEncoderMock,
                $this->authSessionMock,
                $this->moduleManagerMock,
                $this->collectionFactory,
                $this->helperCatalogMock,
                $this->catalogDataMock,
                $this->registryMock,
                $this->translateInlineMock,
                ['jsonHelper' => $this->createMock(JsonHelper::class),
                'directoryHelper' => $this->createMock(DirectoryHelper::class)]
            ])
            ->onlyMethods(['getChildBlock'])
            ->getMock();

        $tabsMock->expects($this->once())
            ->method('getChildBlock')
            ->with('child-tab')
            ->willReturn($childBlockMock);

        $tabsProperty->setValue($tabsMock, [$childTabMock]);

        $result = $tabsMock->getAccordion($parentTabMock);
        $this->assertEquals('<div>child tab html</div>', $result);
    }

    /**
     * Test getAccordion method with no matching child tabs
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getAccordion
     * @return void
     */
    public function testGetAccordionWithNoMatchingChildTabs(): void
    {
        $parentTabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMock();
        $parentTabMock->expects($this->any())
            ->method('getId')
            ->willReturn('parent-tab');

        $childTabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getParentTab'])
            ->getMock();
        $childTabMock->expects($this->any())
            ->method('getParentTab')
            ->willReturn('different-parent-tab');

        $reflection = new ReflectionClass($this->tabs);
        $tabsProperty = $reflection->getProperty('_tabs');
        $tabsProperty->setAccessible(true);

        $tabsMock = $this->getMockBuilder(Tabs::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->jsonEncoderMock,
                $this->authSessionMock,
                $this->moduleManagerMock,
                $this->collectionFactory,
                $this->helperCatalogMock,
                $this->catalogDataMock,
                $this->registryMock,
                $this->translateInlineMock,
                ['jsonHelper' => $this->createMock(JsonHelper::class),
                'directoryHelper' => $this->createMock(DirectoryHelper::class)]
            ])
            ->onlyMethods(['getChildBlock'])
            ->getMock();

        $tabsMock->expects($this->never())
            ->method('getChildBlock');

        $tabsProperty->setValue($tabsMock, [$childTabMock]);

        $result = $tabsMock->getAccordion($parentTabMock);
        $this->assertEquals('', $result);
    }

    /**
     * Test getAccordion method with multiple child tabs
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getAccordion
     * @return void
     */
    public function testGetAccordionWithMultipleChildTabs(): void
    {
        $parentTabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMock();
        $parentTabMock->expects($this->any())
            ->method('getId')
            ->willReturn('parent-tab');

        $childTab1Mock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getParentTab'])
            ->getMock();
        $childTab1Mock->expects($this->any())
            ->method('getParentTab')
            ->willReturn('parent-tab');

        $childTab2Mock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getParentTab'])
            ->getMock();
        $childTab2Mock->expects($this->any())
            ->method('getParentTab')
            ->willReturn('parent-tab');

        $childTab3Mock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getParentTab'])
            ->getMock();
        $childTab3Mock->expects($this->any())
            ->method('getParentTab')
            ->willReturn('different-parent');

        $childBlockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->addMethods(['setTab'])
            ->onlyMethods(['toHtml'])
            ->getMock();
        $childBlockMock->expects($this->exactly(2))
            ->method('setTab')
            ->willReturnSelf();
        $childBlockMock->expects($this->exactly(2))
            ->method('toHtml')
            ->willReturnOnConsecutiveCalls('<div>child1 html</div>', '<div>child2 html</div>');

        $reflection = new ReflectionClass($this->tabs);
        $tabsProperty = $reflection->getProperty('_tabs');
        $tabsProperty->setAccessible(true);

        $tabsMock = $this->getMockBuilder(Tabs::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->jsonEncoderMock,
                $this->authSessionMock,
                $this->moduleManagerMock,
                $this->collectionFactory,
                $this->helperCatalogMock,
                $this->catalogDataMock,
                $this->registryMock,
                $this->translateInlineMock,
                ['jsonHelper' => $this->createMock(JsonHelper::class),
                'directoryHelper' => $this->createMock(DirectoryHelper::class)]
            ])
            ->onlyMethods(['getChildBlock'])
            ->getMock();

        $tabsMock->expects($this->exactly(2))
            ->method('getChildBlock')
            ->with('child-tab')
            ->willReturn($childBlockMock);

        $tabsProperty->setValue($tabsMock, [$childTab1Mock, $childTab2Mock, $childTab3Mock]);

        $result = $tabsMock->getAccordion($parentTabMock);
        $this->assertEquals('<div>child1 html</div><div>child2 html</div>', $result);
    }

    /**
     * Test getAccordion method with empty tabs array
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getAccordion
     * @return void
     */
    public function testGetAccordionWithEmptyTabs(): void
    {
        $parentTabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMock();
        $parentTabMock->expects($this->any())
            ->method('getId')
            ->willReturn('parent-tab');

        $reflection = new ReflectionClass($this->tabs);
        $tabsProperty = $reflection->getProperty('_tabs');
        $tabsProperty->setAccessible(true);

        $tabsMock = $this->getMockBuilder(Tabs::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->jsonEncoderMock,
                $this->authSessionMock,
                $this->moduleManagerMock,
                $this->collectionFactory,
                $this->helperCatalogMock,
                $this->catalogDataMock,
                $this->registryMock,
                $this->translateInlineMock,
                ['jsonHelper' => $this->createMock(JsonHelper::class),
                'directoryHelper' => $this->createMock(DirectoryHelper::class)]
            ])
            ->onlyMethods(['getChildBlock'])
            ->getMock();

        $tabsMock->expects($this->never())
            ->method('getChildBlock');

        $tabsProperty->setValue($tabsMock, []);

        $result = $tabsMock->getAccordion($parentTabMock);
        $this->assertEquals('', $result);
    }

    /**
     * Test _construct method
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::_construct
     * @return void
     */
    public function testConstruct(): void
    {
        $this->assertEquals('product_info_tabs', $this->tabs->getId());
        $this->assertEquals('product-edit-form-tabs', $this->tabs->getDestElementId());
    }

    /**
     * Test _translateHtml method
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::_translateHtml
     * @return void
     */
    public function testTranslateHtml(): void
    {
        $html = '<div>Test HTML</div>';
        $expectedHtml = '<div>Test HTML</div>';

        $this->translateInlineMock->expects($this->once())
            ->method('processResponseBody')
            ->with($this->identicalTo($html));

        $reflection = new ReflectionClass($this->tabs);
        $method = $reflection->getMethod('_translateHtml');
        $method->setAccessible(true);

        $result = $method->invoke($this->tabs, $html);
        $this->assertEquals($expectedHtml, $result);
    }

    /**
     * Test _prepareLayout with no attribute set ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::_prepareLayout
     * @return void
     */
    public function testPrepareLayoutWithNoAttributeSetId(): void
    {
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->productMock->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn(null);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('set', null)
            ->willReturn(null);

        $tabsMock = $this->getMockBuilder(Tabs::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->jsonEncoderMock,
                $this->authSessionMock,
                $this->moduleManagerMock,
                $this->collectionFactory,
                $this->helperCatalogMock,
                $this->catalogDataMock,
                $this->registryMock,
                $this->translateInlineMock,
                ['jsonHelper' => $this->createMock(JsonHelper::class),
                'directoryHelper' => $this->createMock(DirectoryHelper::class)]
            ])
            ->onlyMethods(['getProduct', 'getLayout'])
            ->getMock();

        $tabsMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $tabsMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $reflection = new ReflectionClass($tabsMock);
        $method = $reflection->getMethod('_prepareLayout');
        $method->setAccessible(true);

        $result = $method->invoke($tabsMock);
        $this->assertSame($tabsMock, $result);
    }

    /**
     * Test _prepareLayout in single store mode
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::_prepareLayout
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareLayoutInSingleStoreMode(): void
    {
        $attributeSetId = 5;
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->productMock->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn($attributeSetId);

        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn(true);

        // Mock attribute group
        $groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttributeGroupCode', 'getTabGroupCode'])
            ->onlyMethods(['getId', 'getAttributeGroupName'])
            ->getMock();
        $groupMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $groupMock->expects($this->any())
            ->method('getAttributeGroupName')
            ->willReturn('General');
        $groupMock->expects($this->any())
            ->method('getAttributeGroupCode')
            ->willReturn('general');
        $groupMock->expects($this->any())
            ->method('getTabGroupCode')
            ->willReturn('basic');

        // Mock attribute
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIsVisible', 'getApplyTo'])
            ->getMockForAbstractClass();
        $attributeMock->expects($this->any())
            ->method('getIsVisible')
            ->willReturn(true);
        $attributeMock->expects($this->any())
            ->method('getApplyTo')
            ->willReturn([]);

        $this->productMock->expects($this->once())
            ->method('getAttributes')
            ->with(1, true)
            ->willReturn(['attr1' => $attributeMock]);

        // Mock collection
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with($attributeSetId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setSortOrder')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$groupMock]));

        // Mock attribute tab block
        $attributeTabBlockMock = $this->getMockBuilder(Attributes::class)
            ->disableOriginalConstructor()
            ->addMethods(['setGroup', 'setGroupAttributes'])
            ->onlyMethods(['toHtml'])
            ->getMock();
        $attributeTabBlockMock->expects($this->once())
            ->method('setGroup')
            ->with($groupMock)
            ->willReturnSelf();
        $attributeTabBlockMock->expects($this->once())
            ->method('setGroupAttributes')
            ->willReturnSelf();
        $attributeTabBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn('<div>attributes</div>');

        $this->helperCatalogMock->expects($this->any())
            ->method('getAttributeTabBlock')
            ->willReturn(null);

        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->willReturn($attributeTabBlockMock);

        $this->translateInlineMock->expects($this->atLeastOnce())
            ->method('processResponseBody');

        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(false);

        $tabsMock = $this->getMockBuilder(Tabs::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->jsonEncoderMock,
                $this->authSessionMock,
                $this->moduleManagerMock,
                $this->collectionFactory,
                $this->helperCatalogMock,
                $this->catalogDataMock,
                $this->registryMock,
                $this->translateInlineMock,
                ['jsonHelper' => $this->createMock(JsonHelper::class),
                'directoryHelper' => $this->createMock(DirectoryHelper::class)]
            ])
            ->onlyMethods([
                'getProduct',
                'getLayout',
                'getNameInLayout',
                'getAttributeTabBlock',
                'addTab',
                'getChildBlock',
                'getUrl'
            ])
            ->getMock();

        $tabsMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $tabsMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $tabsMock->expects($this->any())
            ->method('getNameInLayout')
            ->willReturn('product_tabs');

        $tabsMock->expects($this->any())
            ->method('getAttributeTabBlock')
            ->willReturn(Attributes::class);

        $tabsMock->expects($this->any())
            ->method('getChildBlock')
            ->willReturn(null);

        $tabsMock->expects($this->atLeastOnce())
            ->method('addTab');

        $tabsMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://example.com');

        $reflection = new ReflectionClass($tabsMock);
        $method = $reflection->getMethod('_prepareLayout');
        $method->setAccessible(true);

        $result = $method->invoke($tabsMock);
        $this->assertSame($tabsMock, $result);
    }

    /**
     * Test _prepareLayout in multi-store mode with advanced tabs
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::_prepareLayout
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareLayoutInMultiStoreMode(): void
    {
        $attributeSetId = 5;
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->productMock->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn($attributeSetId);

        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn(false);

        // Mock basic group
        $groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttributeGroupCode', 'getTabGroupCode'])
            ->onlyMethods(['getId', 'getAttributeGroupName'])
            ->getMock();
        $groupMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $groupMock->expects($this->any())
            ->method('getAttributeGroupName')
            ->willReturn('General');
        $groupMock->expects($this->any())
            ->method('getAttributeGroupCode')
            ->willReturn('general');
        $groupMock->expects($this->any())
            ->method('getTabGroupCode')
            ->willReturn('basic');

        // Mock advanced pricing group
        $advancedPricingGroupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttributeGroupCode', 'getTabGroupCode'])
            ->onlyMethods(['getId', 'getAttributeGroupName'])
            ->getMock();
        $advancedPricingGroupMock->expects($this->any())
            ->method('getId')
            ->willReturn(2);
        $advancedPricingGroupMock->expects($this->any())
            ->method('getAttributeGroupName')
            ->willReturn('Advanced Pricing');
        $advancedPricingGroupMock->expects($this->any())
            ->method('getAttributeGroupCode')
            ->willReturn('advanced-pricing');
        $advancedPricingGroupMock->expects($this->any())
            ->method('getTabGroupCode')
            ->willReturn('advanced');

        // Mock design group
        $designGroupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttributeGroupCode', 'getTabGroupCode'])
            ->onlyMethods(['getId', 'getAttributeGroupName'])
            ->getMock();
        $designGroupMock->expects($this->any())
            ->method('getId')
            ->willReturn(3);
        $designGroupMock->expects($this->any())
            ->method('getAttributeGroupName')
            ->willReturn('Design');
        $designGroupMock->expects($this->any())
            ->method('getAttributeGroupCode')
            ->willReturn('design');
        $designGroupMock->expects($this->any())
            ->method('getTabGroupCode')
            ->willReturn('advanced');

        // Mock attribute
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIsVisible', 'getApplyTo'])
            ->getMockForAbstractClass();
        $attributeMock->expects($this->any())
            ->method('getIsVisible')
            ->willReturn(true);
        $attributeMock->expects($this->any())
            ->method('getApplyTo')
            ->willReturn([]);

        $this->productMock->expects($this->any())
            ->method('getAttributes')
            ->willReturnCallback(function ($groupId) use ($attributeMock) {
                return ['attr' . $groupId => $attributeMock];
            });

        // Mock collection
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with($attributeSetId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setSortOrder')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$groupMock, $advancedPricingGroupMock, $designGroupMock]));

        // Mock attribute tab block
        $attributeTabBlockMock = $this->getMockBuilder(Attributes::class)
            ->disableOriginalConstructor()
            ->addMethods(['setGroup', 'setGroupAttributes'])
            ->onlyMethods(['toHtml'])
            ->getMock();
        $attributeTabBlockMock->expects($this->any())
            ->method('setGroup')
            ->willReturnSelf();
        $attributeTabBlockMock->expects($this->any())
            ->method('setGroupAttributes')
            ->willReturnSelf();
        $attributeTabBlockMock->expects($this->any())
            ->method('toHtml')
            ->willReturn('<div>attributes</div>');

        // Mock websites block
        $websitesBlockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toHtml'])
            ->getMock();
        $websitesBlockMock->expects($this->any())
            ->method('toHtml')
            ->willReturn('<div>websites</div>');

        $this->helperCatalogMock->expects($this->any())
            ->method('getAttributeTabBlock')
            ->willReturn(null);

        $layoutMock->expects($this->any())
            ->method('createBlock')
            ->willReturnOnConsecutiveCalls(
                $attributeTabBlockMock,
                $attributeTabBlockMock,
                $attributeTabBlockMock,
                $websitesBlockMock
            );

        $this->translateInlineMock->expects($this->atLeastOnce())
            ->method('processResponseBody');

        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(false);

        $tabsMock = $this->getMockBuilder(Tabs::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->jsonEncoderMock,
                $this->authSessionMock,
                $this->moduleManagerMock,
                $this->collectionFactory,
                $this->helperCatalogMock,
                $this->catalogDataMock,
                $this->registryMock,
                $this->translateInlineMock,
                ['jsonHelper' => $this->createMock(JsonHelper::class),
                'directoryHelper' => $this->createMock(DirectoryHelper::class)]
            ])
            ->onlyMethods([
                'getProduct',
                'getLayout',
                'getNameInLayout',
                'getAttributeTabBlock',
                'addTab',
                'getChildBlock',
                'getUrl'
            ])
            ->getMock();

        $tabsMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $tabsMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $tabsMock->expects($this->any())
            ->method('getNameInLayout')
            ->willReturn('product_tabs');

        $tabsMock->expects($this->any())
            ->method('getAttributeTabBlock')
            ->willReturn(Attributes::class);

        $tabsMock->expects($this->any())
            ->method('getChildBlock')
            ->willReturn(null);

        $tabsMock->expects($this->atLeastOnce())
            ->method('addTab');

        $tabsMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://example.com');

        $reflection = new ReflectionClass($tabsMock);
        $method = $reflection->getMethod('_prepareLayout');
        $method->setAccessible(true);

        $result = $method->invoke($tabsMock);
        $this->assertSame($tabsMock, $result);
    }

    /**
     * Test getProduct with null product in registry
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getProduct
     * @return void
     */
    public function testGetProductWithNullProductInRegistry(): void
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn(null);

        $result = $this->tabs->getProduct();
        $this->assertNull($result);
    }

    /**
     * Test getProduct with non-product object
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getProduct
     * @return void
     */
    public function testGetProductWithNonProductObject(): void
    {
        $this->tabs->setData('product', new \stdClass());
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->tabs->getProduct();
        $this->assertSame($this->productMock, $result);
    }

    /**
     * Test isAdvancedTabGroupActive with empty tabs array
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::isAdvancedTabGroupActive
     * @return void
     */
    public function testIsAdvancedTabGroupActiveWithEmptyTabsArray(): void
    {
        // Add a valid tab first to avoid undefined array key warning
        $tabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGroupCode'])
            ->getMock();
        $tabMock->expects($this->once())
            ->method('getGroupCode')
            ->willReturn('some-other-group');

        $reflection = new ReflectionClass($this->tabs);
        $activeTabProperty = $reflection->getProperty('_activeTab');
        $activeTabProperty->setAccessible(true);
        $activeTabProperty->setValue($this->tabs, 'test-tab');

        $tabsProperty = $reflection->getProperty('_tabs');
        $tabsProperty->setAccessible(true);
        $tabsProperty->setValue($this->tabs, ['test-tab' => $tabMock]);

        $result = $this->tabs->isAdvancedTabGroupActive();
        $this->assertFalse($result);
    }

    /**
     * Test getAccordion with null parent tab ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getAccordion
     * @return void
     */
    public function testGetAccordionWithNullParentTab(): void
    {
        $parentTabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMock();
        $parentTabMock->expects($this->any())
            ->method('getId')
            ->willReturn(null);

        $reflection = new ReflectionClass($this->tabs);
        $tabsProperty = $reflection->getProperty('_tabs');
        $tabsProperty->setAccessible(true);
        $tabsProperty->setValue($this->tabs, []);

        $result = $this->tabs->getAccordion($parentTabMock);
        $this->assertEmpty($result);
    }

    /**
     * Test setAttributeTabBlock with null value
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::setAttributeTabBlock
     * @return void
     */
    public function testSetAttributeTabBlockWithNull(): void
    {
        $result = $this->tabs->setAttributeTabBlock(null);
        $this->assertSame($this->tabs, $result);
        $this->assertNull($this->tabs->getAttributeTabBlock());
    }

    /**
     * Test setAttributeTabBlock with empty string
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::setAttributeTabBlock
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getAttributeTabBlock
     * @return void
     */
    public function testSetAttributeTabBlockWithEmptyString(): void
    {
        $this->helperCatalogMock->expects($this->any())
            ->method('getAttributeTabBlock')
            ->willReturn(null);

        $result = $this->tabs->setAttributeTabBlock('');
        $this->assertSame($this->tabs, $result);
        $this->assertSame('', $this->tabs->getAttributeTabBlock());
    }

    /**
     * Test getGroupCollection with zero attribute set ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getGroupCollection
     * @return void
     */
    public function testGetGroupCollectionWithZeroAttributeSetId(): void
    {
        $collectionMock = $this->createMock(Collection::class);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with(0)
            ->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('setSortOrder')
            ->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $result = $this->tabs->getGroupCollection(0);
        $this->assertSame($collectionMock, $result);
    }

    /**
     * Test getGroupCollection with negative attribute set ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getGroupCollection
     * @return void
     */
    public function testGetGroupCollectionWithNegativeAttributeSetId(): void
    {
        $collectionMock = $this->createMock(Collection::class);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with(-1)
            ->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('setSortOrder')
            ->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $result = $this->tabs->getGroupCollection(-1);
        $this->assertSame($collectionMock, $result);
    }

    /**
     * Test _translateHtml with null HTML
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::_translateHtml
     * @return void
     */
    public function testTranslateHtmlWithNull(): void
    {
        $this->translateInlineMock->expects($this->once())
            ->method('processResponseBody')
            ->with($this->identicalTo(null));

        $reflection = new ReflectionClass($this->tabs);
        $method = $reflection->getMethod('_translateHtml');
        $method->setAccessible(true);

        $result = $method->invoke($this->tabs, null);
        $this->assertNull($result);
    }

    /**
     * Test _translateHtml with empty string
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::_translateHtml
     * @return void
     */
    public function testTranslateHtmlWithEmptyString(): void
    {
        $this->translateInlineMock->expects($this->once())
            ->method('processResponseBody')
            ->with($this->identicalTo(''));

        $reflection = new ReflectionClass($this->tabs);
        $method = $reflection->getMethod('_translateHtml');
        $method->setAccessible(true);

        $result = $method->invoke($this->tabs, '');
        $this->assertEquals('', $result);
    }

    /**
     * Test _translateHtml with special characters
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::_translateHtml
     * @return void
     */
    public function testTranslateHtmlWithSpecialCharacters(): void
    {
        $scriptOpen = '<' . 'script>';
        $scriptClose = '</' . 'script>';
        $html = '<div>Test & "quotes" ' . $scriptOpen . 'alert("xss")' . $scriptClose . '</div>';

        $this->translateInlineMock->expects($this->once())
            ->method('processResponseBody')
            ->with($this->identicalTo($html));

        $reflection = new ReflectionClass($this->tabs);
        $method = $reflection->getMethod('_translateHtml');
        $method->setAccessible(true);

        $result = $method->invoke($this->tabs, $html);
        $this->assertEquals($html, $result);
    }

    /**
     * Test getAccordion when child-tab block returns null
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getAccordion
     * @return void
     */
    public function testGetAccordionWhenChildTabBlockReturnsNull(): void
    {
        $parentTabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMock();
        $parentTabMock->expects($this->any())
            ->method('getId')
            ->willReturn('parent-tab');

        $childTabMock = $this->getMockBuilder(Tab::class)
            ->disableOriginalConstructor()
            ->addMethods(['getParentTab'])
            ->getMock();
        $childTabMock->expects($this->any())
            ->method('getParentTab')
            ->willReturn('parent-tab');

        $reflection = new ReflectionClass($this->tabs);
        $tabsProperty = $reflection->getProperty('_tabs');
        $tabsProperty->setAccessible(true);

        $tabsMock = $this->getMockBuilder(Tabs::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->jsonEncoderMock,
                $this->authSessionMock,
                $this->moduleManagerMock,
                $this->collectionFactory,
                $this->helperCatalogMock,
                $this->catalogDataMock,
                $this->registryMock,
                $this->translateInlineMock,
                ['jsonHelper' => $this->createMock(JsonHelper::class),
                'directoryHelper' => $this->createMock(DirectoryHelper::class)]
            ])
            ->onlyMethods(['getChildBlock'])
            ->getMock();

        $tabsMock->expects($this->once())
            ->method('getChildBlock')
            ->with('child-tab')
            ->willReturn(null);

        $tabsProperty->setValue($tabsMock, [$childTabMock]);

        $this->expectException(\Error::class);
        $tabsMock->getAccordion($parentTabMock);
    }

    /**
     * Test getProduct with string instead of product
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getProduct
     * @return void
     */
    public function testGetProductWithString(): void
    {
        $this->tabs->setData('product', 'not a product');
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->tabs->getProduct();
        $this->assertSame($this->productMock, $result);
    }

    /**
     * Test getProduct with array instead of product
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs::getProduct
     * @return void
     */
    public function testGetProductWithArray(): void
    {
        $this->tabs->setData('product', ['id' => 1, 'name' => 'Test']);
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $result = $this->tabs->getProduct();
        $this->assertSame($this->productMock, $result);
    }
}
