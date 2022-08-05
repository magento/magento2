<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model\Js;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\View\FileSystem;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\Framework\Translate;
use Magento\Framework\App\AreaList;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;

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
     * @var RendererInterface
     */
    private $origRenderer;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        $viewFileSystem = $this->createPartialMock(FileSystem::class, ['getLocaleFileName']);
        $viewFileSystem->expects($this->any())->method('getLocaleFileName')
            ->willReturn(
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                dirname(__DIR__) . '/_files/Magento/Store/i18n/en_AU.csv'
            );

        $objectManager = Bootstrap::getObjectManager();
        $objectManager->addSharedInstance($viewFileSystem, FileSystem::class);
        $translator = $objectManager->create(Translate::class);
        $objectManager->addSharedInstance($translator, Translate::class);
        $areaList = $this->getMockBuilder(AreaList::class)->disableOriginalConstructor()->getMock();
        $this->origRenderer = Phrase::getRenderer();
        Phrase::setRenderer(
            $objectManager->get(RendererInterface::class)
        );

        $this->model = $objectManager->create(
            PreProcessor::class,
            [
                'translate' => $translator,
                'areaList' => $areaList
            ]
        );

        $translator->setLocale('en_AU');
        $translator->loadData();
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        Phrase::setRenderer($this->origRenderer);
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
        $this->assertEquals($translation, $this->model->translate($content));
    }

    /**
     * Data provider for translation.
     *
     * @return array
     */
    public function contentForTranslateDataProvider()
    {
        return [
            'i18n_js_file_error' => [
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
            'checkTranslationWithWhiteSpaces' => [
                <<<i18n
                title: $.mage.__(
                    'Original value for Magento_Store module'
                ),
                title: \$t(
                    'Original value for Magento_Store module'
                );
                title: jQuery.mage.__(
                    'Original value for Magento_Store module'
                );
i18n
                ,
                <<<i18n
                title: 'Translated value for Magento_Store module in en_AU',
                title: 'Translated value for Magento_Store module in en_AU';
                title: 'Translated value for Magento_Store module in en_AU';
i18n
            ],
            'checkTranslationWithReplace' => [
                <<<i18n
                $.mage.__('The maximum you may purchase is %1.').replace('%1', params.maxAllowed);
                \$t('The maximum you may purchase is %1.').replace('%1', params.maxAllowed);
i18n
                ,
                <<<i18n
                'The maximum you may purchase is %1.'.replace('%1', params.maxAllowed);
                'The maximum you may purchase is %1.'.replace('%1', params.maxAllowed);
i18n
            ],
            'checkAvoidingMatchingWithJsInString' => [
                <<<i18n
                \$t('Payment ' + this.getTitle() + ' can\'t be initialized')
                \$t(
                    'Set unique country-state combinations within the same fixed product tax. ' +
                    'Verify the combinations and try again.'
                )
i18n
                ,
                <<<i18n
                \$t('Payment ' + this.getTitle() + ' can\'t be initialized')
                \$t(
                    'Set unique country-state combinations within the same fixed product tax. ' +
                    'Verify the combinations and try again.'
                )
i18n
            ],
            'checkAvoidMatchingPhtml' => [
                <<<i18n
                globalMessageList.addErrorMessage({
                        message: \$t(<?= /* @noEscape */ json_encode(\$params['error_msg'])?>)
                    });
i18n
                ,
                <<<i18n
                globalMessageList.addErrorMessage({
                        message: \$t(<?= /* @noEscape */ json_encode(\$params['error_msg'])?>)
                    });
i18n
            ]
        ];
    }
}
