<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Backend\Block\Widget\ContainerInterface;
use Magento\Catalog\Block\Adminhtml\Product\Edit;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit test for Product Edit block
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    private Edit $block;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var Context|MockObject
     */
    private MockObject $contextMock;

    /**
     * @var EncoderInterface|MockObject
     */
    private MockObject $jsonEncoderMock;

    /**
     * @var SetFactory|MockObject
     */
    private MockObject $attributeSetFactoryMock;

    /**
     * @var Registry|MockObject
     */
    private MockObject $registryMock;

    /**
     * @var ProductHelper|MockObject
     */
    private MockObject $productHelperMock;

    /**
     * @var Escaper|MockObject
     */
    private MockObject $escaperMock;

    /**
     * @var Product|MockObject
     */
    private MockObject $productMock;

    /**
     * @var RequestInterface|MockObject
     */
    private MockObject $requestMock;

    /**
     * @var UrlInterface|MockObject
     */
    private MockObject $urlBuilderMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private MockObject $layoutMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Prepare ObjectManager for JsonHelper and DirectoryHelper that are internally retrieved
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->contextMock = $this->createMock(Context::class);
        $this->jsonEncoderMock = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->attributeSetFactoryMock = $this->createMock(SetFactory::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->productHelperMock = $this->createMock(ProductHelper::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->productMock = $this->createMock(Product::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);

        $this->contextMock->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        $this->contextMock->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->contextMock->method('getEscaper')
            ->willReturn($this->escaperMock);
        $this->registryMock->method('registry')
            ->with('current_product')
            ->willReturn($this->productMock);

        $this->block = $this->objectManager->getObject(
            Edit::class,
            [
                'context' => $this->contextMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'attributeSetFactory' => $this->attributeSetFactoryMock,
                'registry' => $this->registryMock,
                'productHelper' => $this->productHelperMock,
                'escaper' => $this->escaperMock
            ]
        );
    }

    /**
     * Invoke protected/private method on the block
     *
     * @param string $methodName
     * @return mixed
     */
    private function invokeProtectedMethod(string $methodName): mixed
    {
        $reflection = new ReflectionClass($this->block);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invoke($this->block);
    }

    /**
     * Test getProduct returns product from registry
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getProduct
     * @return void
     */
    public function testGetProductReturnsProductFromRegistry(): void
    {
        $result = $this->block->getProduct();
        $this->assertSame($this->productMock, $result);
    }

    /**
     * Test getValidationUrl returns correct URL
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getValidationUrl
     * @return void
     */
    public function testGetValidationUrlReturnsCorrectUrl(): void
    {
        $expectedUrl = 'http://example.com/catalog/product/validate';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('catalog/*/validate', ['_current' => true])
            ->willReturn($expectedUrl);

        $this->assertSame($expectedUrl, $this->block->getValidationUrl());
    }

    /**
     * Test getSaveUrl returns correct URL
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getSaveUrl
     * @return void
     */
    public function testGetSaveUrlReturnsCorrectUrl(): void
    {
        $expectedUrl = 'http://example.com/catalog/product/save';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('catalog/*/save', ['_current' => true, 'back' => null])
            ->willReturn($expectedUrl);

        $this->assertSame($expectedUrl, $this->block->getSaveUrl());
    }

    /**
     * Test getSaveAndContinueUrl returns correct URL
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getSaveAndContinueUrl
     * @return void
     */
    public function testGetSaveAndContinueUrlReturnsCorrectUrl(): void
    {
        $expectedUrl = 'http://example.com/catalog/product/save/back/edit';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                'catalog/*/save',
                ['_current' => true, 'back' => 'edit', 'tab' => '{{tab_id}}', 'active_tab' => null]
            )
            ->willReturn($expectedUrl);

        $this->assertSame($expectedUrl, $this->block->getSaveAndContinueUrl());
    }

    /**
     * Test getDuplicateUrl returns correct URL
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getDuplicateUrl
     * @return void
     */
    public function testGetDuplicateUrlReturnsCorrectUrl(): void
    {
        $expectedUrl = 'http://example.com/catalog/product/duplicate';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('catalog/*/duplicate', ['_current' => true])
            ->willReturn($expectedUrl);

        $this->assertSame($expectedUrl, $this->block->getDuplicateUrl());
    }

    /**
     * Test getProductId returns product ID
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getProductId
     * @return void
     */
    public function testGetProductIdReturnsProductId(): void
    {
        $expectedId = 123;
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($expectedId);

        $this->assertSame($expectedId, $this->block->getProductId());
    }

    /**
     * Data provider for testGetProductSetIdReturnsAttributeSetId
     *
     * @return array
     */
    public static function productSetIdDataProvider(): array
    {
        return [
            'product has attribute set id' => [
                'attributeSetId' => 4,
                'requestSetParam' => null,
                'expectedSetId' => 4
            ],
            'product has no attribute set id, use request param' => [
                'attributeSetId' => null,
                'requestSetParam' => 7,
                'expectedSetId' => 7
            ],
            'neither product nor request has set id' => [
                'attributeSetId' => null,
                'requestSetParam' => null,
                'expectedSetId' => null
            ]
        ];
    }

    /**
     * Test getProductSetId returns correct attribute set ID
     *
     * @dataProvider productSetIdDataProvider
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getProductSetId
     * @param int|null $attributeSetId
     * @param int|null $requestSetParam
     * @param int|null $expectedSetId
     * @return void
     */
    public function testGetProductSetIdReturnsAttributeSetId(
        ?int $attributeSetId,
        ?int $requestSetParam,
        ?int $expectedSetId
    ): void {
        $this->productMock->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn($attributeSetId);

        if ($attributeSetId === null) {
            $this->requestMock->expects($this->once())
                ->method('getParam')
                ->with('set', null)
                ->willReturn($requestSetParam);
        }

        $this->assertSame($expectedSetId, $this->block->getProductSetId());
    }

    /**
     * Data provider for testGetHeaderReturnsCorrectHeader
     *
     * @return array
     */
    public static function headerDataProvider(): array
    {
        return [
            'existing product returns escaped name' => [
                'productId' => 1,
                'productName' => 'Test Product',
                'escapedName' => 'Test Product',
                'expectedHeader' => 'Test Product'
            ],
            'new product returns New Product phrase' => [
                'productId' => null,
                'productName' => null,
                'escapedName' => null,
                'expectedHeader' => 'New Product'
            ],
            'product with empty name returns empty string' => [
                'productId' => 1,
                'productName' => '',
                'escapedName' => '',
                'expectedHeader' => ''
            ],
            'product name with special characters is escaped' => [
                'productId' => 1,
                'productName' => 'Product <b>"Special"</b> & \'Test\'',
                'escapedName' => 'Product &lt;b&gt;&quot;Special&quot;&lt;/b&gt; &amp; \'Test\'',
                'expectedHeader' => 'Product &lt;b&gt;&quot;Special&quot;&lt;/b&gt; &amp; \'Test\''
            ]
        ];
    }

    /**
     * Test getHeader returns correct header based on product state
     *
     * Tests various scenarios including:
     * - Existing product with normal name
     * - New product (no ID)
     * - Product with empty name
     * - Product name with special characters requiring escaping
     *
     * @dataProvider headerDataProvider
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getHeader
     * @param int|null $productId
     * @param string|null $productName
     * @param string|null $escapedName
     * @param string $expectedHeader
     * @return void
     */
    public function testGetHeaderReturnsCorrectHeader(
        ?int $productId,
        ?string $productName,
        ?string $escapedName,
        string $expectedHeader
    ): void {
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        if ($productId) {
            $this->productMock->expects($this->once())
                ->method('getName')
                ->willReturn($productName);
            $this->escaperMock->expects($this->once())
                ->method('escapeHtml')
                ->with($productName)
                ->willReturn($escapedName);
        }

        $result = $this->block->getHeader();
        $this->assertSame($expectedHeader, (string)$result);
    }

    /**
     * Data provider for testGetAttributeSetNameReturnsCorrectName
     *
     * @return array
     */
    public static function attributeSetNameDataProvider(): array
    {
        return [
            'product has attribute set' => [
                'setId' => 4,
                'setName' => 'Default',
                'expectedName' => 'Default'
            ],
            'product has no attribute set' => [
                'setId' => null,
                'setName' => null,
                'expectedName' => ''
            ],
            'invalid attribute set ID returns null' => [
                'setId' => 99999,
                'setName' => null,
                'expectedName' => null
            ],
            'empty attribute set name' => [
                'setId' => 4,
                'setName' => '',
                'expectedName' => ''
            ]
        ];
    }

    /**
     * Test getAttributeSetName returns correct attribute set name
     *
     * Tests various scenarios including:
     * - Valid attribute set ID with name
     * - No attribute set (null ID)
     * - Invalid/non-existent attribute set ID
     * - Empty attribute set name
     *
     * @dataProvider attributeSetNameDataProvider
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getAttributeSetName
     * @param int|null $setId
     * @param string|null $setName
     * @param string|null $expectedName
     * @return void
     */
    public function testGetAttributeSetNameReturnsCorrectName(
        ?int $setId,
        ?string $setName,
        ?string $expectedName
    ): void {
        $this->productMock->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn($setId);

        if ($setId) {
            $attributeSetMock = $this->createMock(Set::class);
            $attributeSetMock->expects($this->once())
                ->method('getAttributeSetName')
                ->willReturn($setName);
            $this->attributeSetFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($attributeSetMock);
            $attributeSetMock->expects($this->once())
                ->method('load')
                ->with($setId)
                ->willReturnSelf();
        }

        $this->assertSame($expectedName, $this->block->getAttributeSetName());
    }

    /**
     * Test getSelectedTabId returns empty string when tab param is null
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getSelectedTabId
     * @return void
     */
    public function testGetSelectedTabIdReturnsEmptyStringWhenTabIsNull(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('tab')
            ->willReturn(null);

        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with(null)
            ->willReturn('');

        $this->assertSame('', $this->block->getSelectedTabId());
    }

    /**
     * Test _getAttributes handles attributes with null applyTo values
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_getAttributes
     * @return void
     */
    public function testGetAttributesHandlesNullApplyToValues(): void
    {
        $attributeMock = $this->createMock(EavAttribute::class);
        $attributeMock->expects($this->once())
            ->method('getApplyTo')
            ->willReturn(null);

        $attributes = ['test_attribute' => $attributeMock];

        $this->productMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $result = $this->invokeProtectedMethod('_getAttributes');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('test_attribute', $result);
        $this->assertNull($result['test_attribute']);
    }

    /**
     * Test getSelectedTabId returns escaped tab ID from request
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getSelectedTabId
     * @return void
     */
    public function testGetSelectedTabIdReturnsEscapedTabId(): void
    {
        $tabId = 'product_info_tabs_group_4';
        $escapedTabId = 'product_info_tabs_group_4';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('tab')
            ->willReturn($tabId);

        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with($tabId)
            ->willReturn($escapedTabId);

        $this->assertSame($escapedTabId, $this->block->getSelectedTabId());
    }

    /**
     * Test getFieldsAutogenerationMasks returns helper masks
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getFieldsAutogenerationMasks
     * @return void
     */
    public function testGetFieldsAutogenerationMasksReturnsMasksFromHelper(): void
    {
        $expectedMasks = ['url_key' => '{{name}}'];
        $this->productHelperMock->expects($this->once())
            ->method('getFieldsAutogenerationMasks')
            ->willReturn($expectedMasks);

        $this->assertSame($expectedMasks, $this->block->getFieldsAutogenerationMasks());
    }

    /**
     * Test getAttributesAllowedForAutogeneration returns allowed attributes from helper
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getAttributesAllowedForAutogeneration
     * @return void
     */
    public function testGetAttributesAllowedForAutogenerationReturnsAttributesFromHelper(): void
    {
        $expectedAttributes = ['name', 'sku', 'url_key'];
        $this->productHelperMock->expects($this->once())
            ->method('getAttributesAllowedForAutogeneration')
            ->willReturn($expectedAttributes);

        $this->assertSame($expectedAttributes, $this->block->getAttributesAllowedForAutogeneration());
    }

    /**
     * Data provider for button HTML tests
     *
     * @return array
     */
    public static function buttonHtmlDataProvider(): array
    {
        return [
            'back button' => [
                'method' => 'getBackButtonHtml',
                'alias' => 'back_button',
                'expectedHtml' => '<button>Back</button>'
            ],
            'cancel/reset button' => [
                'method' => 'getCancelButtonHtml',
                'alias' => 'reset_button',
                'expectedHtml' => '<button>Reset</button>'
            ],
            'save button' => [
                'method' => 'getSaveButtonHtml',
                'alias' => 'save_button',
                'expectedHtml' => '<button>Save</button>'
            ],
            'save and edit button' => [
                'method' => 'getSaveAndEditButtonHtml',
                'alias' => 'save_and_edit_button',
                'expectedHtml' => '<button>Save & Edit</button>'
            ],
            'delete button' => [
                'method' => 'getDeleteButtonHtml',
                'alias' => 'delete_button',
                'expectedHtml' => '<button>Delete</button>'
            ],
            'save split button' => [
                'method' => 'getSaveSplitButtonHtml',
                'alias' => 'save-split-button',
                'expectedHtml' => '<button>Save Split</button>'
            ]
        ];
    }

    /**
     * Test button HTML methods return child HTML
     *
     * @dataProvider buttonHtmlDataProvider
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getBackButtonHtml
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getCancelButtonHtml
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getSaveButtonHtml
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getSaveAndEditButtonHtml
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getDeleteButtonHtml
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::getSaveSplitButtonHtml
     * @param string $method
     * @param string $alias
     * @param string $expectedHtml
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testButtonHtmlReturnsChildHtml(string $method, string $alias, string $expectedHtml): void
    {
        $blockName = str_replace('-', '_', $alias) . '_block';
        $this->layoutMock->expects($this->once())
            ->method('getChildName')
            ->willReturnCallback(function ($parentName, $childAlias) use ($alias, $blockName) {
                return $childAlias === $alias ? $blockName : '';
            });
        $this->layoutMock->expects($this->once())
            ->method('renderElement')
            ->with($blockName)
            ->willReturn($expectedHtml);

        $this->assertSame($expectedHtml, $this->block->$method());
    }

    /**
     * Data provider for testIsProductNew
     *
     * @return array
     */
    public static function isProductNewDataProvider(): array
    {
        return [
            'new product with no ID' => [
                'productId' => null,
                'expectedResult' => true
            ],
            'existing product with ID' => [
                'productId' => 123,
                'expectedResult' => false
            ]
        ];
    }

    /**
     * Test _isProductNew returns correct value based on product state
     *
     * @dataProvider isProductNewDataProvider
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_isProductNew
     * @param int|null $productId
     * @param bool $expectedResult
     * @return void
     */
    public function testIsProductNewReturnsCorrectValue(?int $productId, bool $expectedResult): void
    {
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->assertSame($expectedResult, $this->invokeProtectedMethod('_isProductNew'));
    }

    /**
     * Test _getAttributes returns array with attribute codes and apply to values
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_getAttributes
     * @return void
     */
    public function testGetAttributesReturnsAttributeApplyToArray(): void
    {
        $attribute1Mock = $this->createMock(EavAttribute::class);
        $attribute1Mock->expects($this->once())
            ->method('getApplyTo')
            ->willReturn(['simple', 'configurable']);

        $attribute2Mock = $this->createMock(EavAttribute::class);
        $attribute2Mock->expects($this->once())
            ->method('getApplyTo')
            ->willReturn(['virtual']);

        $attributes = [
            'color' => $attribute1Mock,
            'size' => $attribute2Mock
        ];

        $this->productMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $result = $this->invokeProtectedMethod('_getAttributes');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('color', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertSame(['simple', 'configurable'], $result['color']);
        $this->assertSame(['virtual'], $result['size']);
    }

    /**
     * Test _getAttributes returns empty array when product has no attributes
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_getAttributes
     * @return void
     */
    public function testGetAttributesReturnsEmptyArrayWhenNoAttributes(): void
    {
        $this->productMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn([]);

        $result = $this->invokeProtectedMethod('_getAttributes');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Data provider for testGetSaveSplitButtonOptions
     *
     * @return array
     */
    public static function saveSplitButtonOptionsDataProvider(): array
    {
        return [
            'non-popup mode with duplicable product' => [
                'isPopup' => false,
                'isDuplicable' => true,
                'expectedOptionsCount' => 4,
                'expectedOptionIds' => ['edit-button', 'new-button', 'duplicate-button', 'close-button']
            ],
            'non-popup mode with non-duplicable product' => [
                'isPopup' => false,
                'isDuplicable' => false,
                'expectedOptionsCount' => 3,
                'expectedOptionIds' => ['edit-button', 'new-button', 'close-button']
            ],
            'popup mode with duplicable product' => [
                'isPopup' => true,
                'isDuplicable' => true,
                'expectedOptionsCount' => 2,
                'expectedOptionIds' => ['new-button', 'close-button']
            ],
            'popup mode with non-duplicable product' => [
                'isPopup' => true,
                'isDuplicable' => false,
                'expectedOptionsCount' => 2,
                'expectedOptionIds' => ['new-button', 'close-button']
            ]
        ];
    }

    /**
     * Test _getSaveSplitButtonOptions returns correct options based on context
     *
     * @dataProvider saveSplitButtonOptionsDataProvider
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_getSaveSplitButtonOptions
     * @param bool $isPopup
     * @param bool $isDuplicable
     * @param int $expectedOptionsCount
     * @param array $expectedOptionIds
     * @return void
     */
    public function testGetSaveSplitButtonOptionsReturnsCorrectOptions(
        bool $isPopup,
        bool $isDuplicable,
        int $expectedOptionsCount,
        array $expectedOptionIds
    ): void {
        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param) use ($isPopup) {
                return $param === 'popup' ? ($isPopup ? '1' : null) : null;
            });

        if (!$isPopup) {
            $this->productMock->expects($this->once())
                ->method('isDuplicable')
                ->willReturn($isDuplicable);
        }

        $result = $this->invokeProtectedMethod('_getSaveSplitButtonOptions');

        $this->assertIsArray($result);
        $this->assertCount($expectedOptionsCount, $result);

        $resultIds = array_column($result, 'id');
        $this->assertSame($expectedOptionIds, $resultIds);
    }

    /**
     * Test _getSaveSplitButtonOptions save and edit is default in non-popup mode
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_getSaveSplitButtonOptions
     * @return void
     */
    public function testGetSaveSplitButtonOptionsSaveAndEditIsDefaultInNonPopupMode(): void
    {
        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param) {
                return $param === 'popup' ? null : null;
            });

        $this->productMock->expects($this->once())
            ->method('isDuplicable')
            ->willReturn(true);

        $result = $this->invokeProtectedMethod('_getSaveSplitButtonOptions');

        $editButton = $result[0];
        $this->assertSame('edit-button', $editButton['id']);
        $this->assertTrue($editButton['default']);
    }

    /**
     * Test _getSaveSplitButtonOptions has correct data attributes
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_getSaveSplitButtonOptions
     * @return void
     */
    public function testGetSaveSplitButtonOptionsHasCorrectDataAttributes(): void
    {
        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param) {
                return $param === 'popup' ? null : null;
            });

        $this->productMock->expects($this->once())
            ->method('isDuplicable')
            ->willReturn(true);

        $result = $this->invokeProtectedMethod('_getSaveSplitButtonOptions');

        // Verify Save & Edit button
        $editButton = $result[0];
        $this->assertArrayHasKey('data_attribute', $editButton);
        $this->assertArrayHasKey('mage-init', $editButton['data_attribute']);
        $this->assertSame(
            'saveAndContinueEdit',
            $editButton['data_attribute']['mage-init']['button']['event']
        );

        // Verify Save & New button
        $newButton = $result[1];
        $this->assertSame(
            'saveAndNew',
            $newButton['data_attribute']['mage-init']['button']['event']
        );

        // Verify Save & Duplicate button
        $duplicateButton = $result[2];
        $this->assertSame(
            'saveAndDuplicate',
            $duplicateButton['data_attribute']['mage-init']['button']['event']
        );

        // Verify Save & Close button
        $closeButton = $result[3];
        $this->assertSame(
            'save',
            $closeButton['data_attribute']['mage-init']['button']['event']
        );
    }

    /**
     * Test _prepareLayout adds back button in non-popup mode with toolbar
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_prepareLayout
     * @return void
     */
    public function testPrepareLayoutAddsBackButtonInNonPopupModeWithToolbar(): void
    {
        $toolbarMock = $this->getMockBuilder(ContainerInterface::class)
            ->addMethods(['addChild'])
            ->getMockForAbstractClass();

        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param, $default = null) {
                if ($param === 'popup') {
                    return null;
                }
                if ($param === 'store') {
                    return 1;
                }
                return $default;
            });

        $this->productMock->expects($this->atLeastOnce())
            ->method('isReadonly')
            ->willReturn(true);

        $this->layoutMock->expects($this->atLeastOnce())
            ->method('getBlock')
            ->with('page.actions.toolbar')
            ->willReturn($toolbarMock);

        $expectedUrl = 'http://example.com/catalog/product/';
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('catalog/*/', ['store' => 1])
            ->willReturn($expectedUrl);

        $toolbarMock->expects($this->once())
            ->method('addChild')
            ->with(
                'back_button',
                Button::class,
                $this->callback(function ($config) use ($expectedUrl) {
                    return isset($config['label'])
                        && isset($config['onclick'])
                        && str_contains($config['onclick'], $expectedUrl);
                })
            );

        $this->invokeProtectedMethod('_prepareLayout');
    }

    /**
     * Test _prepareLayout adds close window button in popup mode
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_prepareLayout
     * @return void
     */
    public function testPrepareLayoutAddsCloseWindowButtonInPopupMode(): void
    {
        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param, $default = null) {
                if ($param === 'popup') {
                    return '1';
                }
                return $default;
            });

        $this->productMock->expects($this->atLeastOnce())
            ->method('isReadonly')
            ->willReturn(true);

        $result = $this->invokeProtectedMethod('_prepareLayout');

        $this->assertSame($this->block, $result);
    }

    /**
     * Test _prepareLayout adds reset button when product is not readonly
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_prepareLayout
     * @return void
     */
    public function testPrepareLayoutAddsResetButtonWhenProductNotReadonly(): void
    {
        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param, $default = null) {
                if ($param === 'popup') {
                    return '1';
                }
                return $default;
            });

        $this->productMock->expects($this->atLeastOnce())
            ->method('isReadonly')
            ->willReturn(false);

        $expectedUrl = 'http://example.com/catalog/product/edit';
        $this->urlBuilderMock->method('getUrl')
            ->willReturn($expectedUrl);

        $result = $this->invokeProtectedMethod('_prepareLayout');

        $this->assertSame($this->block, $result);
    }

    /**
     * Test _prepareLayout adds save split button when product is not readonly and toolbar exists
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit::_prepareLayout
     * @return void
     */
    public function testPrepareLayoutAddsSaveSplitButtonWhenProductNotReadonlyWithToolbar(): void
    {
        $toolbarMock = $this->getMockBuilder(ContainerInterface::class)
            ->addMethods(['addChild'])
            ->getMockForAbstractClass();

        $this->requestMock->method('getParam')
            ->willReturnCallback(function ($param, $default = null) {
                if ($param === 'popup') {
                    return null;
                }
                if ($param === 'store') {
                    return 0;
                }
                return $default;
            });

        $this->productMock->expects($this->atLeastOnce())
            ->method('isReadonly')
            ->willReturn(false);

        $this->productMock->expects($this->atLeastOnce())
            ->method('isDuplicable')
            ->willReturn(true);

        $this->layoutMock->expects($this->atLeastOnce())
            ->method('getBlock')
            ->with('page.actions.toolbar')
            ->willReturn($toolbarMock);

        $this->urlBuilderMock->method('getUrl')
            ->willReturn('http://example.com/some/url');

        $toolbarMock->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnCallback(function ($name, $type, $config) use ($toolbarMock) {
                if ($name === 'save-split-button') {
                    $this->assertSame(SplitButton::class, $type);
                    $this->assertArrayHasKey('options', $config);
                    $this->assertIsArray($config['options']);
                }
                return $toolbarMock;
            });

        $this->invokeProtectedMethod('_prepareLayout');
    }
}
