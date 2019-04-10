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
    protected function setUp()
    {
        $viewFileSystem = $this->createPartialMock(FileSystem::class, ['getLocaleFileName']);
        $viewFileSystem->expects($this->any())->method('getLocaleFileName')
            ->willReturn(dirname(__DIR__) . '/_files/Magento/Store/i18n/en_AU.csv');

        $objectManager = Bootstrap::getObjectManager();
        $objectManager->addSharedInstance($viewFileSystem, FileSystem::class);
        $translator = $objectManager->create(Translate::class);
        $objectManager->addSharedInstance($translator, Translate::class);
        $areaList = $objectManager->create(AreaList::class);
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
    protected function tearDown()
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
        CacheCleaner::cleanAll();
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
                ),
EOT
                ,
                <<<EOT
                title: 'Translated value for Magento_Store module in en_AU',
EOT
            ],
            [
                <<<EOT
                title: \$t(
                    'Original value for Magento_Store module'
                );
EOT
                ,
                <<<EOT
                title: 'Translated value for Magento_Store module in en_AU';
EOT
            ],
            [
                <<<EOT
                $.mage.__('The maximum you may purchase is %1.').replace('%1', params.maxAllowed);
EOT
                ,
                <<<EOT
                'The maximum you may purchase is %1.'.replace('%1', params.maxAllowed);
EOT
            ],
            [
                <<<EOT
                \$t("text double quote");
                \$t('text "some');
                \$t('Payment ' + this.getTitle() + ' can\'t be initialized')
                \$t('The maximum you may purchase is %1.').replace('%1', params.maxAllowed);
                \$t(
                    'Set unique country-state combinations within the same fixed product tax. ' +
                    'Verify the combinations and try again.'
                )
EOT
                ,
                <<<EOT
                'text double quote';
                'text "some';
                \$t('Payment ' + this.getTitle() + ' can\'t be initialized')
                'The maximum you may purchase is %1.'.replace('%1', params.maxAllowed);
                \$t(
                    'Set unique country-state combinations within the same fixed product tax. ' +
                    'Verify the combinations and try again.'
                )
EOT
            ],
        ];
    }
}
