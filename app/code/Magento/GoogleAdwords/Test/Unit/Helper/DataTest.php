<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Test\Unit\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
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
        $className = \Magento\GoogleAdwords\Helper\Data::class;
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className);
        $this->_helper = $objectManager->getObject($className, $arguments);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->_scopeConfigMock = $context->getScopeConfig();
        $this->_registryMock = $arguments['registry'];
    }

    /**
     * @return array
     */
    public function dataProviderForTestIsActive()
    {
        return [
            [true, 1234, true],
            [true, 'conversionId', false],
            [true, '', false],
            [false, '', false]
        ];
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
        $languages = ['en', 'ru', 'uk'];
        $this->_scopeConfigMock->expects(
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
        return [
            ['some-language', 'some-language'],
            ['zh_TW', 'zh_Hant'],
            ['zh_CN', 'zh_Hans'],
            ['iw', 'he']
        ];
    }

    /**
     * @param string $language
     * @param string $returnLanguage
     * @dataProvider dataProviderForTestConvertLanguage
     */
    public function testConvertLanguageCodeToLocaleCode($language, $returnLanguage)
    {
        $convertArray = ['zh_TW' => 'zh_Hant', 'iw' => 'he', 'zh_CN' => 'zh_Hans'];
        $this->_scopeConfigMock->expects(
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
        $this->_scopeConfigMock->expects(
            $this->at(0)
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
        $this->_scopeConfigMock->expects(
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
        return [
            ['getConversionId', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_ID, 123],
            ['getConversionLanguage', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_LANGUAGE, 'en'],
            ['getConversionFormat', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_FORMAT, '2'],
            ['getConversionColor', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_COLOR, 'ffffff'],
            ['getConversionLabel', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_LABEL, 'Label'],
            ['getConversionValueType', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_VALUE_TYPE, '1'],
            ['getConversionValueConstant', \Magento\GoogleAdwords\Helper\Data::XML_PATH_CONVERSION_VALUE, '0']
        ];
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
        $returnValueCurrency = 'USD';
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
        $this->_registryMock->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_CURRENCY_REGISTRY_NAME
        )->will(
            $this->returnValue($returnValueCurrency)
        );

        $this->assertEquals($returnValue, $this->_helper->getConversionValue());
        $this->assertEquals($returnValueCurrency, $this->_helper->getConversionValueCurrency());
    }

    /**
     * @return array
     */
    public function dataProviderForTestConversionValueConstant()
    {
        return [[1.4, 1.4], ['', \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_DEFAULT]];
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
