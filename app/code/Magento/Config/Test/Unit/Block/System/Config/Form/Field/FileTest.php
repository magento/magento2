<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\File;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Framework\Data\Form\Field\File
 */
class FileTest extends TestCase
{
    /**
     * @var File
     */
    protected $file;

    /**
     * @var array
     */
    protected $testData;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->testData = [
            'before_element_html' => 'test_before_element_html',
            'html_id' => 'test_id',
            'name' => 'test_name',
            'value' => 'test_value',
            'title' => 'test_title',
            'disabled' => true,
            'after_element_js'    => 'test_after_element_js',
            'after_element_html'  => 'test_after_element_html',
            'html_id_prefix'      => 'test_id_prefix_',
            'html_id_suffix'      => '_test_id_suffix',
        ];

        $this->file = $objectManager->getObject(
            File::class,
            [
                '_escaper' => $objectManager->getObject(Escaper::class),
                'data' => $this->testData,

            ]
        );

        $formMock = new DataObject();
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
        $this->assertContains(
            '<input type="checkbox" name="' . $this->testData['name'] . '[delete]"',
            $html
        );
    }
}
