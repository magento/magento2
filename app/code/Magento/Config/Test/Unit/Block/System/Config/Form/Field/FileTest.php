<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\File;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Framework\Data\Form\Field\File
 */
class FileTest extends TestCase
{
    /**
     * XSS value
     */
    private const XSS_FILE_NAME_TEST = '<img src=x onerror=alert(1)>.crt';

    /**
     * Input name
     */
    private const INPUT_NAME_TEST = 'test_name';

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Factory|MockObject
     */
    private $factoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $factoryCollectionMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var array
     */
    protected array $testData = [
        'before_element_html' => 'test_before_element_html',
        'html_id' => 'test_id',
        'name' => 'test_name',
        'value' => 'test_value',
        'title' => 'test_title',
        'disabled' => true,
        'after_element_js' => 'test_after_element_js',
        'after_element_html' => 'test_after_element_html',
        'html_id_prefix' => 'test_id_prefix_',
        'html_id_suffix' => '_test_id_suffix',
    ];

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->factoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->factoryCollectionMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->file = $objectManager->getObject(
            File::class,
            [
                'factoryElement' => $this->factoryMock,
                'factoryCollection' => $this->factoryCollectionMock,
                '_escaper' => $this->escaperMock,
                'data' => $this->testData,
            ]
        );

        $formMock = new DataObject();
        $formMock->setHtmlIdPrefix($this->testData['html_id_prefix']);
        $formMock->setHtmlIdSuffix($this->testData['html_id_suffix']);
        $this->file->setForm($formMock);
    }

    public function testGetElementHtml(): void
    {
        $expectedHtmlId = $this->testData['html_id_prefix']
            . $this->testData['html_id']
            . $this->testData['html_id_suffix'];
        $escapeValue = $this->testData['value'];
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnMap(
            [
                [$expectedHtmlId, null, $expectedHtmlId],
                [self::XSS_FILE_NAME_TEST, null, self::XSS_FILE_NAME_TEST],
                [self::INPUT_NAME_TEST, null, self::INPUT_NAME_TEST],
                [$escapeValue, null, $escapeValue],
            ]
        );

        $html = $this->file->getElementHtml();

        $this->assertStringContainsString('<label class="addbefore" for="' . $expectedHtmlId . '"', $html);
        $this->assertStringContainsString($this->testData['before_element_html'], $html);
        $this->assertStringContainsString('<input id="' . $expectedHtmlId . '"', $html);
        $this->assertStringContainsString('name="' . $this->testData['name'] . '"', $html);
        $this->assertStringContainsString('value="' . $this->testData['value'] . '"', $html);
        $this->assertStringContainsString('disabled="disabled"', $html);
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString($this->testData['after_element_js'], $html);
        $this->assertStringContainsString('<label class="addafter" for="' . $expectedHtmlId . '"', $html);
        $this->assertStringContainsString($this->testData['after_element_html'], $html);
        $this->assertStringContainsString(
            '<input type="checkbox" name="' . $this->testData['name'] . '[delete]"',
            $html
        );
    }
}
