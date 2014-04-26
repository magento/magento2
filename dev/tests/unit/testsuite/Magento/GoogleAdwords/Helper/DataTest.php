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
namespace Magento\GoogleAdwords\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configNodeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_registryMock;

    /**
     * @var \Magento\GoogleAdwords\Helper\Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $context = $this->getMock('Magento\Framework\App\Helper\Context', array(), array(), '', false);
        $this->_helper = $objectManager->getObject(
            'Magento\GoogleAdwords\Helper\Data',
            array(
                'config' => $this->_configMock,
                'scopeConfig' => $this->_scopeConfigMock,
                'registry' => $this->_registryMock,
                'context' => $context
            )
        );
    }

    /**
     * @return array
     */
    public function dataProviderForTestIsActive()
    {
        return array(
            array(true, 1234, true),
            array(true, 'conversionId', false),
            array(true, '', false),
            array(false, '', false)
        );
    }

    /**
     * @param bool $isActive
     * @param string $returnConfigValue
     * @param bool $returnValue
     * @dataProvider dataProviderForTestIsActive
     */
    public function testIsGoogleAdwordsActive($isActive, $returnConfigValue, $returnValue)
    {
        $this->_scopeConfigMock->expects(
            $this->any()
        )->method(
            'isSetFlag'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::XML_PATH_ACTIVE
        )->will(
            $this->returnValue($isActive)
        );
        $this->_scopeConfigMock->expects($this->any())->method('getValue')->with($this->isType('string'))->will(
            $this->returnCallback(
                function () use ($returnConfigValue) {
                    return $returnConfigValue;
                }
            )
        );

        $this->assertEquals($returnValue, $this->_helper->isGoogleAdwordsActive());
    }

    public function testGetLanguageCodes()
    {
        $languages = array('en', 'ru', 'uk');
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::XML_PATH_LANGUAGES,
            'default'
        )->will(
            $this->returnValue($languages)
        );
        $this->assertEquals($languages, $this->_helper->getLanguageCodes());
    }

    public function dataProviderForTestConvertLanguage()
    {
        return array(
            array('some-language', 'some-language'),
            array('zh_TW', 'zh_Hant'),
            array('zh_CN', 'zh_Hans'),
            array('iw', 'he')
        );
    }

    /**
     * @param string $language
     * @param string $returnLanguage
     * @dataProvider dataProviderForTestConvertLanguage
     */
    public function testConvertLanguageCodeToLocaleCode($language, $returnLanguage)
    {
        $convertArray = array('zh_TW' => 'zh_Hant', 'iw' => 'he', 'zh_CN' => 'zh_Hans');
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::XML_PATH_LANGUAGE_CONVERT,
            'default'
        )->will(
            $this->returnValue($convertArray)
        );
        $this->assertEquals($returnLanguage, $this->_helper->convertLanguageCodeToLocaleCode($language));
    }

    public function testGetConversionImgSrc()
    {
        $conversionId = 123;
        $label = 'LabEl';
        $imgSrc = sprintf(
            'https://www.googleadservices.com/pagead/conversion/%s/?label=%s&amp;guid=ON&amp;script=0',
            $conversionId,
            $label
        );
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_IMG_SRC,
            'default'
        )->will(
            $this->returnValue($imgSrc)
        );
        $this->assertEquals($imgSrc, $this->_helper->getConversionImgSrc());
    }

    public function testGetConversionJsSrc()
    {
        $jsSrc = 'some-js-src';
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_JS_SRC
        )->will(
            $this->returnValue($jsSrc)
        );
        $this->assertEquals($jsSrc, $this->_helper->getConversionJsSrc());
    }

    /**
     * @return array
     */
    public function dataProviderForTestStoreConfig()
    {
        return array(
            array('getConversionId', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_ID, 123),
            array('getConversionLanguage', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_LANGUAGE, 'en'),
            array('getConversionFormat', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_FORMAT, '2'),
            array('getConversionColor', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_COLOR, 'ffffff'),
            array('getConversionLabel', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_LABEL, 'Label'),
            array('getConversionValueType', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_VALUE_TYPE, '1'),
            array('getConversionValueConstant', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_VALUE, '0')
        );
    }

    /**
     * @param string $method
     * @param string $xmlPath
     * @param string $returnValue
     * @dataProvider dataProviderForTestStoreConfig
     */
    public function testGetStoreConfigValue($method, $xmlPath, $returnValue)
    {
        $this->_scopeConfigMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            $xmlPath
        )->will(
            $this->returnValue($returnValue)
        );

        $this->assertEquals($returnValue, $this->_helper->{$method}());
    }

    public function testGetConversionValueDynamic()
    {
        $returnValue = 4.1;
        $this->_scopeConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_VALUE_TYPE
        )->will(
            $this->returnValue(\Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_TYPE_DYNAMIC)
        );
        $this->_registryMock->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_REGISTRY_NAME
        )->will(
            $this->returnValue($returnValue)
        );

        $this->assertEquals($returnValue, $this->_helper->getConversionValue());
    }

    /**
     * @return array
     */
    public function dataProviderForTestConversionValueConstant()
    {
        return array(array(1.4, 1.4), array('', \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_DEFAULT));
    }

    /**
     * @param string $conversionValueConst
     * @param string $returnValue
     * @dataProvider dataProviderForTestConversionValueConstant
     */
    public function testGetConversionValueConstant($conversionValueConst, $returnValue)
    {
        $this->_scopeConfigMock->expects(
            $this->at(0)
        )->method(
            'getValue'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_VALUE_TYPE
        )->will(
            $this->returnValue(\Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_TYPE_CONSTANT)
        );
        $this->_registryMock->expects($this->never())->method('registry');
        $this->_scopeConfigMock->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_VALUE
        )->will(
            $this->returnValue($conversionValueConst)
        );

        $this->assertEquals($returnValue, $this->_helper->getConversionValue());
    }
}
