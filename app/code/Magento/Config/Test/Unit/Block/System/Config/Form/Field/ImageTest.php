<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Field\Image
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Url|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field\Image
     */
    protected $image;

    /**
     * @var array
     */
    protected $testData;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('some-rando-string');
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $listener, string $selector): string {
                    return "<script>document.querySelector('{$selector}').{$event} = () => { {$listener} };</script>";
                }
            );
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\Url::class);
        $this->image = $objectManager->getObject(
            \Magento\Config\Block\System\Config\Form\Field\Image::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                '_escaper' => $objectManager->getObject(\Magento\Framework\Escaper::class),
                'random' => $randomMock,
                'secureRenderer' => $secureRendererMock
            ]
        );

        $this->testData = [
            'html_id_prefix' => 'test_id_prefix_',
            'html_id'        => 'test_id',
            'html_id_suffix' => '_test_id_suffix',
            'path'           => 'catalog/product/placeholder',
            'value'          => 'test_value',
        ];

        $formMock = new \Magento\Framework\DataObject();
        $formMock->setHtmlIdPrefix($this->testData['html_id_prefix']);
        $formMock->setHtmlIdSuffix($this->testData['html_id_suffix']);
        $this->image->setForm($formMock);
    }

    /**
     * @covers \Magento\Config\Block\System\Config\Form\Field\Image::_getUrl
     */
    public function testGetElementHtmlWithValue()
    {
        $type = 'media';
        $url = 'http://test.example.com/media/';
        $this->urlBuilderMock->expects($this->once())->method('getBaseUrl')
            ->with(['_type' => $type])->will($this->returnValue($url));

        $this->image->setValue($this->testData['value']);
        $this->image->setHtmlId($this->testData['html_id']);
        $this->image->setFieldConfig(
            [
                'id' => 'placeholder',
                'type' => 'image',
                'sortOrder' => '1',
                'showInDefault' => '1',
                'showInWebsite' => '1',
                'showInStore' => '1',
                'label' => null,
                'backend_model' => \Magento\Config\Model\Config\Backend\Image::class,
                'upload_dir' => [
                    'config' => 'system/filesystem/media',
                    'scope_info' => '1',
                    'value' => $this->testData['path'],
                ],
                'base_url' => [
                    'type' => $type,
                    'scope_info' => '1',
                    'value' => $this->testData['path'],
                ],
                '_elementType' => 'field',
                'path' => 'catalog/placeholder',
            ]
        );

        $expectedHtmlId = $this->testData['html_id_prefix']
            . $this->testData['html_id']
            . $this->testData['html_id_suffix'];

        $html = $this->image->getElementHtml();
        $this->assertContains('class="input-file"', $html);
        $this->assertContains('<input', $html);
        $this->assertContains('type="file"', $html);
        $this->assertContains('value="test_value"', $html);
        $this->assertContains(
            '<a previewlinkid="linkIdsome-rando-string" href="'
            . $url
            . $this->testData['path']
            . '/'
            . $this->testData['value']
            . '"',
            $html
        );
        $this->assertContains("imagePreview('{$expectedHtmlId}_image');\nreturn false;", $html);
        $this->assertContains('<input type="checkbox"', $html);
    }
}
