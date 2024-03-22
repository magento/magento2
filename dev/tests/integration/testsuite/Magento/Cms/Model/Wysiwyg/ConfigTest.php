<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Wysiwyg;

use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleWysiwygConfig\Model\Config as TestModuleWysiwygConfig;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    private $model;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->create(\Magento\Cms\Model\Wysiwyg\Config::class);
        $this->design = $objectManager->get(DesignInterface::class);
    }

    /**
     * Tests that config returns valid config array in it
     *
     * @return void
     */
    public function testGetConfig()
    {
        $config = $this->model->getConfig();
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $config);
    }

    /**
     * Tests that config returns right urls going to the published library path
     *
     * @return void
     */
    public function testGetConfigCssUrls()
    {
        $config = $this->model->getConfig();
        $designTheme = $this->design->getConfigurationDesignTheme('adminhtml');
        $publicPathPattern = "http://localhost/static/%s/adminhtml/{$designTheme}/en_US/%s";
        $tinyMceConfig = $config->getData('tinymce');
        $contentCss = $tinyMceConfig['content_css'];
        if (is_array($contentCss)) {
            foreach ($contentCss as $url) {
                $this->assertStringMatchesFormat($publicPathPattern, $url);
            }
        } else {
            $this->assertStringMatchesFormat($publicPathPattern, $contentCss);
        }
    }

    /**
     * Test enabled module is able to modify WYSIWYG config
     *
     * @return void
     *
     * @magentoConfigFixture default/cms/wysiwyg/editor Magento_TestModuleWysiwygConfig/wysiwyg/tinymceTestAdapter
     */
    public function testTestModuleEnabledModuleIsAbleToModifyConfig()
    {
        $objectManager = Bootstrap::getObjectManager();
        $compositeConfigProvider = $objectManager->create(\Magento\Cms\Model\Wysiwyg\CompositeConfigProvider::class);
        $model = $objectManager->create(
            \Magento\Cms\Model\Wysiwyg\Config::class,
            ['configProvider' => $compositeConfigProvider]
        );
        $config = $model->getConfig();
        // @phpstan-ignore-next-line
        $this->assertEquals(TestModuleWysiwygConfig::CONFIG_HEIGHT, $config['height']);
        // @phpstan-ignore-next-line
        $this->assertEquals(TestModuleWysiwygConfig::CONFIG_CONTENT_CSS, $config['content_css']);
        $this->assertArrayHasKey('tinymce', $config);
        $this->assertArrayHasKey('toolbar', $config['tinymce']);
        $this->assertStringNotContainsString(
            'charmap',
            $config['tinymce']['toolbar'],
            'Failed to address that the custom test module removes "charmap" button from the toolbar'
        );
    }
}
