<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Translate;

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
        $translatorMock = $this->getMockBuilder('stdClass')->setMethods(array('translate'))->getMock();
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
            array('translator' => array($translatorMock, 'translate'))
        );

        $this->assertEquals($translatedStr, $translator->{$method}($strToTranslate));
    }

    /**
     * @return array
     */
    public function translateDataProvider()
    {
        return array(array('translate', 'Translate me!', 'Translated string'));
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
     * Test __() with more than one parameter passed
     */
    public function testUnderscoresTranslation()
    {
        $this->markTestIncomplete('MAGETWO-1012: i18n Improvements - Localization/Translations');
    }
}
