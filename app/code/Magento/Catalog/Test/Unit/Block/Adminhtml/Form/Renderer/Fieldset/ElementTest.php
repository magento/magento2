<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Form\Renderer\Fieldset;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ElementTest extends TestCase
{
    /**
     * @var Element
     */
    private Element $block;

    /**
     * @var AbstractElement|MockObject
     */
    private $elementMock;

    /**
     * @var DataObject
     */
    private DataObject $formDataObject;

    /**
     * @var Product|MockObject
     */
    private $dataObjectMock;

    /**
     * @var Attribute|MockObject
     */
    private $attributeMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var TemplateContext|MockObject
     */
    private $contextMock;

    /**
     * Prepare SUT and collaborators.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        // Preserve real methods on AbstractElement so magic __call and setData work
        $this->elementMock = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dataObjectMock = $this->createMock(Product::class);
        $this->attributeMock = $this->createMock(Attribute::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        // Use DataObject to provide magic getDataObject() via __call
        $this->formDataObject = new DataObject([
            'data_object' => $this->dataObjectMock,
            'html_id_prefix' => '',
            'html_id_suffix' => ''
        ]);

        // Attach form and attribute to the element via real methods/data
        $this->elementMock->setForm($this->formDataObject);
        $this->elementMock->setData('entity_attribute', $this->attributeMock);
        $this->elementMock->setData('name', 'test_name');
        $this->elementMock->setData('html_id', 'test_id');

        // Inject Escaper, Random and SecureHtmlRenderer into AbstractElement (constructor is disabled)
        $escaper = new Escaper();
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('abcdef1234');
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderStyleAsTag')->willReturn('');
        $secureRendererMock->method('renderEventListenerAsTag')->willReturn('');

        // Inject required collaborators into AbstractElement using reflection helper
        $this->setObjectProperty($this->elementMock, '_escaper', $escaper);
        $this->setObjectProperty($this->elementMock, 'random', $randomMock);
        $this->setObjectProperty($this->elementMock, 'secureRenderer', $secureRendererMock);

        // Instantiate real SUT like AssignProductsTest using ObjectManager helper
        $this->contextMock = $this->createMock(TemplateContext::class);
        $this->contextMock->method('getStoreManager')->willReturn($this->storeManagerMock);

        $this->block = $objectManager->getObject(
            Element::class,
            ['context' => $this->contextMock]
        );

        // Attach the form element to block via reflection
        $this->setObjectProperty($this->block, '_element', $this->elementMock);
    }

    /**
     * Set a private/protected property via reflection on an object, walking parents.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     * @return void
     */
    private function setObjectProperty(object $object, string $property, $value): void
    {
        $this->setObjectPropertyRecursive(new \ReflectionClass($object), $object, $property, $value);
    }

    /**
     * Recursively set a property on the declaring class without using loops.
     *
     * @param \ReflectionClass $ref
     * @param object $object
     * @param string $property
     * @param mixed $value
     * @return void
     */
    private function setObjectPropertyRecursive(\ReflectionClass $ref, object $object, string $property, $value): void
    {
        if ($ref->hasProperty($property)) {
            $refProp = $ref->getProperty($property);
            $refProp->setValue($object, $value);
            return;
        }
        $parent = $ref->getParentClass();
        if ($parent === false) {
            $this->fail(sprintf('Property "%s" not found on %s or its parents', $property, get_class($object)));
        }
        $this->setObjectPropertyRecursive($parent, $object, $property, $value);
    }

    /**
     * Test getDataObject returns the form's data object.
     *
     * @return void
     */
    public function testGetDataObject(): void
    {
        $this->assertSame($this->dataObjectMock, $this->block->getDataObject());
    }

    /**
     * Test getAttribute returns the element's entity attribute.
     *
     * @return void
     */
    public function testGetAttribute(): void
    {
        $this->assertSame($this->attributeMock, $this->block->getAttribute());
    }

    /**
     * Test getAttributeCode proxies to attribute->getAttributeCode.
     *
     * @return void
     */
    public function testGetAttributeCode(): void
    {
        $this->attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('test_code');
        $this->assertSame('test_code', $this->block->getAttributeCode());
    }

    /**
     * Test canDisplayUseDefault returns true when attribute not global and entity has id/store.
     *
     * @return void
     */
    public function testCanDisplayUseDefaultReturnsTrue(): void
    {
        $this->attributeMock->expects($this->once())
            ->method('isScopeGlobal')
            ->willReturn(false);
        $this->dataObjectMock->method('getId')->willReturn(1);
        $this->dataObjectMock->method('getStoreId')->willReturn(2);

        $this->assertTrue($this->block->canDisplayUseDefault());
    }

    /**
     * Test canDisplayUseDefault returns false when attribute is global.
     *
     * @return void
     */
    public function testCanDisplayUseDefaultReturnsFalse(): void
    {
        $this->attributeMock->expects($this->once())
            ->method('isScopeGlobal')
            ->willReturn(true);
        $this->assertFalse($this->block->canDisplayUseDefault());
    }

    /**
     * Data provider for usedDefault scenarios.
     *
     * @return array
     */

    public static function usedDefaultDataProvider(): array
    {
        return [
            'no_store_value_flag' => [
                'existsStoreValueFlag' => false,
                'storeId' => 1,
                'elementValue' => 'anything',
                'defaultValue' => 'default',
                'isRequired' => null,
                'expected' => true,
            ],
            'store_value_exists_equals_default' => [
                'existsStoreValueFlag' => true,
                'storeId' => 2,
                'elementValue' => 'default',
                'defaultValue' => 'default',
                'isRequired' => null,
                'expected' => false,
            ],
            'store_value_exists_not_equal_default' => [
                'existsStoreValueFlag' => true,
                'storeId' => 2,
                'elementValue' => 'custom',
                'defaultValue' => 'default',
                'isRequired' => null,
                'expected' => false,
            ],
            'default_false_not_required_value_present' => [
                'existsStoreValueFlag' => true,
                'storeId' => 2,
                'elementValue' => 'custom',
                'defaultValue' => false,
                'isRequired' => false,
                'expected' => false,
            ]
        ];
    }

    /**
     * Test usedDefault returns true when no store value flag is set.
     * @dataProvider usedDefaultDataProvider
     * @param bool $existsStoreValueFlag
     * @param int|null $storeId
     * @param mixed $elementValue
     * @param mixed $defaultValue
     * @param bool $expected
     * @return void
     */
    public function testUsedDefaultReturnsTrueWhenNoStoreValueFlag(
        bool $existsStoreValueFlag,
        ?int $storeId,
        mixed $elementValue,
        mixed $defaultValue,
        ?bool $isRequired,
        bool $expected
    ): void {
        $this->attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('test_code');

        $this->dataObjectMock->method('getAttributeDefaultValue')->willReturn($defaultValue);
        $this->dataObjectMock->method('getExistsStoreValueFlag')->willReturn($existsStoreValueFlag);

        if ($storeId !== null) {
            $this->dataObjectMock->method('getStoreId')->willReturn($storeId);
        }
        if ($elementValue !== null) {
            $this->elementMock->setData('value', $elementValue);
        }

        // only set getIsRequired when provided by dataset
        if ($isRequired !== null) {
            $this->attributeMock->method('getIsRequired')->willReturn($isRequired);
        }

        $this->assertSame($expected, $this->block->usedDefault());
    }

    /**
     * Test checkFieldDisable sets element disabled when default is used.
     *
     * @return void
     */
    public function testCheckFieldDisableDisablesElement(): void
    {
        $this->attributeMock->expects($this->once())
            ->method('isScopeGlobal')
            ->willReturn(false);
        $this->dataObjectMock->method('getId')->willReturn(1);
        $this->dataObjectMock->method('getStoreId')->willReturn(2);
        $this->attributeMock->method('getAttributeCode')->willReturn('test_code');
        $this->dataObjectMock->method('getAttributeDefaultValue')->willReturn('default');
        $this->dataObjectMock->method('getExistsStoreValueFlag')->willReturn(false);

        $this->block->checkFieldDisable();
        $this->assertTrue((bool)$this->elementMock->getData('disabled'));
    }

    /**
     * Data provider for getScopeLabel scenarios.
     *
     * @return array
     */
    public static function getScopeLabelDataProvider(): array
    {
        return [
            'single_store_mode_->_empty' => [
                'isSingleStoreMode' => true,
                'attributePresent'  => true,
                'frontendInput'     => 'text',
                'isGlobal'          => null,
                'isWebsite'         => null,
                'isStore'           => null,
                'expected'          => '',
            ],
            'no_attribute_->_empty' => [
                'isSingleStoreMode' => false,
                'attributePresent'  => false,
                'frontendInput'     => null,
                'isGlobal'          => null,
                'isWebsite'         => null,
                'isStore'           => null,
                'expected'          => '',
            ],
            'frontend_is_gallery_->_empty' => [
                'isSingleStoreMode' => false,
                'attributePresent'  => true,
                'frontendInput'     => 'gallery',
                'isGlobal'          => null,
                'isWebsite'         => null,
                'isStore'           => null,
                'expected'          => '',
            ],
            'global_scope_->_[GLOBAL]' => [
                'isSingleStoreMode' => false,
                'attributePresent'  => true,
                'frontendInput'     => 'text',
                'isGlobal'          => true,
                'isWebsite'         => false,
                'isStore'           => false,
                'expected'          => '[GLOBAL]',
            ],
            'website_scope_->_[WEBSITE]' => [
                'isSingleStoreMode' => false,
                'attributePresent'  => true,
                'frontendInput'     => 'text',
                'isGlobal'          => false,
                'isWebsite'         => true,
                'isStore'           => false,
                'expected'          => '[WEBSITE]',
            ],
            'store_scope_->_[STORE_VIEW]' => [
                'isSingleStoreMode' => false,
                'attributePresent'  => true,
                'frontendInput'     => 'text',
                'isGlobal'          => false,
                'isWebsite'         => false,
                'isStore'           => true,
                'expected'          => '[STORE VIEW]',
            ],
        ];
    }

    /**
     * Validate getScopeLabel returns correct label across scenarios.
     *
     * @dataProvider getScopeLabelDataProvider
     */
    public function testGetScopeLabelScenarios(
        bool $isSingleStoreMode,
        bool $attributePresent,
        ?string $frontendInput,
        ?bool $isGlobal,
        ?bool $isWebsite,
        ?bool $isStore,
        string $expected
    ): void {
        // Case 1: No attribute -> element has null attribute, label must be empty.
        if ($attributePresent === false) {
            $this->elementMock->setData('entity_attribute', null);
            $this->assertSame($expected, (string) $this->block->getScopeLabel());
            return;
        }

        // Make the attribute available to the block through the element.
        $this->elementMock->setData('entity_attribute', $this->attributeMock);

        // Case 2: Single store mode -> empty.
        $this->storeManagerMock
            ->method('isSingleStoreMode')
            ->willReturn($isSingleStoreMode);

        if ($isSingleStoreMode) {
            $this->assertSame($expected, (string) $this->block->getScopeLabel());
            return;
        }

        // Case 3: Gallery input -> empty.
        $this->attributeMock
            ->method('getFrontendInput')
            ->willReturn($frontendInput);

        if ($frontendInput === 'gallery') {
            $this->assertSame($expected, (string) $this->block->getScopeLabel());
            return;
        }

        // Case 4: Scope flags — provide deterministic booleans.
        // Null means "unspecified"; we treat it as false to avoid ambiguity.
        $this->attributeMock
            ->method('isScopeGlobal')
            ->willReturn((bool) $isGlobal);

        $this->attributeMock
            ->method('isScopeWebsite')
            ->willReturn((bool) $isWebsite);

        $this->attributeMock
            ->method('isScopeStore')
            ->willReturn((bool) $isStore);

        // Single assertion for the final label.
        $this->assertSame($expected, (string) $this->block->getScopeLabel());
    }

    /**
     * Test getElementLabelHtml contains label text when label is set.
     *
     * @return void
     */
    public function testGetElementLabelHtmlWithLabel(): void
    {
        $this->elementMock->setData('label', 'Test Label');
        $html = $this->block->getElementLabelHtml();
        $this->assertStringContainsString('Test Label', $html);
    }

    /**
     * Test getElementLabelHtml returns string when label is empty.
     *
     * @return void
     */
    public function testGetElementLabelHtmlWithoutLabel(): void
    {
        $this->elementMock->setData('label', '');
        $html = $this->block->getElementLabelHtml();
        $this->assertIsString($html);
    }

    /**
     * Test getElementHtml renders input element markup.
     *
     * @return void
     */
    public function testGetElementHtml(): void
    {
        $this->elementMock->setData('value', '');
        $html = $this->block->getElementHtml();
        $this->assertStringContainsString('<input', $html);
    }
}
