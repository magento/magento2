<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Locale\Validator
     */
    protected $_validatorModel;

    protected function setUp()
    {
        $localeConfigMock = $this->getMock('Magento\Framework\Locale\ConfigInterface');
        $localeConfigMock->expects(
            $this->any()
        )->method(
            'getAllowedLocales'
        )->will(
            $this->returnValue(['en_US', 'de_DE', 'es_ES'])
        );

        $this->_validatorModel = new \Magento\Framework\Locale\Validator($localeConfigMock);
    }

    /**
     * @return array
     */
    public function testIsValidDataProvider()
    {
        return [
            'case1' => ['locale' => 'en_US', 'valid' => true],
            'case2' => ['locale' => 'pp_PP', 'valid' => false]
        ];
    }

    /**
     * @dataProvider testIsValidDataProvider
     * @param string $locale
     * @param boolean $valid
     * @covers \Magento\Framework\Locale\Validator::isValid
     */
    public function testIsValid($locale, $valid)
    {
        $this->assertEquals($valid, $this->_validatorModel->isValid($locale));
    }
}
