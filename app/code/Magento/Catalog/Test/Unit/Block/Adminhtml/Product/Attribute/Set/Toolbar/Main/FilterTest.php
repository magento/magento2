<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute\Set\Toolbar\Main;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar\Main\Filter;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit test for Filter block
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar\Main\Filter
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Filter
     */
    private Filter $block;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var MockObject&FormFactory
     */
    private MockObject $formFactoryMock;

    /**
     * @var MockObject&SetFactory
     */
    private MockObject $setFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Prepare ObjectManager for helpers used by parent blocks
        $objects = [
            [JsonHelper::class, $this->createMock(JsonHelper::class)],
            [DirectoryHelper::class, $this->createMock(DirectoryHelper::class)]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->formFactoryMock = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setFactoryMock = $this->getMockBuilder(SetFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $this->objectManager->getObject(
            Filter::class,
            [
                'formFactory' => $this->formFactoryMock,
                'setFactory' => $this->setFactoryMock
            ]
        );
    }

    /**
     * Data provider for attribute set options scenarios
     *
     * @return array<string, array<string, array<int, array<string, string>>>>
     */
    public static function attributeSetOptionsDataProvider(): array
    {
        return [
            'with attribute set options' => [
                'attributeSetOptions' => [
                    ['value' => '1', 'label' => 'Default'],
                    ['value' => '2', 'label' => 'Custom Set']
                ]
            ],
            'with empty attribute set collection' => [
                'attributeSetOptions' => []
            ]
        ];
    }

    /**
     * Test that _prepareForm creates form with select field for given attribute set options
     *
     * @param array<int, array<string, string>> $attributeSetOptions
     * @return void
     */
    #[DataProvider('attributeSetOptionsDataProvider')]
    public function testPrepareFormCreatesFormWithSelectField(array $attributeSetOptions): void
    {
        $selectElementMock = $this->createMock(Select::class);
        $formMock = $this->createPartialMockWithReflection(
            Form::class,
            ['addField', 'setUseContainer', 'setMethod']
        );

        $formMock->expects($this->once())
            ->method('setUseContainer')
            ->with(true)
            ->willReturnSelf();
        $formMock->expects($this->once())
            ->method('setMethod')
            ->with('post')
            ->willReturnSelf();
        $formMock->expects($this->once())
            ->method('addField')
            ->with(
                'set_switcher',
                'select',
                $this->callback(function (array $config) use ($attributeSetOptions) {
                    return isset($config['values']) && $config['values'] === $attributeSetOptions;
                })
            )
            ->willReturn($selectElementMock);

        $this->formFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($formMock);

        $this->setupAttributeSetMocks($attributeSetOptions);

        $prepareFormMethod = new ReflectionMethod(Filter::class, '_prepareForm');
        $prepareFormMethod->invoke($this->block);

        $this->assertSame($formMock, $this->block->getForm());
    }

    /**
     * Data provider for select field configuration tests
     *
     * @return array<string, array<string, string|bool>>
     */
    public static function selectFieldConfigurationDataProvider(): array
    {
        return [
            'onchange handler for form submission' => [
                'configKey' => 'onchange',
                'expectedValue' => 'this.form.submit()'
            ],
            'field is required' => [
                'configKey' => 'required',
                'expectedValue' => true
            ],
            'correct CSS class' => [
                'configKey' => 'class',
                'expectedValue' => 'left-col-block'
            ],
            'no_span option enabled' => [
                'configKey' => 'no_span',
                'expectedValue' => true
            ],
            'correct field name' => [
                'configKey' => 'name',
                'expectedValue' => 'set_switcher'
            ]
        ];
    }

    /**
     * Test that select field has correct configuration
     *
     * @param string $configKey
     * @param string|bool $expectedValue
     * @return void
     */
    #[DataProvider('selectFieldConfigurationDataProvider')]
    public function testSelectFieldHasCorrectConfiguration(string $configKey, string|bool $expectedValue): void
    {
        $this->setupAttributeSetMocks([]);

        $selectElementMock = $this->createMock(Select::class);
        $formMock = $this->createPartialMockWithReflection(
            Form::class,
            ['addField', 'setUseContainer', 'setMethod']
        );

        $formMock->expects($this->once())
            ->method('addField')
            ->with(
                'set_switcher',
                'select',
                $this->callback(function (array $config) use ($configKey, $expectedValue) {
                    return isset($config[$configKey]) && $config[$configKey] === $expectedValue;
                })
            )
            ->willReturn($selectElementMock);
        $formMock->expects($this->once())
            ->method('setUseContainer')
            ->with(true)
            ->willReturnSelf();
        $formMock->expects($this->once())
            ->method('setMethod')
            ->with('post')
            ->willReturnSelf();

        $this->formFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($formMock);

        $prepareFormMethod = new ReflectionMethod(Filter::class, '_prepareForm');
        $prepareFormMethod->invoke($this->block);
    }

    /**
     * Setup attribute set mocks for _prepareForm method testing
     *
     * Creates and configures attribute set related mocks for testing the protected _prepareForm method.
     *
     * @param array<int, array<string, string>> $attributeSetOptions Attribute set options to return from collection
     * @return void
     */
    private function setupAttributeSetMocks(array $attributeSetOptions): void
    {
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($attributeSetOptions);

        $attributeSetMock = $this->getMockBuilder(AttributeSet::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetMock->expects($this->once())
            ->method('getResourceCollection')
            ->willReturn($collectionMock);

        $this->setFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($attributeSetMock);
    }
}
