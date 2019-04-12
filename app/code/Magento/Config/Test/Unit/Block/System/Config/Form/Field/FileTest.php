<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Field\File
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field\File
     */
    protected $file;

    /**
     * @var array
     */
    protected $testData;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->escaperMock->method('escapeHtml')->willReturnArgument(0);

        $this->testData = [
            'before_element_html' => 'test_before_element_html',
            'html_id'             => 'test_id',
            'name'                => 'test_name',
            'value'               => 'test_value',
            'title'               => 'test_title',
            'disabled'            => true,
            'after_element_js'    => 'test_after_element_js',
            'after_element_html'  => 'test_after_element_html',
            'html_id_prefix'      => 'test_id_prefix_',
            'html_id_suffix'      => '_test_id_suffix',
        ];

        $this->file = $objectManager->getObject(
            \Magento\Config\Block\System\Config\Form\Field\File::class,
            [
                'escaper' => $this->escaperMock,
                'data' => $this->testData
            ]
        );

        $formMock = new \Magento\Framework\DataObject();
        $formMock->setHtmlIdPrefix($this->testData['html_id_prefix']);
        $formMock->setHtmlIdSuffix($this->testData['html_id_suffix']);
        $this->file->setForm($formMock);
    }

    public function testGetElementHtml()
    {
        $html = $this->file->getElementHtml();

        $expectedHtmlId = $this->testData['html_id_prefix']
            . $this->testData['html_id']
            . $this->testData['html_id_suffix'];

        $this->assertContains('<label class="addbefore" for="' . $expectedHtmlId . '"', $html);
        $this->assertContains($this->testData['before_element_html'], $html);
        $this->assertContains('<input id="' . $expectedHtmlId . '"', $html);
        $this->assertContains('name="' . $this->testData['name'] . '"', $html);
        $this->assertContains('value="' . $this->testData['value'] . '"', $html);
        $this->assertContains('disabled="disabled"', $html);
        $this->assertContains('type="file"', $html);
        $this->assertContains($this->testData['after_element_js'], $html);
        $this->assertContains('<label class="addafter" for="' . $expectedHtmlId . '"', $html);
        $this->assertContains($this->testData['after_element_html'], $html);
        $this->assertContains('<input type="checkbox" name="' . $this->testData['name'] . '[delete]"', $html);
    }
}
