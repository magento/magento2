<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Test\Unit;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check that translate calls are passed to given translator
     *
     * @param string $method
     * @param string $strToTranslate
     * @param string $translatedStr
     * @dataProvider translateDataProvider
     */
    public function testTranslate($method, $strToTranslate, $translatedStr)
    {
        $translatorMock = $this->getMockBuilder('stdClass')->setMethods(['translate'])->getMock();
        $translatorMock->expects(
            $this->once()
        )->method(
            'translate'
        )->with(
            $strToTranslate
        )->will(
            $this->returnValue($translatedStr)
        );
        $translator = new \Magento\Framework\Translate\Adapter(
            ['translator' => [$translatorMock, 'translate']]
        );

        $this->assertEquals($translatedStr, $translator->{$method}($strToTranslate));
    }

    /**
     * @return array
     */
    public function translateDataProvider()
    {
        return [['translate', 'Translate me!', 'Translated string']];
    }

    /**
     * Test that string is returned in any case
     */
    public function testTranslateNoProxy()
    {
        $translator = new \Magento\Framework\Translate\Adapter();
        $this->assertEquals('test string', $translator->translate('test string'));
    }

    /**
     * Test translation with more than one parameter passed
     */
    public function testUnderscoresTranslation()
    {
        $this->markTestIncomplete('MAGETWO-1012: i18n Improvements - Localization/Translations');
    }
}
