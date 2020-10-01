<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleAdwords\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleAdwords\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $_registryMock;

    /**
     * @var Data
     */
    protected $_helper;

    protected function setUp(): void
    {
        $className = Data::class;
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className);
        $this->_helper = $objectManager->getObject($className, $arguments);
        /** @var Context $context */
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
            Data::XML_PATH_ACTIVE
        )->willReturn(
            $isActive
        );
        $this->_scopeConfigMock->method('getValue')->with($this->isType('string'))->willReturnCallback(
            function () use ($returnConfigValue) {
                return $returnConfigValue;
            }
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
            Data::XML_PATH_LANGUAGES,
            'default'
        )->willReturn(
            $languages
        );
        $this->assertEquals($languages, $this->_helper->getLanguageCodes());
    }

    /**
     * @return array
     */
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
            Data::XML_PATH_LANGUAGE_CONVERT,
            'default'
        )->willReturn(
            $convertArray
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
            Data::XML_PATH_CONVERSION_IMG_SRC,
            'default'
        )->willReturn(
            $imgSrc
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
            Data::XML_PATH_CONVERSION_JS_SRC
        )->willReturn(
            $jsSrc
        );
        $this->assertEquals($jsSrc, $this->_helper->getConversionJsSrc());
    }

    /**
     * @return array
     */
    public function dataProviderForTestStoreConfig()
    {
        return [
            ['getConversionId', Data::XML_PATH_CONVERSION_ID, 123],
            ['getConversionLanguage', Data::XML_PATH_CONVERSION_LANGUAGE, 'en'],
            ['getConversionFormat', Data::XML_PATH_CONVERSION_FORMAT, '2'],
            ['getConversionColor', Data::XML_PATH_CONVERSION_COLOR, 'ffffff'],
            ['getConversionLabel', Data::XML_PATH_CONVERSION_LABEL, 'Label'],
            ['getConversionValueType', Data::XML_PATH_CONVERSION_VALUE_TYPE, '1'],
            ['getConversionValueConstant', Data::XML_PATH_CONVERSION_VALUE, '0'],
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
        )->willReturn(
            $returnValue
        );

        $this->assertEquals($returnValue, $this->_helper->{$method}());
    }

    public function testHasSendConversionValueCurrency()
    {
        $this->_scopeConfigMock->expects($this->once())->method('isSetFlag')->willReturn(true);

        $this->assertTrue($this->_helper->hasSendConversionValueCurrency());
    }

    public function testGetConversionValueDynamic()
    {
        $returnValue = 4.1;
        $this->_scopeConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            Data::XML_PATH_CONVERSION_VALUE_TYPE
        )->willReturn(
            Data::CONVERSION_VALUE_TYPE_DYNAMIC
        );
        $this->_registryMock->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            Data::CONVERSION_VALUE_REGISTRY_NAME
        )->willReturn(
            $returnValue
        );

        $this->assertEquals($returnValue, $this->_helper->getConversionValue());
    }

    public function testGetConversionValueCurrency()
    {
        $returnValueCurrency = 'USD';
        $this->_scopeConfigMock->expects($this->once())->method('isSetFlag')->willReturn(true);
        $this->_registryMock->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            Data::CONVERSION_VALUE_CURRENCY_REGISTRY_NAME
        )->willReturn(
            $returnValueCurrency
        );

        $this->assertEquals($returnValueCurrency, $this->_helper->getConversionValueCurrency());
    }

    /**
     * @return array
     */
    public function dataProviderForTestConversionValueConstant()
    {
        return [[1.4, 1.4], ['', Data::CONVERSION_VALUE_DEFAULT]];
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
            Data::XML_PATH_CONVERSION_VALUE_TYPE
        )->willReturn(
            Data::CONVERSION_VALUE_TYPE_CONSTANT
        );
        $this->_registryMock->expects($this->never())->method('registry');
        $this->_scopeConfigMock->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            Data::XML_PATH_CONVERSION_VALUE
        )->willReturn(
            $conversionValueConst
        );

        $this->assertEquals($returnValue, $this->_helper->getConversionValue());
    }
}
