<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab\Attributes;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Create;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Create block
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Create
 */
class CreateTest extends TestCase
{
    /**
     * @var Create
     */
    private Create $block;

    /**
     * @var MockObject&UrlInterface
     */
    private MockObject $urlBuilderMock;

    /**
     * @var MockObject&EventManagerInterface
     */
    private MockObject $eventManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        // Prepare ObjectManager for helpers used by parent blocks
        $objects = [
            [JsonHelper::class, $this->createMock(JsonHelper::class)],
            [DirectoryHelper::class, $this->createMock(DirectoryHelper::class)]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->eventManagerMock = $this->createMock(EventManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $contextMock->method('getEventManager')->willReturn($this->eventManagerMock);

        $this->block = $objectManager->getObject(
            Create::class,
            [
                'context' => $contextMock
            ]
        );
    }

    /**
     * Test getConfig returns DataObject instance
     *
     * @return void
     */
    public function testGetConfigReturnsDataObjectInstance(): void
    {
        $result = $this->block->getConfig();

        $this->assertInstanceOf(DataObject::class, $result);
    }

    /**
     * Test getConfig returns same instance on subsequent calls
     *
     * @return void
     */
    public function testGetConfigReturnsSameInstanceOnSubsequentCalls(): void
    {
        $firstResult = $this->block->getConfig();
        $secondResult = $this->block->getConfig();

        $this->assertSame($firstResult, $secondResult);
    }

    /**
     * Test config property is null before getConfig call
     *
     * @return void
     */
    public function testConfigPropertyIsNullBeforeGetConfigCall(): void
    {
        $reflection = new \ReflectionClass($this->block);
        $property = $reflection->getProperty('_config');

        $this->assertNull($property->getValue($this->block));
    }

    /**
     * Test getConfig initializes config property with DataObject
     *
     * @return void
     */
    public function testGetConfigInitializesConfigPropertyWithDataObject(): void
    {
        $this->block->getConfig();

        $reflection = new \ReflectionClass($this->block);
        $property = $reflection->getProperty('_config');

        $this->assertInstanceOf(DataObject::class, $property->getValue($this->block));
    }

    /**
     * Data provider for getJsObjectName scenarios
     *
     * @return array
     */
    public static function jsObjectNameDataProvider(): array
    {
        return [
            'with simple id' => [
                'id' => 'test_button',
                'expectedResult' => 'test_buttonJsObject'
            ],
            'with numeric id' => [
                'id' => 'button_123',
                'expectedResult' => 'button_123JsObject'
            ],
            'with group id' => [
                'id' => 'create_attribute_5',
                'expectedResult' => 'create_attribute_5JsObject'
            ]
        ];
    }

    /**
     * Test getJsObjectName returns correct format
     *
     * @param string $id
     * @param string $expectedResult
     * @return void
     */
    #[DataProvider('jsObjectNameDataProvider')]
    public function testGetJsObjectNameReturnsCorrectFormat(string $id, string $expectedResult): void
    {
        $this->block->setId($id);

        $result = $this->block->getJsObjectName();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Test _beforeToHtml sets button id with group id from config
     *
     * @return void
     */
    public function testBeforeToHtmlSetsButtonIdWithGroupId(): void
    {
        $groupId = '10';
        $this->block->getConfig()->setGroupId($groupId);

        $this->urlBuilderMock->method('getUrl')
            ->willReturn('http://example.com/catalog/product_attribute/new');

        // Call protected method
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_beforeToHtml');
        $method->invoke($this->block);

        $this->assertSame('create_attribute_' . $groupId, $this->block->getId());
    }

    /**
     * Test _beforeToHtml sets button type
     *
     * @return void
     */
    public function testBeforeToHtmlSetsButtonType(): void
    {
        $this->urlBuilderMock->method('getUrl')
            ->willReturn('http://example.com/url');

        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_beforeToHtml');
        $method->invoke($this->block);

        $this->assertSame('button', $this->block->getType());
    }

    /**
     * Test _beforeToHtml sets button class
     *
     * @return void
     */
    public function testBeforeToHtmlSetsButtonClass(): void
    {
        $this->urlBuilderMock->method('getUrl')
            ->willReturn('http://example.com/url');

        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_beforeToHtml');
        $method->invoke($this->block);

        $this->assertSame('action-add', $this->block->getClass());
    }

    /**
     * Test _beforeToHtml sets correct URL in config
     *
     * @return void
     */
    public function testBeforeToHtmlSetsCorrectUrlInConfig(): void
    {
        $expectedUrl = 'http://example.com/catalog/product_attribute/new'
            . '?group=general&store=1&product=5&type=simple&popup=1';
        $groupCode = 'general';
        $storeId = '1';
        $productId = '5';
        $typeId = 'simple';

        $this->block->getConfig()
            ->setAttributeGroupCode($groupCode)
            ->setStoreId($storeId)
            ->setProductId($productId)
            ->setTypeId($typeId);

        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with(
                'catalog/product_attribute/new',
                [
                    'group' => $groupCode,
                    'store' => $storeId,
                    'product' => $productId,
                    'type' => $typeId,
                    'popup' => 1
                ]
            )
            ->willReturn($expectedUrl);

        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_beforeToHtml');
        $method->invoke($this->block);

        $this->assertSame($expectedUrl, $this->block->getConfig()->getUrl());
    }

    /**
     * Test _toHtml dispatches event before rendering
     *
     * @return void
     */
    public function testToHtmlDispatchesEventBeforeRendering(): void
    {
        $eventDispatched = false;
        $this->eventManagerMock->method('dispatch')
            ->willReturnCallback(function ($eventName, $data) use (&$eventDispatched) {
                if ($eventName === 'adminhtml_catalog_product_edit_tab_attributes_create_html_before') {
                    $eventDispatched = true;
                    // Prevent parent _toHtml from executing
                    $data['block']->setCanShow(false);
                }
            });

        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_toHtml');
        $method->invoke($this->block);

        $this->assertTrue($eventDispatched);
    }

    /**
     * Test _toHtml returns empty string when canShow is false
     *
     * @return void
     */
    public function testToHtmlReturnsEmptyStringWhenCanShowIsFalse(): void
    {
        $this->eventManagerMock->method('dispatch')
            ->willReturnCallback(function ($eventName, $data) {
                if ($eventName === 'adminhtml_catalog_product_edit_tab_attributes_create_html_before') {
                    // Simulate event observer setting canShow to false
                    $data['block']->setCanShow(false);
                }
            });

        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_toHtml');
        $result = $method->invoke($this->block);

        $this->assertSame('', $result);
    }

    /**
     * Test _toHtml sets canShow to true initially
     *
     * @return void
     */
    public function testToHtmlSetsCanShowToTrueInitially(): void
    {
        $canShowValue = null;
        $this->eventManagerMock->method('dispatch')
            ->willReturnCallback(function ($eventName, $data) use (&$canShowValue) {
                if ($eventName === 'adminhtml_catalog_product_edit_tab_attributes_create_html_before') {
                    // Capture canShow value when event is dispatched
                    $canShowValue = $data['block']->getCanShow();
                    // Prevent parent _toHtml from executing
                    $data['block']->setCanShow(false);
                }
            });

        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_toHtml');
        $method->invoke($this->block);

        $this->assertTrue($canShowValue);
    }

    /**
     * Test _beforeToHtml returns block instance for method chaining
     *
     * @return void
     */
    public function testBeforeToHtmlReturnsBlockInstance(): void
    {
        $this->urlBuilderMock->method('getUrl')
            ->willReturn('http://example.com/url');

        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('_beforeToHtml');
        $result = $method->invoke($this->block);

        $this->assertSame($this->block, $result);
    }

    /**
     * Test _toHtml calls parent toHtml when canShow is true
     *
     * @return void
     */
    public function testToHtmlCallsParentWhenCanShowIsTrue(): void
    {
        $expectedHtml = '<button>Test Button</button>';

        $objectManager = new ObjectManager($this);

        // Prepare ObjectManager for helpers used by parent blocks
        $objects = [
            [JsonHelper::class, $this->createMock(JsonHelper::class)],
            [DirectoryHelper::class, $this->createMock(DirectoryHelper::class)]
        ];
        $objectManager->prepareObjectManager($objects);

        $eventManagerMock = $this->createMock(EventManagerInterface::class);
        $eventManagerMock->method('dispatch');

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $contextMock->method('getEventManager')->willReturn($eventManagerMock);

        // Create partial mock to control parent::_toHtml() behavior
        $blockMock = $this->getMockBuilder(Create::class)
            ->setConstructorArgs(['context' => $contextMock])
            ->onlyMethods(['fetchView', 'getTemplateFile'])
            ->getMock();

        $blockMock->method('getTemplateFile')
            ->willReturn('test_template.phtml');
        $blockMock->method('fetchView')
            ->willReturn($expectedHtml);

        $reflection = new \ReflectionClass($blockMock);
        $method = $reflection->getMethod('_toHtml');
        $result = $method->invoke($blockMock);

        $this->assertSame($expectedHtml, $result);
    }
}
