<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Test for the widget.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EditablemultiselectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Editablemultiselect
     */
    protected $_model;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $testHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('some-rando-string');
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $listener, string $selector): string {
                    return "<script>document.querySelector('{$selector}').{$event} = () => { {$listener} };</script>";
                }
            );
        $secureRendererMock->method('renderTag')
            ->willReturnCallback(
                function (string $tag, array $attrs, ?string $content): string {
                    $attrs = new DataObject($attrs);

                    return "<$tag {$attrs->serialize()}>$content</$tag>";
                }
            );
        $this->_model = $testHelper->getObject(
            \Magento\Framework\Data\Form\Element\Editablemultiselect::class,
            [
                'random' => $randomMock,
                'secureRenderer' => $secureRendererMock
            ]
        );
        $values = [
            ['value' => 1, 'label' => 'Value1'],
            ['value' => 2, 'label' => 'Value2'],
            ['value' => 3, 'label' => 'Value3'],
        ];
        $value = [1, 3];
        $this->_model->setForm(new DataObject());
        $this->_model->setData(['values' => $values, 'value' => $value]);
    }

    public function testGetElementHtmlRendersDataAttributesWhenDisabled()
    {
        $this->_model->setDisabled(true);
        $elementHtml = $this->_model->getElementHtml();
        $this->assertContains('disabled="disabled"', $elementHtml);
        $this->assertContains('data-is-removable="no"', $elementHtml);
        $this->assertContains('data-is-editable="no"', $elementHtml);
    }

    public function testGetElementHtmlRendersRelatedJsClassInitialization()
    {
        $this->_model->setElementJsClass('CustomSelect');
        $elementHtml = $this->_model->getElementHtml();
        $this->assertContains('ElementControl = new CustomSelect(', $elementHtml);
        $this->assertContains('ElementControl.init();', $elementHtml);
    }
}
