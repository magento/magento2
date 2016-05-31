<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Wysiwyg;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_model;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cms\Model\Wysiwyg\Config'
        );
    }

    /**
     * Tests that config returns valid config array in it
     */
    public function testGetConfig()
    {
        $config = $this->_model->getConfig();
        $this->assertInstanceOf('Magento\Framework\DataObject', $config);
    }

    /**
     * Tests that config returns right urls going to the published library path
     */
    public function testGetConfigCssUrls()
    {
        $config = $this->_model->getConfig();
        $publicPathPattern = 'http://localhost/pub/static/adminhtml/Magento/backend/en_US/mage/%s';
        $this->assertStringMatchesFormat($publicPathPattern, $config->getPopupCss());
        $this->assertStringMatchesFormat($publicPathPattern, $config->getContentCss());
    }

    /**
     * Tests that config doesn't process incoming already prepared data
     *
     * @dataProvider getConfigNoProcessingDataProvider
     */
    public function testGetConfigNoProcessing($original)
    {
        $config = $this->_model->getConfig($original);
        $actual = $config->getData();
        foreach (array_keys($actual) as $key) {
            if (!isset($original[$key])) {
                unset($actual[$key]);
            }
        }
        $this->assertEquals($original, $actual);
    }

    /**
     * @return array
     */
    public function getConfigNoProcessingDataProvider()
    {
        return [
            [
                [
                    'files_browser_window_url' => 'http://example.com/111/',
                    'directives_url' => 'http://example.com/222/',
                    'popup_css' => 'http://example.com/333/popup.css',
                    'content_css' => 'http://example.com/444/content.css',
                    'directives_url_quoted' => 'http://example.com/555/',
                ],
            ],
            [
                [
                    'files_browser_window_url' => '/111/',
                    'directives_url' => '/222/',
                    'popup_css' => '/333/popup.css',
                    'content_css' => '/444/content.css',
                    'directives_url_quoted' => '/555/',
                ]
            ],
            [
                [
                    'files_browser_window_url' => '111/',
                    'directives_url' => '222/',
                    'popup_css' => '333/popup.css',
                    'content_css' => '444/content.css',
                    'directives_url_quoted' => '555/',
                ]
            ]
        ];
    }
}
