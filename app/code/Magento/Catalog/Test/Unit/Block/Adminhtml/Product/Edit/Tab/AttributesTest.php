<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Create;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Tier;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Image;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean;
use \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg;

/**
 * Unit tests for Attributes block
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AttributesTest extends TestCase
{
    use MockCreationTrait;

    /**
     * Test attribute set ID for mock product
     */
    private const TEST_ATTRIBUTE_SET_ID = 4;

    /**
     * Test store ID for mock product
     */
    private const TEST_STORE_ID = 1;

    /**
     * Test group ID for attribute group
     */
    private const TEST_GROUP_ID = 1;

    /**
     * @var Attributes|MockObject
     */
    private Attributes|MockObject $attributesMock;

    /**
     * @var FormFactory|MockObject
     */
    private FormFactory|MockObject $formFactoryMock;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private ManagerInterface|MockObject $eventManagerMock;

    /**
     * @var AuthorizationInterface|MockObject
     */
    private AuthorizationInterface|MockObject $authorizationMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private LayoutInterface|MockObject $layoutMock;

    protected function setUp(): void
    {
        $this->formFactoryMock = $this->createMock(FormFactory::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->authorizationMock = $this->createMock(AuthorizationInterface::class);
        $this->layoutMock = $this->createMock(LayoutInterface::class);

        $this->attributesMock = $this->createPartialMockWithReflection(
            Attributes::class,
            ['getGroup', 'getGroupAttributes', '_setFieldset', 'setForm']
        );

        $this->injectDependencies();
    }

    /**
     * Test getAdditionalElementTypes returns all default element types
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_getAdditionalElementTypes
     * @return void
     */
    public function testGetAdditionalElementTypesReturnsDefaultTypes(): void
    {
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('adminhtml_catalog_product_edit_element_types');

        $result = $this->invokeProtectedMethod('_getAdditionalElementTypes');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('price', $result);
        $this->assertEquals(Price::class, $result['price']);
        $this->assertArrayHasKey('weight', $result);
        $this->assertEquals(Weight::class, $result['weight']);
        $this->assertArrayHasKey('gallery', $result);
        $this->assertEquals(Gallery::class, $result['gallery']);
        $this->assertArrayHasKey('image', $result);
        $this->assertEquals(Image::class, $result['image']);
        $this->assertArrayHasKey('boolean', $result);
        $this->assertEquals(Boolean::class, $result['boolean']);
        $this->assertArrayHasKey('textarea', $result);
        $this->assertEquals(Wysiwyg::class, $result['textarea']);
    }

    /**
     * Test getAdditionalElementTypes merges custom types from event
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_getAdditionalElementTypes
     * @return void
     */
    public function testGetAdditionalElementTypesMergesCustomTypes(): void
    {
        $customTypes = [
            'custom_type' => 'Custom\Type\Class',
            'another_type' => 'Another\Type\Class'
        ];

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('adminhtml_catalog_product_edit_element_types')
            ->willReturnCallback(function ($eventName, $data) use ($customTypes) {
                unset($eventName);
                $data['response']->setTypes($customTypes);
            });

        $result = $this->invokeProtectedMethod('_getAdditionalElementTypes');

        $this->assertArrayHasKey('price', $result);
        $this->assertArrayHasKey('custom_type', $result);
        $this->assertArrayHasKey('another_type', $result);
        $this->assertEquals('Custom\Type\Class', $result['custom_type']);
        $this->assertEquals('Another\Type\Class', $result['another_type']);
    }

    /**
     * Test prepareForm returns early when no group is set
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm
     * @return void
     */
    public function testPrepareFormReturnsEarlyWhenNoGroup(): void
    {
        $this->formFactoryMock->expects($this->never())->method('create');
        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->attributesMock->method('getGroup')->willReturn(null);

        $this->invokeProtectedMethod('_prepareForm');
    }

    /**
     * Test prepareForm creates form and calls setDataObject
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm
     * @return void
     */
    public function testPrepareFormCreatesFormWithProduct(): void
    {
        $mocks = $this->setupStandardPrepareFormTest();

        $mocks['form']->expects($this->once())
            ->method('setDataObject')
            ->with($mocks['product'])
            ->willReturnSelf();

        $this->invokeProtectedMethod('_prepareForm');
    }

    /**
     * Test prepareForm sets tier price renderer
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm
     * @return void
     */
    public function testPrepareFormSetsTierPriceRenderer(): void
    {
        $productMock = $this->createProductMock(1, false);
        $formMock = $this->createFormMock();
        $groupMock = $this->createGroupMock('pricing', 'Pricing');

        $this->setupRegistryForProduct($productMock, true);
        $this->setupFormFactory($formMock);
        $this->setupBasicFormExpectations($formMock, false);

        $tierPriceElementMock = $this->createMock(AbstractElement::class);
        $tierBlockMock = $this->createMock(Tier::class);

        $formMock->method('getElement')->willReturnMap([
            ['tier_price', $tierPriceElementMock],
            ['media_gallery', null]
        ]);

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(Tier::class)
            ->willReturn($tierBlockMock);

        $tierPriceElementMock->expects($this->once())
            ->method('setRenderer')
            ->with($tierBlockMock);

        $this->attributesMock->method('getGroup')->willReturn($groupMock);
        $this->attributesMock->method('getGroupAttributes')->willReturn([]);

        $this->invokeProtectedMethod('_prepareForm');
    }

    /**
     * Test prepareForm creates attribute controls when authorized
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm
     * @return void
     */
    public function testPrepareFormCreatesAttributeControlsWhenAuthorized(): void
    {
        $productMock = $this->createProductMock(1, false);
        $this->configureProductExtended($productMock);

        $formMock = $this->createFormMock();
        $formMock->method('getDataObject')->willReturn($productMock);
        $fieldsetMock = $this->createFieldsetMock();
        $createBlockMock = $this->createAttributeCreateBlockMock();
        $searchBlockMock = $this->createAttributeSearchBlockMock();
        $groupMock = $this->createGroupMock('general', 'General', self::TEST_GROUP_ID);

        $this->setupRegistryForProduct($productMock, true);
        $this->setupFormFactory($formMock);
        $this->setupExtendedFormExpectations($formMock, $fieldsetMock);

        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Catalog::attributes_attributes')
            ->willReturn(true);

        $this->layoutMock->expects($this->exactly(2))
            ->method('createBlock')
            ->willReturnOnConsecutiveCalls($createBlockMock, $searchBlockMock);

        $searchBlockMock->expects($this->once())
            ->method('setAttributeCreate')
            ->with('<create-html>');
        $fieldsetMock->expects($this->once())
            ->method('setHeaderBar')
            ->with('<search-html>');

        $this->attributesMock->method('getGroup')->willReturn($groupMock);
        $this->attributesMock->method('getGroupAttributes')->willReturn([]);

        $this->invokeProtectedMethod('_prepareForm');
    }

    /**
     * Test prepareForm does not create attribute controls when not authorized
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm
     * @return void
     */
    public function testPrepareFormDoesNotCreateAttributeControlsWhenNotAuthorized(): void
    {
        $productMock = $this->createProductMock(1, false);
        $this->configureProductExtended($productMock);

        $formMock = $this->createFormMock();
        $formMock->method('getDataObject')->willReturn($productMock);
        $fieldsetMock = $this->createFieldsetMock();
        $groupMock = $this->createGroupMock('general', 'General', self::TEST_GROUP_ID);

        $this->setupRegistryForProduct($productMock, true);
        $this->setupFormFactory($formMock);
        $this->setupExtendedFormExpectations($formMock, $fieldsetMock);

        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Catalog::attributes_attributes')
            ->willReturn(false);

        $this->layoutMock->expects($this->never())
            ->method('createBlock');

        $fieldsetMock->expects($this->never())
            ->method('setHeaderBar');

        $this->attributesMock->method('getGroup')->willReturn($groupMock);
        $this->attributesMock->method('getGroupAttributes')->willReturn([]);

        $this->invokeProtectedMethod('_prepareForm');
    }

    /**
     * Test prepareForm sets default values for new product
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm
     * @return void
     */
    public function testPrepareFormSetsDefaultValuesForNewProduct(): void
    {
        $productMock = $this->createProductMock(null, false);
        $formMock = $this->createFormMock();
        $groupMock = $this->createGroupMock('general', 'General');

        $this->setupRegistryForProduct($productMock, true);
        $this->setupFormFactory($formMock);
        $this->setupBasicFormExpectations($formMock);

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->method('getAttributeCode')->willReturn('custom_attribute');
        $attributeMock->method('getDefaultValue')->willReturn('default_value');

        $formMock->expects($this->once())
            ->method('addValues')
            ->with($this->callback(function ($values) {
                return isset($values['custom_attribute']) &&
                       $values['custom_attribute'] === 'default_value';
            }))
            ->willReturnSelf();

        $this->attributesMock->method('getGroup')->willReturn($groupMock);
        $this->attributesMock->method('getGroupAttributes')->willReturn([$attributeMock]);

        $this->invokeProtectedMethod('_prepareForm');
    }

    /**
     * Test prepareForm sets attributes readonly when product has locked attributes
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm
     * @return void
     */
    public function testPrepareFormSetsAttributesReadonlyWhenProductHasLockedAttributes(): void
    {
        $productMock = $this->createProductMock(1, true, ['name']);
        $formMock = $this->createFormMock();
        $elementMock = $this->createMock(AbstractElement::class);
        $groupMock = $this->createGroupMock('general', 'General');

        $this->setupRegistryForProduct($productMock, true);
        $this->setupFormFactory($formMock);
        $this->setupBasicFormExpectations($formMock, false);

        $formMock->method('getElement')->willReturnMap([
            ['tier_price', null],
            ['media_gallery', null],
            ['name', $elementMock]
        ]);

        $elementMock->expects($this->once())
            ->method('setReadonly')
            ->with(true, true);

        $this->attributesMock->method('getGroup')->willReturn($groupMock);
        $this->attributesMock->method('getGroupAttributes')->willReturn([]);

        $this->invokeProtectedMethod('_prepareForm');
    }

    /**
     * Test prepareForm dispatches event with form and layout
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm
     * @return void
     */
    public function testPrepareFormDispatchesEventWithFormAndLayout(): void
    {
        $mocks = $this->setupStandardPrepareFormTest();

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'adminhtml_catalog_product_edit_prepare_form',
                $this->callback(function ($data) use ($mocks) {
                    return isset($data['form'])
                        && $data['form'] === $mocks['form']
                        && isset($data['layout'])
                        && $data['layout'] === $this->layoutMock;
                })
            );

        $this->invokeProtectedMethod('_prepareForm');
    }

    /**
     * Test prepareForm when use_wrapper is false does not add attribute create blocks
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm
     * @return void
     */
    public function testPrepareFormWhenUseWrapperIsFalseDoesNotAddAttributeBlocks(): void
    {
        $productMock = $this->createProductMock(1, false);
        $formMock = $this->createFormMock();
        $groupMock = $this->createGroupMock('general', 'General');
        $fieldsetMock = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);

        $this->setupRegistryForProduct($productMock, false);
        $this->setupFormFactory($formMock);
        $this->setupBasicFormExpectations($formMock);

        // When use_wrapper is false, legend should be null and collapsable should be false
        $formMock->expects($this->once())
            ->method('addFieldset')
            ->with(
                'group-fields-general',
                $this->callback(function ($config) {
                    return $config['legend'] === null
                        && $config['collapsable'] === false;
                })
            )
            ->willReturn($fieldsetMock);

        // When use_wrapper is false, attribute create/search blocks should NOT be created
        $this->layoutMock->expects($this->never())
            ->method('createBlock')
            ->with(\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Create::class);

        $this->attributesMock->method('getGroup')->willReturn($groupMock);
        $this->attributesMock->method('getGroupAttributes')->willReturn([]);

        $this->invokeProtectedMethod('_prepareForm');
    }

    /**
     * Inject dependencies into the attributes mock via reflection
     *
     * @return void
     */
    private function injectDependencies(): void
    {
        $reflection = new \ReflectionClass($this->attributesMock);

        $dependencies = [
            '_eventManager' => $this->eventManagerMock,
            '_layout' => $this->layoutMock,
            '_formFactory' => $this->formFactoryMock,
            '_coreRegistry' => $this->registryMock,
            '_authorization' => $this->authorizationMock
        ];

        foreach ($dependencies as $propertyName => $value) {
            $property = $reflection->getProperty($propertyName);
            $property->setValue($this->attributesMock, $value);
        }
    }

    /**
     * Invoke a protected method via reflection
     *
     * @param string $methodName
     * @return mixed
     */
    private function invokeProtectedMethod(string $methodName): mixed
    {
        $reflection = new \ReflectionClass($this->attributesMock);
        $method = $reflection->getMethod($methodName);
        return $method->invoke($this->attributesMock);
    }

    /**
     * Create a configured product mock
     *
     * @param int|null $id
     * @param bool $hasLockedAttributes
     * @param array $lockedAttributes
     * @return MockObject
     */
    private function createProductMock(?int $id, bool $hasLockedAttributes, array $lockedAttributes = []): MockObject
    {
        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn($id);
        $productMock->method('hasLockedAttributes')->willReturn($hasLockedAttributes);
        $productMock->method('getLockedAttributes')->willReturn($lockedAttributes);
        $productMock->method('dataHasChangedFor')->willReturn(false);
        return $productMock;
    }

    /**
     * Create a configured form mock
     *
     * @return MockObject
     */
    private function createFormMock(): MockObject
    {
        return $this->createPartialMockWithReflection(
            Form::class,
            ['setDataObject', 'getDataObject', 'setFieldNameSuffix', 'addFieldset', 'getElement', 'addValues']
        );
    }

    /**
     * Create a configured fieldset mock
     *
     * @return MockObject
     */
    private function createFieldsetMock(): MockObject
    {
        return $this->createPartialMockWithReflection(
            Fieldset::class,
            ['setHeaderBar']
        );
    }

    /**
     * Create a configured group mock
     *
     * @param string $code
     * @param string $name
     * @param int|null $id
     * @return MockObject
     */
    private function createGroupMock(string $code, string $name, ?int $id = null): MockObject
    {
        $groupMock = $this->createPartialMockWithReflection(
            Group::class,
            ['getAttributeGroupCode', 'getAttributeGroupName', 'getId']
        );

        $groupMock->method('getAttributeGroupCode')->willReturn($code);
        $groupMock->method('getAttributeGroupName')->willReturn($name);

        if ($id !== null) {
            $groupMock->method('getId')->willReturn($id);
        }

        return $groupMock;
    }

    /**
     * Setup registry mock to return product and use_wrapper flag
     *
     * @param MockObject $productMock
     * @param bool $useWrapper
     * @return void
     */
    private function setupRegistryForProduct(MockObject $productMock, bool $useWrapper): void
    {
        $this->registryMock->method('registry')->willReturnMap([
            ['product', $productMock],
            ['use_wrapper', $useWrapper]
        ]);
    }

    /**
     * Setup form factory to return form mock
     *
     * @param MockObject $formMock
     * @return void
     */
    private function setupFormFactory(MockObject $formMock): void
    {
        $this->formFactoryMock->method('create')->willReturn($formMock);
    }

    /**
     * Setup basic form expectations common to most tests
     *
     * @param MockObject $formMock
     * @param bool $includeGetElement
     * @return void
     */
    private function setupBasicFormExpectations(MockObject $formMock, bool $includeGetElement = true): void
    {
        $fieldsetMock = $this->createFieldsetMock();

        $formMock->method('addFieldset')->willReturn($fieldsetMock);

        if ($includeGetElement) {
            $formMock->method('getElement')->willReturn(null);
        }

        $formMock->method('setFieldNameSuffix')->willReturnSelf();
        $formMock->method('addValues')->willReturnSelf();
        $formMock->method('setDataObject')->willReturnSelf();
    }

    /**
     * Setup standard mocks for _prepareForm tests
     *
     * @param int|null $productId
     * @param string $groupCode
     * @param string $groupName
     * @param bool $useWrapper
     * @return array{product: MockObject, form: MockObject, group: MockObject}
     */
    private function setupStandardPrepareFormTest(
        ?int $productId = 1,
        string $groupCode = 'general',
        string $groupName = 'General',
        bool $useWrapper = true
    ): array {
        $productMock = $this->createProductMock($productId, false);
        $formMock = $this->createFormMock();
        $groupMock = $this->createGroupMock($groupCode, $groupName);

        $this->setupRegistryForProduct($productMock, $useWrapper);
        $this->setupFormFactory($formMock);
        $this->setupBasicFormExpectations($formMock);

        $this->attributesMock->method('getGroup')->willReturn($groupMock);
        $this->attributesMock->method('getGroupAttributes')->willReturn([]);

        return [
            'product' => $productMock,
            'form' => $formMock,
            'group' => $groupMock
        ];
    }

    /**
     * Setup extended form mock configuration
     *
     *
     * @param MockObject $formMock
     * @param MockObject $fieldsetMock
     * @return void
     */
    private function setupExtendedFormExpectations(MockObject $formMock, MockObject $fieldsetMock): void
    {
        $formMock->method('addFieldset')->willReturn($fieldsetMock);
        $formMock->method('setDataObject')->willReturnSelf();
        $formMock->method('getElement')->willReturn(null);
        $formMock->method('setFieldNameSuffix')->willReturnSelf();
        $formMock->method('addValues')->willReturnSelf();
    }

    /**
     * Configure product mock with store, attribute set, and type
     *
     * @param MockObject $productMock
     * @param int $storeId
     * @param int $attributeSetId
     * @param string $typeId
     * @return void
     */
    private function configureProductExtended(
        MockObject $productMock,
        int $storeId = self::TEST_STORE_ID,
        int $attributeSetId = self::TEST_ATTRIBUTE_SET_ID,
        string $typeId = 'simple'
    ): void {
        $productMock->method('getStoreId')->willReturn($storeId);
        $productMock->method('getAttributeSetId')->willReturn($attributeSetId);
        $productMock->method('getTypeId')->willReturn($typeId);
    }

    /**
     * Create attribute config mock with fluent setters
     *
     * @return MockObject
     */
    private function createAttributeConfigMock(): MockObject
    {
        $configMock = $this->createPartialMockWithReflection(
            DataObject::class,
            [
                'setAttributeGroupCode', 'setTabId', 'setGroupId',
                'setStoreId', 'setAttributeSetId', 'setTypeId', 'setProductId'
            ]
        );
        $fluentMethods = [
            'setAttributeGroupCode', 'setTabId', 'setGroupId', 'setStoreId',
            'setAttributeSetId', 'setTypeId', 'setProductId'
        ];

        foreach ($fluentMethods as $method) {
            $configMock->method($method)->willReturnSelf();
        }

        return $configMock;
    }

    /**
     * Create attribute create block mock
     *
     * @return MockObject
     */
    private function createAttributeCreateBlockMock(): MockObject
    {
        $configMock = $this->createAttributeConfigMock();

        $createBlockMock = $this->createMock(Create::class);
        $createBlockMock->method('getConfig')->willReturn($configMock);
        $createBlockMock->method('toHtml')->willReturn('<create-html>');

        return $createBlockMock;
    }

    /**
     * Create attribute search block mock
     *
     * @return MockObject
     */
    private function createAttributeSearchBlockMock(): MockObject
    {
        $searchBlockMock = $this->createPartialMockWithReflection(
            Search::class,
            ['setGroupId', 'setGroupCode', 'setAttributeCreate', 'toHtml']
        );

        $searchBlockMock->method('setGroupId')->willReturnSelf();
        $searchBlockMock->method('setGroupCode')->willReturnSelf();
        $searchBlockMock->method('setAttributeCreate')->willReturnSelf();
        $searchBlockMock->method('toHtml')->willReturn('<search-html>');

        return $searchBlockMock;
    }
}
