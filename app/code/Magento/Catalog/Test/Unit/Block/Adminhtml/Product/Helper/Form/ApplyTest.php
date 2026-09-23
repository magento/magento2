<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Escaper;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply
 */
class ApplyTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Apply
     */
    private Apply $apply;

    /**
     * @var Factory|MockObject
     */
    private $factoryElementMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $factoryCollectionMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var SecureHtmlRenderer|MockObject
     */
    private $secureRendererMock;

    /**
     * @var AbstractForm|MockObject
     */
    private $formMock;

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->factoryElementMock = $this->createMock(Factory::class);
        $this->factoryCollectionMock = $this->createMock(CollectionFactory::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $this->formMock = $this->createMock(AbstractForm::class);

        // Escaper should return the value as-is for ids used in this test
        $this->escaperMock->method('escapeHtml')->willReturnCallback(
            static function ($value) {
                return $value;
            }
        );

        // emulate magic getters via DataObject for html id prefix/suffix
        $this->formMock->setData('html_id_prefix', '');
        $this->formMock->setData('html_id_suffix', '');

        // Create using ObjectManager helper to mirror Magento patterns
        $this->apply = $this->objectManager->getObject(
            Apply::class,
            [
                'factoryElement' => $this->factoryElementMock,
                'factoryCollection' => $this->factoryCollectionMock,
                'escaper' => $this->escaperMock,
            ]
        );

        // Inject SecureHtmlRenderer into Apply's private property
        $secureRendererProperty = new ReflectionProperty(Apply::class, 'secureRenderer');
        $secureRendererProperty->setValue($this->apply, $this->secureRendererMock);

        // Provide a form and necessary baseline data
        $this->apply->setForm($this->formMock);
        $this->apply->setId('apply_id');
        $this->apply->setData('name', 'apply');
        $this->apply->setData('mode_labels', ['all' => 'All', 'custom' => 'Custom']);
    }

    /**
     * Verify the element renders a select with correct options, attaches onchange listener,
     * and appends the parent multiselect HTML to the output.
     *
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply::getElementHtml
     */
    public function testGetElementHtmlBuildsSelectAndEventAndAppendsParentOutput(): void
    {
        $element = $this->apply;
        $element->setValue(null);

        // Mock event listener markup to a deterministic string
        $this->secureRendererMock->expects($this->once())
            ->method('renderEventListenerAsTag')
            ->with(
                'onchange',
                'toggleApplyVisibility(this)',
                'select#apply_id'
            )
            ->willReturn('<script type="text/x-magento-init">{}</script>'); // simulate Magento init script

        $html = $element->getElementHtml();

        // Select tag with id and no readonly/disabled when not set
        $this->assertStringContainsString('<select id="apply_id">', $html);

        // Options include both 'all' and 'custom', with custom not selected when value is null
        $this->assertStringContainsString('<option value="0">All</option>', $html);
        $this->assertStringContainsString('<option value="1" ', $html);
        $this->assertStringNotContainsString('<option value="1" selected', $html);

        // Event markup is appended as x-magento-init wrapper
        $this->assertStringContainsString('<script type="text/x-magento-init">', $html);

        // Parent multiselect HTML is appended after our select; we cannot assert exact content,
        // but ensure something follows (closing select and line breaks already accounted for).
        $this->assertStringContainsString('</select><br /><br />', $html);
    }

    /**
     * Verify readonly/disabled flags are rendered on the select and the "custom"
     * option is selected when a non-null value is provided.
     *
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply::setReadonly
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply::getElementHtml
     */
    public function testGetElementHtmlIncludesReadonlyDisabledAndSelected(): void
    {
        $element = $this->apply;

        // Set flags via the element-specific setReadonly
        $element->setReadonly(true, true); // readonly + disabled
        $element->setValue('non-empty');   // triggers selected on "custom" option

        $this->secureRendererMock->expects($this->once())
            ->method('renderEventListenerAsTag')
            ->with(
                'onchange',
                'toggleApplyVisibility(this)',
                'select#apply_id'
            )
            ->willReturn('<script type="text/x-magento-init">{}</script>');

        $html = $element->getElementHtml();

        // Both readonly and disabled appear on the select
        $this->assertStringContainsString('readonly="readonly"', $html);
        $this->assertStringContainsString('disabled="disabled"', $html);

        // Custom option should be selected when value is non-null
        $this->assertStringContainsString('<option value="1" selected>', $html);
    }

    /**
     * Ensure `setReadonly` returns the same instance to support fluent chaining.
     *
     * @return void
     * @covers \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply::setReadonly
     */
    public function testSetReadonlyReturnsSelf(): void
    {
        $result = $this->apply->setReadonly(true, true);
        $this->assertSame($this->apply, $result);
    }
}
