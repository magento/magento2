<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter;

use Pelago\Emogrifier\CssInliner as EmogrifierCssInliner;

class CssInlinerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Css\PreProcessor\Adapter\CssInliner
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->model = $this->objectManager->create(\Magento\Framework\Css\PreProcessor\Adapter\CssInliner::class);
    }

    /**
     * @param string $htmlFilePath
     * @param string $cssFilePath
     * @param string $cssExpected
     * @dataProvider getFilesDataProvider
     */
    public function testGetFiles($htmlFilePath, $cssFilePath, $cssExpected)
    {
        $html = file_get_contents($htmlFilePath);
        $css = file_get_contents($cssFilePath);
        $this->model->setHtml($html);
        $this->model->setCss($css);
        $result = $this->model->process();
        $this->assertStringContainsString($cssExpected, $result);
    }

    /**
     * @return array
     */
    public function getFilesDataProvider()
    {
        $fixtureDir = dirname(dirname(__DIR__));
        return [
            'noSpacesCss'=>[
                'resultHtml' => $fixtureDir . "/_files/css/test-input.html",
                'cssWithoutSpaces' => $fixtureDir . "/_files/css/test-css-no-spaces.css",
                'vertical-align: top; padding: 10px 10px 10px 0; width: 50%;'
            ],
            'withSpacesCss'=>[
                'resultHtml' => $fixtureDir . "/_files/css/test-input.html",
                'cssWithSpaces' => $fixtureDir . "/_files/css/test-css-with-spaces.css",
                'vertical-align: top; padding: 10px 10px 10px 0; width: 50%;'
            ],
        ];
    }

    /**
     * @param string $htmlFilePath
     * @param string $cssFilePath
     * @param string $cssExpected
     * @dataProvider getFilesDataProviderEmogrifier
     */
    public function testGetFilesEmogrifier($htmlFilePath, $cssFilePath, $cssExpected)
    {
        $html = file_get_contents($htmlFilePath);
        $css = file_get_contents($cssFilePath);
        $result = EmogrifierCssInliner::fromHtml($html)->inlineCss($css)->render();

        /**
         * This test was implemented for the issue which existed in the older version of Emogrifier.
         * Test was updated, as the library got updated as well.
         */
        $this->assertStringContainsString($cssExpected, $result);
    }

    /**
     * @return array
     */
    public function getFilesDataProviderEmogrifier()
    {
        $fixtureDir = dirname(dirname(__DIR__));
        return [
            'noSpacesCss'=>[
                'resultHtml' => $fixtureDir . "/_files/css/test-input.html",
                'cssWithoutSpaces' => $fixtureDir . "/_files/css/test-css-no-spaces.css",
                'vertical-align: top; padding: 10px 10px 10px 0; width: 50%;'
            ]
        ];
    }
}
