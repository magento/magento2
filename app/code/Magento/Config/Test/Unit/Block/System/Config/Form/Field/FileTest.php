<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Field\File
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Form\Field\File
     */
    protected $file;

    protected function setUp()
    {
        $factoryMock = $this->getMockBuilder('Magento\Framework\Data\Form\Element\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $collectionFactoryMock = $this->getMockBuilder('Magento\Framework\Data\Form\Element\CollectionFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $escaperMock = $this->getMockBuilder('Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->file = $objectManager->getObject(
            'Magento\Config\Block\System\Config\Form\Field\File',
            [
                'factoryElement'    => $factoryMock,
                'factoryCollection' => $collectionFactoryMock,
                'escaper'           => $escaperMock,
                'data'              =>
                [
                    'before_element_html' => 'test_before_element_html',
                    'html_id'             => 'test_id',
                    'name'                => 'test_name',
                    'value'               => 'test_value',
                    'title'               => 'test_title',
                    'disabled'            => true,
                    'after_element_js'    => 'test_after_element_js',
                    'after_element_html'  => 'test_after_element_html',
                ]
            ]
        );

        $formMock = new \Magento\Framework\Object();
        $formMock->getHtmlIdPrefix('id_prefix');
        $formMock->getHtmlIdPrefix('id_suffix');
        $this->file->setForm($formMock);
    }

    public function testGetElementHtml()
    {
        $html = $this->file->getElementHtml();

        $this->assertContains('<label class="addbefore" for="test_id"', $html);
        $this->assertContains('test_before_element_html', $html);
        $this->assertContains('<input id="test_id"', $html);
        $this->assertContains('name="test_name"', $html);
        $this->assertContains('value="test_value"', $html);
        $this->assertContains('disabled="disabled"', $html);
        $this->assertContains('type="file"', $html);
        $this->assertContains('test_after_element_js', $html);
        $this->assertContains('<label class="addafter" for="test_id"', $html);
        $this->assertContains('test_after_element_html', $html);
        $this->assertContains('<input type="checkbox" name="test_name[delete]"', $html);
    }
}
