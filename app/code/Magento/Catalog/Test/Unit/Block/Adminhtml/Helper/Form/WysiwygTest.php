<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Helper\Data as BackendHelperData;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\View\LayoutInterface;
use ReflectionClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WysiwygTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var ElementFactory|MockObject
     */
    private $factoryElement;
    /**
     * @var ElementCollectionFactory|MockObject
     */
    private $factoryCollection;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var WysiwygConfig|MockObject
     */
    private $wysiwygConfig;
    /**
     * @var LayoutInterface|MockObject
     */
    private $layout;
    /**
     * @var ModuleManager|MockObject
     */
    private $moduleManager;
    /**
     * @var BackendHelperData|MockObject
     */
    private $backendData;
    /**
     * @var SecureHtmlRenderer|MockObject
     */
    private $secureRenderer;
    /**
     * @var Wysiwyg
     */
    private Wysiwyg $element;

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->escaper = $this->createMock(Escaper::class);
        $this->escaper
            ->method('escapeHtml')
            ->willReturnCallback(
                function ($value) {
                    return (string)$value;
                }
            );
        $this->factoryElement = $this->createMock(ElementFactory::class);
        $this->factoryCollection = $this->createMock(ElementCollectionFactory::class);
        $this->wysiwygConfig = $this->createMock(WysiwygConfig::class);
        $this->layout = $this->createMock(LayoutInterface::class);
        $this->moduleManager = $this->createMock(ModuleManager::class);
        $this->backendData = $this->createMock(BackendHelperData::class);
        $this->secureRenderer = $this->createMock(SecureHtmlRenderer::class);

        $this->objectManager->prepareObjectManager(
            [
                [SecureHtmlRenderer::class, $this->secureRenderer],
                [Random::class, new Random()],
            ]
        );

        $this->element = $this->objectManager->getObject(
            Wysiwyg::class,
            [
                'factoryElement'    => $this->factoryElement,
                'factoryCollection' => $this->factoryCollection,
                'escaper'           => $this->escaper,
                'wysiwygConfig'     => $this->wysiwygConfig,
                'layout'            => $this->layout,
                'moduleManager'     => $this->moduleManager,
                'backendData'       => $this->backendData,
                'secureRenderer'    => $this->secureRenderer,
            ]
        );

        $this->setPrivateProperty(
            $this->element,
            AbstractElement::class,
            'random',
            new Random()
        );

        $formMock = $this->createPartialMockWithReflection(Form::class, ['getHtmlIdPrefix', 'getHtmlIdSuffix']);
        $formMock->method('getHtmlIdPrefix')->willReturn('');
        $formMock->method('getHtmlIdSuffix')->willReturn('');
        $this->element->setForm($formMock);
    }

    /**
     * Ensure getAfterElementHtml appends the WYSIWYG button and initialization script
     * markers when the module/config/attribute flags enable the editor.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg::getAfterElementHtml
     * @return void
     */
    public function testGetAfterElementHtmlWhenEnabledAddsButtonAndScript(): void
    {
        $this->givenEnabledFlags();
        $this->givenWysiwygConfig();
        $attributeMock = $this->givenEnabledAttribute();
        $this->givenBackendUrlAndLayoutButton();
        $this->givenSecureRendererForInitScript();
        $this->givenElementDataForEnabled($attributeMock, 'my_wysiwyg_field');

        $html = $this->element->getAfterElementHtml();

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('WYSIWYG Editor', $html);
        // Verify we now have a data‑mage‑init attribute (no inline onclick)
        $this->assertStringContainsString('data-mage-init', $html);
        $this->assertStringContainsString('catalogWysiwygEditor.open', $html);
        $this->assertStringContainsString('wysiwygSetup', $html);
        $this->assertStringContainsString('my_wysiwyg_field', $html);
        $this->assertStringContainsString('[script', $html);
        $this->assertStringContainsString('text/x-magento-init', $html);
        $this->assertStringContainsString('[/script]', $html);
    }

    /**
     * Set module/config flags to enabled.
     *
     * @return void
     */
    private function givenEnabledFlags(): void
    {
        $this->moduleManager
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_Cms')
            ->willReturn(true);
        $this->wysiwygConfig
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
    }

    /**
     * Stub WYSIWYG config object returned by getConfig.
     *
     * @return void
     */
    private function givenWysiwygConfig(): void
    {
        $this->wysiwygConfig
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn(
                new DataObject(
                    [
                        'plugins' => ['advlist', 'autolink'],
                        'menubar' => false,
                    ]
                )
            );
    }

    /**
     * Provide an attribute whose getIsWysiwygEnabled returns true.
     *
     * @return object
     */
    private function givenEnabledAttribute(): object
    {
        $attributeMock = $this->createPartialMockWithReflection(DataObject::class, ['getIsWysiwygEnabled']);
        $attributeMock->method('getIsWysiwygEnabled')->willReturn(true);
        return $attributeMock;
    }

    /**
     * Stub backend URL and return a button block that outputs data-mage-init instead of inline onclick.
     *
     * @return void
     */
    private function givenBackendUrlAndLayoutButton(): void
    {
        $this->backendData
            ->method('getUrl')
            ->with('catalog/product/wysiwyg')
            ->willReturn('http://example.com/catalog/product/wysiwyg');

        $this->layout
            ->method('createBlock')
            ->with(
                Button::class,
                '',
                $this->callback(
                    function ($args) {
                        if (!isset($args['data'])) {
                            return false;
                        }
                        $data = $args['data'];
                        return isset($data['label'], $data['type'], $data['class'], $data['onclick'])
                            && method_exists($data['label'], '__toString')
                            && (string) $data['label'] === 'WYSIWYG Editor'
                            && $data['type'] === 'button'
                            && $data['class'] === 'action-wysiwyg';
                    }
                )
            )
            ->willReturnCallback(
                function (...$params) {
                    $args = $params[2] ?? [];
                    $onclick = $args['data']['onclick'] ?? '';
                    $initJson = json_encode(['catalogWysiwygEditor' => ['open' => $onclick]]);
                    return new class ($initJson) {
                        /**
                         * @var string
                         */
                        private string $initJson;
                        public function __construct(string $initJson)
                        {
                            $this->initJson = $initJson;
                        }
                        public function toHtml(): string
                        {
                            return '<button class="action-wysiwyg" '
                                . 'data-mage-init=\'' . $this->initJson . '\'>'
                                . 'WYSIWYG Editor</button>';
                        }
                    };
                }
            );
    }

    /**
     * Stub secure renderer to emit a text/x-magento-init script wrapper.
     *
     * @return void
     */
    private function givenSecureRendererForInitScript(): void
    {
        $this->secureRenderer
            ->expects($this->once())
            ->method('renderTag')
            ->with(
                'script',
                $this->isType('array'),
                $this->stringContains('mage/adminhtml/wysiwyg/tiny_mce/setup'),
                false
            )
            ->willReturnCallback(
                function (...$args) {
                    $content = (string) ($args[2] ?? '');
                    return '[script type="text/x-magento-init"]' . $content . '[/script]';
                }
            );
    }

    /**
     * Set the per-test element data for the enabled scenario.
     *
     * @param  object $attributeMock
     * @param  string $htmlId
     * @return void
     */
    private function givenElementDataForEnabled(object $attributeMock, string $htmlId): void
    {
        $this->element->setData('html_id', $htmlId);
        $this->element->setData('entity_attribute', $attributeMock);
        $this->element->setData('name', $htmlId);
        $this->element->setData('value', '');
    }

    /**
     * Ensure getAfterElementHtml does not append button or script markers when
     * the editor is disabled by configuration.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg::getAfterElementHtml
     * @return void
     */
    public function testGetAfterElementHtmlWhenDisabledReturnsParentHtmlOnly(): void
    {
        $this->moduleManager
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_Cms')
            ->willReturn(false);
        $this->wysiwygConfig
            ->expects($this->never())
            ->method('isEnabled');
        $this->wysiwygConfig
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn(new DataObject([]));

        $this->secureRenderer
            ->expects($this->never())
            ->method('renderTag');
        $this->layout
            ->expects($this->never())
            ->method('createBlock');

        $this->element->setData('html_id', 'disabled_field');
        $this->element->setData('name', 'disabled_field');
        $this->element->setData('value', '');

        $html = $this->element->getAfterElementHtml();

        $this->assertIsString($html);
        $this->assertStringNotContainsString('WYSIWYG Editor', $html);
        $this->assertStringNotContainsString('catalogWysiwygEditor.open', $html);
        $this->assertStringNotContainsString('wysiwygSetup', $html);
    }

    /**
     * Validate getIsWysiwygEnabled across combinations of module/config/attribute flags.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg::getIsWysiwygEnabled
     * @param bool $moduleEnabled
     * @param bool $configEnabled
     * @param bool $attributeEnabled
     * @param bool $expected
     *
     * @return void
     */
    #[DataProvider('getIsWysiwygEnabledDataProvider')]
    public function testGetIsWysiwygEnabled(
        bool $moduleEnabled,
        bool $configEnabled,
        bool $attributeEnabled,
        bool $expected
    ): void {
        $this->moduleManager
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_Cms')
            ->willReturn($moduleEnabled);
        if ($moduleEnabled) {
            $this->wysiwygConfig
                ->expects($this->once())
                ->method('isEnabled')
                ->willReturn($configEnabled);
        } else {
            $this->wysiwygConfig
                ->expects($this->never())
                ->method('isEnabled');
        }
        $attributeMock = $this->createPartialMockWithReflection(DataObject::class, ['getIsWysiwygEnabled']);
        $attributeMock->method('getIsWysiwygEnabled')->willReturn($attributeEnabled);

        $this->element->setData('html_id', 'field_id');
        $this->element->setData('entity_attribute', $attributeMock);
        $this->element->setData('name', 'field_id');
        $this->element->setData('value', '');

        $this->assertSame($expected, $this->element->getIsWysiwygEnabled());
    }

    /**
     * Data provider for getIsWysiwygEnabled test scenarios.
     *
     * @return array
     */
    public static function getIsWysiwygEnabledDataProvider(): array
    {
        return [
            'all true => enabled' => [true,  true,  true,  true],
            'module disabled => disabled' => [false, true,  true,  false],
            'config disabled => disabled' => [true,  false, true,  false],
            'attribute disabled => disabled' => [true,  true,  false, false],
            'all false => disabled' => [false, false, false, false],
            'module enabled, others false => disabled' => [true,  false, false, false],
            'config enabled only => disabled' => [false, true,  false, false],
        ];
    }

    /**
     * Set a private/protected property on an object (searching up the inheritance chain).
     *
     * @param  object $object
     * @param  string $declaringClass
     * @param  string $property
     * @param  mixed  $value
     * @return void
     */
    private function setPrivateProperty(object $object, string $declaringClass, string $property, $value): void
    {
        $ref = new ReflectionClass($declaringClass);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }
}
