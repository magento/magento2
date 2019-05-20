<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model\Js;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\FileSystem;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\Framework\Translate;

/**
 * Class for testing translation.
 */
class PreProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PreProcessor
     */
    private $model;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $viewFileSystem = $this->createPartialMock(FileSystem::class, ['getLocaleFileName']);
        $viewFileSystem->expects($this->any())->method('getLocaleFileName')
            ->willReturn(dirname(__DIR__) . '/_files/Magento/Store/i18n/en_AU.csv');

        $objectManager = Bootstrap::getObjectManager();
        $objectManager->addSharedInstance($viewFileSystem, FileSystem::class);
        $translator = $objectManager->create(Translate::class);
        $objectManager->addSharedInstance($translator, Translate::class);

        $config = $this->createPartialMock(Config::class, ['isEmbeddedStrategy', 'getPatterns']);
        $config->expects($this->atLeastOnce())->method('isEmbeddedStrategy')->willReturn(true);
        $config->expects($this->atLeastOnce())->method('getPatterns')->willReturn(
            [
                "~(?:\\$|jQuery)\\.mage\\.__\\((?s)[^'\\\")]*?(['\\\"])(?P<translate>.+?)(?<!\\\\)\\1(?s).*?\\)~",
                "~\\\$t\\((?s)[^'\\\")]*?([\\\"'])(?P<translate>.+?)\\1(?s).*?\\)~"
            ]
        );
        $this->model = $objectManager->create(
            PreProcessor::class,
            [
                'config' => $config
            ]
        );
    }

    /**
     * Test for backend translation strategy.
     *
     * @param string $content
     * @param string $translation
     * @return void
     * @dataProvider contentForTranslateDataProvider
     */
    public function testProcess(string $content, string $translation)
    {
        CacheCleaner::cleanAll();
        $locale = $this->getMockBuilder(
            LocalInterface::class
        )->getMockForAbstractClass();
        $context = $this->createPartialMock(
            FallbackContext::class,
            ['getAreaCode', 'getLocale']
        );

        $context->expects($this->atLeastOnce())->method('getAreaCode')->willReturn('base');
        $context->expects($this->atLeastOnce())->method('getLocale')->willReturn('en_AU');
        $locale->expects($this->atLeastOnce())->method('getContext')->willReturn($context);

        $chain =  Bootstrap::getObjectManager()->create(
            Chain::class,
            ['asset' => $locale, 'origContent' => '', 'origContentType' => '', 'origAssetPath' => '']
        );
        $chain->setContent($content);
        $this->model->process($chain);
        $this->assertEquals($translation, $chain->getContent());
    }

    /**
     * Data provider for translation.
     *
     * @return array
     */
    public function contentForTranslateDataProvider()
    {
        return [
            [
                'setTranslateProp = function (el, original) {
            var location = $(el).prop(\'tagName\').toLowerCase(),
                translated = $.mage.__(original),
                translationData = {
                    shown: translated,
                    translated: translated,
                    original: original
                },
                translateAttr = composeTranslateAttr(translationData, location);

            $(el).attr(\'data-translate\', translateAttr);

            setText(el, translationData.shown);
        },',
                'setTranslateProp = function (el, original) {
            var location = $(el).prop(\'tagName\').toLowerCase(),
                translated = $.mage.__(original),
                translationData = {
                    shown: translated,
                    translated: translated,
                    original: original
                },
                translateAttr = composeTranslateAttr(translationData, location);

            $(el).attr(\'data-translate\', translateAttr);

            setText(el, translationData.shown);
        },'
            ],
            [
                <<<EOT
                title: $.mage.__(
                    'Original value for Magento_Store module'
                )
EOT
                ,
                <<<EOT
                title: 'Translated value for Magento_Store module in en_AU'
EOT
            ],
            [
                <<<EOT
                title: \$t(
                    'Original value for Magento_Store module'
                )
EOT
                ,
                <<<EOT
                title: 'Translated value for Magento_Store module in en_AU'
EOT
            ],
        ];
    }
}
