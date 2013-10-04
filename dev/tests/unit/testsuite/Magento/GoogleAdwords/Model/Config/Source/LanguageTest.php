<?php

namespace Magento\GoogleAdwords\Model\Config\Source;

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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class LanguageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_localeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_localeModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_uppercaseFilterMock;

    /**
     * @var \Magento\GoogleAdwords\Model\Config\Source\Language
     */
    protected $_model;

    protected function setUp()
    {
        $this->_helperMock = $this->getMock('Magento\GoogleAdwords\Helper\Data', array(), array(), '', false);
        $this->_localeMock = $this->getMock('Zend_Locale', array(), array(), '', false);
        $this->_localeModelMock = $this->getMock('Magento\Core\Model\LocaleInterface', array(), array(), '', false);
        $this->_localeModelMock->expects($this->once())->method('getLocale')
            ->will($this->returnValue($this->_localeMock));
        $this->_uppercaseFilterMock = $this->getMock('Magento\GoogleAdwords\Model\Filter\UppercaseTitle', array(),
            array(), '', false);

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject('Magento\GoogleAdwords\Model\Config\Source\Language', array(
            'locale' => $this->_localeModelMock,
            'helper' => $this->_helperMock,
            'uppercaseFilter' => $this->_uppercaseFilterMock,
        ));
    }

    public function testToOptionArray()
    {
        $languageCodes = array('languageCode1', 'languageCode2');
        $langToLocalesMap = array('languageCode1' => 'localeCode1', 'languageCode2' => 'localeCode2');
        $expectedLanguages = array(
            array(
                'value' => 'languageCode1',
                'label' => 'TranslationForSpecifiedLanguage1 / translationForDefaultLanguage1 (languageCode1)',
            ),
            array(
                'value' => 'languageCode2',
                'label' => 'TranslationForSpecifiedLanguage2 / translationForDefaultLanguage2 (languageCode2)',
            ),
        );

        $this->_helperMock->expects($this->once())->method('getLanguageCodes')
            ->will($this->returnValue($languageCodes));
        $this->_helperMock->expects($this->atLeastOnce())->method('convertLanguageCodeToLocaleCode')
            ->will($this->returnCallback(
                function ($languageCode) use ($langToLocalesMap) {
                    return $langToLocalesMap[$languageCode];
                }
            ));

        $localeMock = $this->_localeMock;
        $localeMock::staticExpects($this->at(0))->method('getTranslation')
            ->with('localeCode1', 'language', 'languageCode1')
            ->will($this->returnValue('translationForSpecifiedLanguage1'));

        $localeMock::staticExpects($this->at(1))->method('getTranslation')
            ->with('localeCode1', 'language')
            ->will($this->returnValue('translationForDefaultLanguage1'));

        $localeMock::staticExpects($this->at(2))->method('getTranslation')
            ->with('localeCode2', 'language', 'languageCode2')
            ->will($this->returnValue('translationForSpecifiedLanguage2'));

        $localeMock::staticExpects($this->at(3))->method('getTranslation')
            ->with('localeCode2', 'language')
            ->will($this->returnValue('translationForDefaultLanguage2'));

        $this->_uppercaseFilterMock->expects($this->at(0))->method('filter')
            ->with('translationForSpecifiedLanguage1')
            ->will($this->returnValue('TranslationForSpecifiedLanguage1'));

        $this->_uppercaseFilterMock->expects($this->at(1))->method('filter')
            ->with('translationForSpecifiedLanguage2')
            ->will($this->returnValue('TranslationForSpecifiedLanguage2'));

        $this->assertEquals($expectedLanguages, $this->_model->toOptionArray());
    }
}
