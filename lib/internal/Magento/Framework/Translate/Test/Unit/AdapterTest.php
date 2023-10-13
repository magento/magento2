<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Translate\Test\Unit;

use Laminas\I18n\Translator\Translator;
use Magento\Framework\Translate\Adapter;
use PHPUnit\Framework\TestCase;

class AdapterTest extends TestCase
{
    /**
     * Check that translate calls are passed to given translator
     *
     * @param string $strToTranslate
     * @param string $translatedStr
     * @dataProvider translateDataProvider
     */
    public function testTranslate($strToTranslate, $translatedStr)
    {
        $translatorMock = $this->getMockBuilder(Translator::class)
            ->setMethods(['translate'])->getMock();
        $translatorMock->expects(
            $this->once()
        )->method(
            'translate'
        )->with(
            $strToTranslate
        )->willReturn(
            $translatedStr
        );
        $translator = new Adapter();
        $translator->setTranslator($translatorMock);

        $this->assertEquals($translatedStr, $translator->translate($strToTranslate));
    }

    /**
     * @return array
     */
    public function translateDataProvider()
    {
        return [['Translate me!', 'Translated string']];
    }

    /**
     * Test that string is returned in any case
     */
    public function testTranslateNoProxy()
    {
        $translator = new Adapter();
        $this->assertEquals('test string', $translator->translate('test string'));
    }

    /**
     * Test translation with more than one parameter passed
     */
    public function testUnderscoresTranslation()
    {
        $this->markTestSkipped('MAGETWO-1012: i18n Improvements - Localization/Translations');
    }
}
